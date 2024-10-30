<?php
/*
Plugin Name: LinkedInclude
Plugin URI: http://wordpress.org/plugins/linkedinclude/
Description: Article Importer for LinkedIn
Version: 3.0.4
Author: era404
Author URI: http://www.era404.com
License: GPLv2 or later.
Copyright 2015 ERA404 Creative Group, Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/***********************************************************************************
*     Globals
***********************************************************************************/
global $wpdb;
define('LINKEDINCLUDE_URL', admin_url() . 'admin.php?page=linkedinclude');
define('LINKEDINCLUDE_TABLE', $wpdb->prefix . 'linkedinclude_posts');
use GuzzleHttp\Client;

/***********************************************************************************
 *     Setup Plugin > Create Table
***********************************************************************************/
require_once("linkedinclude_setup.php");
// this hook will cause our creation function to run when the plugin is activated
register_activation_hook( 	__FILE__, 'linkedinclude_install' );
register_deactivation_hook( __FILE__, 'linkedinclude_uninstall' );
register_uninstall_hook(    __FILE__, 'linkedinclude_uninstall' );

/***********************************************************************************
*     Setup Admin Menus
***********************************************************************************/
add_action( 'admin_init', 'linkedinclude_admin_init' );
add_action( 'admin_menu', 'linkedinclude_admin_menu' );
 
function linkedinclude_admin_init() {
	/* Register our stylesheet. */
	wp_register_style( 'linkedinclude-styles', plugins_url('linkedinclude_admin.css', __FILE__) );
	/* and javascripts */
	wp_enqueue_script( 'linkedinclude-script', plugins_url('linkedinclude_admin.js', __FILE__), array('jquery'), 1.0 ); 	// jQuery will be included automatically
	wp_localize_script('linkedinclude-script', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) ); 	// setting ajaxurl
}
add_action( 'wp_ajax_showhide', 'linkedInArticleDisplay' ); 	//for loading image to refine
//add_action( 'wp_ajax_cropimage', 'linkedinclude_cropimage' ); 	//for refining crop
 
function linkedinclude_admin_menu() {
	/* Register our plugin page */
	$page = add_submenu_page(	'tools.php', 
								'LinkedInclude', 
								'LinkedInclude', 
								'manage_options', 
								'linkedinclude', 
								'linkedinclude_plugin_options');

	/* Using registered $page handle to hook stylesheet loading */
	add_action( 'admin_print_styles-' . $page, 'linkedinclude_admin_styles' );
	add_action( 'admin_print_scripts-'. $page, 'linkedinclude_admin_scripts' );
}
 
function linkedinclude_admin_styles() { wp_enqueue_style( 'linkedinclude-styles' ); wp_enqueue_style( 'thickbox' ); }
function linkedinclude_admin_scripts() { wp_enqueue_script( 'linkedinclude-script' ); wp_enqueue_script( 'thickbox' ); }
 
function linkedinclude_plugin_options() {
	global $wpdb;
	
	/* Output our admin page */
	echo "<div id='linkedinclude'>
			<h1>LinkedInclude <span>(Experimental)</span></h1>
			<div id='linkedinclude_instructions' data-expanded='0'>
		  		<h1>LinkedInclude <span>(Experimental)</span></h1>
				<p>This version of LinkedInclude attempts to import LinkedIn articles, exploiting the <em>Author Post Activity</em><br />
				  permalink still accessible at the time this plugin was rewritten:<br />
				  <strong>https://www.linkedin.com/today/author/[ author ]</strong>.</p>
				  <p>Because LinkedIn does <u>not</u> offer, encourage, or support the replication of articles outside of their network, this
				  automated process is to be considered experimental and not guaranteed to be a permanent solution for importing articles
				  from LinkedIn into WordPress. The popularity of earlier versions of this plugin is the reason we continue to support
				  LinkedInclude, however we cannot guarantee that this rewritten version will continue to function after the operators of
				  LinkedIn discover this exploit.</p>
					
				<p>If you wish to continue, enter a valid LinkedIn Author Post Activity link.</p>
					<ol>
					  <li>Click <strong>TEST</strong> to check if an articles list is found and capable of being read by LinkedInclude using the Author Post Activity exploit on LinkedIn.
						If you don't see any results following a test, it's very likely that attempting to FETCH ARTICLES will yield the same results.</li>
		 
					  <li>Click <strong>FETCH ARTICLES</strong> to attempt importing all related articles.<br />
						It appears that LinkedIn throttles these types of connections. If you get the desired results only periodically, it's advised you wait 24hrs before trying again.</li>
					</ol>
				<p>Please refer to <a href='https://wordpress.org/plugins/linkedinclude/#installation' title='LinkedInclude on the WordPress Plugin Repository' target='_blank'>LinkedInclude's installation instructions</a> for more information.</p>

		  		<div class='litabs'> 			
		  			<div class='litab' style='float:right;'>Instructions</div>
		  		</div>
		  	</div>";

	//record author posts
	if(!empty($_POST) && isset($_POST['authorurl']) && filter_var($_POST['authorurl'], FILTER_VALIDATE_URL)){
		
		error_reporting(E_ALL); ini_set('display_errors',1);
		
		//transform & clean
		$authorurl = (string) trim($_POST['authorurl']," /");		
		
		//testing url?
		if(isset($_POST['test']) && filter_var($authorurl, FILTER_VALIDATE_URL)){
			echo "<strong>Testing scrape of Author URL</strong>: {$authorurl}<br />";
			echo "Below is what the LinkedInclude was able to read. ";
			echo "<a href='".LINKEDINCLUDE_URL."' title='Return to LinkedInclude' target='_top'>Return to LinkedInclude</a><br /><br /><hr />";
			getLinkedInArticles($authorurl,true);
			echo "<a href='".LINKEDINCLUDE_URL."' title='Return to LinkedInclude' target='_top'>Return to LinkedInclude</a>";
			exit;
		}
		
		//importing
		if(isset($_POST['submit'])) getLinkedInArticles($authorurl);
	}
	
	//get last linkedinarticle, or use default
	$lastauthor = $wpdb->get_var("SELECT authorurl FROM ".LINKEDINCLUDE_TABLE." ORDER BY article_id DESC LIMIT 1");
	if(!$lastauthor) $lastauthor = "https://www.linkedin.com/today/author/brigettehyacinth/";
	
	echo <<<FORM
	<section>
	<form method='post' class='linkedinclude_fetch'>
	LinkedIn Article URL:
		<input type='text' name='authorurl' placeholder='Author URL (https://www.linkedin.com/today/author/...)'
			value='{$lastauthor}' /> &nbsp;
		<input type='submit' name='test' value='test' />
		<input type='submit' name='submit' value='fetch articles' />
	</form>
	</section>
FORM;
	
	//display all posts, return newest author id
	showLinkedInArticles();

	//display donations form
	$bulcclublogo = plugins_url('linkedinclude/img/bulcclub.png');
	?>
<!-- paypal donations, please -->
<div class="footer">
	<div class="donate" style='display:none;'>	
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="JE3VMPUQ6JRTN">
		<input type="image" src="<?php echo plugins_url('linkedinclude/img/paypal.png');?>" border="0" name="submit" alt="PayPal&mdash;The safer, easier way to donate!" title="PayPal&mdash;The safer, easier way to donate!" class="donate">
		<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
		<p>If <b>ERA404's LinkedInclude WordPress Plugin</b> has made your life easier and you wish to say thank you, use the Secure PayPal link above to buy us a cup of coffee.</p>
	</div>
	<div class="bulcclub">
		<a href="https://www.bulc.club/?utm_source=wordpress&utm_campaign=linkedinclude&utm_term=linkedinclude" title="Bulc Club. It's Free!" target="_blank"><img src="<?php echo plugins_url('linkedinclude/img/bulcclub.png');?>" alt="Join Bulc Club. It's Free!" /></a>
		<p>For added protection from malicious code and spam, use Bulc Club's unlimited 100% free email forwarders and email filtering to protect your inbox and privacy. <strong><a href="https://www.bulc.club/?utm_source=wordpress&utm_campaign=linkedinclude&utm_term=linkedinclude" title="Bulc Club. It's Free!" target="_blank">Join Bulc Club &raquo;</a></strong></p>
	</div>
</div>
</div><!--end donations form -->
<?php
}

/**************************************************************************************************
*	Helper Functions
/**************************************************************************************************
*	GetLinkedInArticles - Loads the Related Posts (given an article URL)
*	https://www.linkedin.com/pulse-fe/api/v1/relatedContent?permalink=[permalink]&template=templates%2Fwalter%2Farticle%2FrelatedContent&returnCount=3&start=0&count=20&height=120&width=360&media=t
**************************************************************************************************/
function getLinkedInArticles($authorurl, $testonly=false){
	require("vendor/autoload.php");
	
	$r = array();			//for results

	try{
		$client = new GuzzleHttp\Client();
		$res = $client->request('GET', $authorurl );
		
		if($res->getStatusCode() == 200){
			$str = (string) $res->getBody(); //myprint_r($str,1);
			require_once("vendor/pharse/pharse.php");
			$html = Pharse::str_get_dom($str);
			$arr = array();
			//author name
			foreach($html('h3.mini-card__title') as $author){
				$author = $author->getPlainText();
				break;
			}
			//title & url
			foreach($html('a.article-card__title--link') as $artnum => $title){
				$arr[ $artnum ]['title'] = $title->getPlainText();
				$arr[ $artnum ]['permalink'] = $articleurl = "https://www.linkedin.com" . $title->href;
				//attempt to get the content
				if($articleurl && strlen($articleurl)>30){
					$subres = $client->request('GET', $articleurl );
					$content =  Pharse::str_get_dom( (string) $subres->getBody() );
					$source = false;
					$body = $content("section.article-body");
					foreach($body as $text){
						if($source = $text->getPlainText()) $arr[ $artnum ]['content'] = $source;
						break;
					}
					if(false == $source){
						$body = $content("div.article-content__body"); 
						foreach($body as $text){
							if($source = $text->getPlainText()) $arr[ $artnum ]['content'] = $source;
							break;
						}
					}
				}
			}
			//image
			foreach($html('img.article-card__image') as $artnum => $img){
				$arr[ $artnum ]['image_caption'] = $img->alt;
				$arr[ $artnum ]['image_url'] = $image = preg_replace('/(<img(.*)((data-delayed-url|src)\=\"([^\"]+)\")([^\>]*)\>)/', '$5', $img->html());
				if(preg_match('/-shrink_([\d]+)_([\d]+)/', $image, $sizes)){
					if(isset($sizes[1])) $arr[ $artnum ]['image_height'] = $sizes[1];
					if(isset($sizes[2])) $arr[ $artnum ]['image_width'] = $sizes[2];
				}
			}
			//date, likes, comments, shares
			foreach($html('div.article-card__content') as $artnum => $meta){
				$arr[ $artnum ]['meta'] = getMetaContent( $meta->html() );
			}
			if($testonly){
				myprint_r($arr);
				return;
			}

/**************************************************************************************************
*	If articles are found, we'll store them to MySQL and fetch/update content afterward
**************************************************************************************************/
			if($arr && !empty($arr)){													global $wpdb;
				//$wpdb->show_errors = true; $wpdb->suppress_errors = false; echo "<pre>";
				$wpdb->show_errors = false; $wpdb->suppress_errors = true;
				
				//do the inserts
				$formats = array(	'%d',	//image_width
									'%d',	//image_height
									'%s',	//image_caption
									'%s',	//image_url
									'%s',	//authorurl
									'%s',	//author
									'%s',	//permalink
									'%s',	//title
									'%s',	//date (human readable)
									'%d',	//date (sortable)
									'%s',	//shares
									'%s',	//likes
									'%s',	//comments
									'%s'	//content
				);
				
/**************************************************************************************************
*	Iterate to INSERT/UPDATE
**************************************************************************************************/
				foreach($arr as $k => $art){
				
					//check if exists first
					$exists = $wpdb->get_var( 	
						$wpdb->prepare("SELECT count(*) FROM ".LINKEDINCLUDE_TABLE." WHERE title=%s", array($art['title']))
					);
										
					//create simple array for each item
					$item = array(
									"image_width"	=> $art['image_width'],
									"image_height"	=> $art['image_height'],
									"image_caption"	=> $art['image_caption'],
									"image_url"		=> $art['image_url'],
									"authorurl"		=> $authorurl,
									"author"		=> $author,
									"permalink"		=> $art['permalink'],
									"title"			=> $art['title'],
									"date_hr"		=> $art['meta']['date'],
									"date"			=> strtotime($art['meta']['date']),
									"shares"		=> $art['meta']['shares'],
									"likes"			=> $art['meta']['likes'],
									"comments"		=> $art['meta']['comments'],
									"content"		=> $art['content'] );
					
					//insert
					if($exists < 1){
						if($wpdb->insert(LINKEDINCLUDE_TABLE, $item, $formats)){
							$r['a'][] = $item['title']; } //add success
						else {	$r['f'][] = $item['title']; } //add fail
						
						if($wpdb->show_errors){ //debugging
							print_r($wpdb->last_error);
							print_r($wpdb->last_query);
							print_r($wpdb->last_result);
							print_r($item);
						}
					//update
					} else {
						$op = $wpdb->update(LINKEDINCLUDE_TABLE, $item, array('title'=>$item['title']), $formats, array('%s'));
						if(false === $op){
							$r['f'][] = $item['title']; //update fail
						} elseif(0 === $op){
							$r['n'][] = $item['title']; //nothing to update
						} elseif(0 < $op){
							$r['u'][] = $item['title']; //update success
						}
						else {}
					}
				}
			} else { /* array of items was empty, or failed to retrieve */
				echo "<div class='msg err'><strong>No Articles Found</strong></div>";
			}
		} else { /* some other status besides 200 returned trying to retrieve articles */
			echo "<div class='msg err'><strong>Status of {$res->getStatusCode()} returned while trying to fetch articles.</strong></div>";
		}
	} catch (Exception $e) { /* error thrown while trying to fetch related articles */
		echo "<div class='msg err'><strong>".$e->getMessage()."</strong></div>";
	}

	displayLinkedIncludeResults($r);
	return;
}
function getMetaContent($html){
	preg_match_all('/span([^\>]+)\>([^\<]+)/', $html, $meta);
	$found = array("date"=>false,"likes"=>false,"comments"=>false,"shares"=>false);
	if(!$meta||empty($meta)||!isset($meta[2])||empty($meta[2])) return($found);	
	foreach($meta[2] as $m){
		    if(strstr($m,"likes")){ 	$found['likes'] = 		trim(str_replace("likes","",$m)); 		}
		elseif(strstr($m,"comments")){ 	$found['comments'] = 	trim(str_replace("comments","",$m)); 	}
		elseif(strstr($m,"shares")){ 	$found['shares'] = 		trim(str_replace("shares","",$m)); 		}
		elseif(strstr($m,", ")){		$found['date'] =		trim($m); 								}
	}
	return($found);
}
/**************************************************************************************************
*	Show the Results of the Scrape
**************************************************************************************************/
function displayLinkedIncludeResults($r){

	if(empty($r)){ echo "<div class='msg'>No posts have been added or updated.</div>"; }
	else {
		$results = "";
		if(isset($r['a']) && !empty($r['a'])){
			$results .= "<div class='msg'><strong>".count($r['a'])." LinkedIn posts were found and recorded:</strong> <em>";
			$results .= implode(", ", $r['a']). "</em></div>";
		}
		if(isset($r['u']) && !empty($r['u'])){
			$results .= "<div class='msg'><strong>".count($r['u'])." LinkedIn posts were found and updated:</strong> <em>";
			$results .= implode(", ", $r['u']). "</em></div>";
		}
		if(isset($r['n']) && !empty($r['n'])){
			$results .= "<div class='msg'><strong>".count($r['n'])." LinkedIn posts were found but no updates were necessary:</strong> <em>";
			$results .= implode(", ", $r['n']). "</em></div>";
		}
		if(isset($r['f']) && !empty($r['f'])){
			$results .= "<div class='msg err'><strong>".count($r['f'])." LinkedIn posts were found but were not recorded:</strong> <em>";
			$results .= implode(", ", $r['f']). "</em></div>";
		}
		echo $results;
	}
	return;
}
/**************************************************************************************************
*	Front-End: Organize the Articles into Blocks and Display
**************************************************************************************************/
function linkedinclude_frontend_styles() { 
	wp_enqueue_style( 'linkedinclude', plugins_url('linkedinclude.css', __FILE__) );
}
add_action( 'wp_enqueue_scripts', 'linkedinclude_frontend_styles' );
function showLinkedInArticles(){
	global $wpdb;
	echo "\n<section>";
	$articles = $wpdb->get_results("SELECT * FROM ".LINKEDINCLUDE_TABLE." ORDER BY date DESC");
	//echo "<pre>"; print_r($articles); echo "</pre>";
	if(!empty($articles)){
		foreach ( $articles as $article ) {
		$a = (array) $article;
		$content = substr($a['content'], 0, @strpos($a['content'], ' ', 400))."...";

		echo "\n<article class='".($a['display'] > 0 ? "":"unchecked")."'>".
			 	"\n\t<aside>".
			 		"\n\t\t<strong>{$a['shares']}</strong> Shares".
			 		"\n\t\t<strong>{$a['likes']}</strong> Likes".
			 		"\n\t\t<strong>{$a['comments']}</strong> Comments".
			 	"\n\t</aside>".
			 	"\n\t<h1><input type='checkbox' data-article-id='{$a['article_id']}' class='li_display' value='1' ".($a['display'] > 0 ? "checked='checked'":"")." title='Show/Hide' autocomplete='Off' />".
			 		"\n\t\t<a href='{$a['permalink']}' title='".esc_attr($a['title'])."' target='_blank' class='permalink'>{$a['title']}</a> ".
			 		"\n\t\t<cite>by <a href='{$a['authorurl']}' title='Author Articles: ".esc_attr($a['author'])."' target='_blank'>{$a['author']}</a></cite>".
			 	"\n\t</h1>".
			 	"\n\t<div>".
			 	"\n\t\t<img src='{$a['image_url']}' alt='".esc_attr($a['title'])."' height='80' />".
			 	"\n\t\t<span class='content'>{$content}<br /><small>Published: {$a['date_hr']}</small></span>".
			 	"\n\t</div>".
			 "\n</article>";
		}
	} else { 
		echo "<article>No LinkedIn articles have been imported.</article>"; 
	}
	echo "\n</section>";
}
/**************************************************************************************************
 *	Ajax Functions
**************************************************************************************************/
function linkedInArticleDisplay(){
	global $wpdb;
	header('Content-type: application/json');
	
	$results = array(	 1 => "Display state toggled to: ",
						-1 => "Invalid article",
						-2 => "Could not locate article",
					);
						
	//valid article?
	$display = 		(in_array($_POST['showhide'], array("true", "false"))  ? (int) ($_POST['showhide']=="true"?1:-1) : false);
	$article_id = 	(isset($_POST['article_id']) ? trim($_POST['article_id']) : false);
	if(!$display || !$article_id) die(json_encode(array("res"=>-1,"msg"=>$results[-1])));
	
	//article located?
	if(!$obj = $wpdb->get_row( $wpdb->prepare("SELECT permalink, content FROM ".LINKEDINCLUDE_TABLE." WHERE article_id=%d", array($article_id)))) die(json_encode(array("res"=>-2,"msg"=>$results[-2])));
	
	//change display state
	$wpdb->query("UPDATE ".LINKEDINCLUDE_TABLE." SET display={$display} WHERE article_id='{$article_id}'");

	//toggled
	die(json_encode(array("res"=>1,"msg"=>$results[1].($display>0?"ON":"OFF"),"lish"=>$display)));
}

/**************************************************************************************************
*	Widget Functions
/**************************************************************************************************
 *	Create the Widget
**************************************************************************************************/
class linkedinclude_widget extends WP_Widget {
	function __construct() {
		parent::__construct(
			'linkedinclude_widget',
			'LinkedInclude Widget',
			array( 'description' => 'Display the Articles from LinkedIn', )
		);
	}
	public function widget( $args, $instance ) {
		//widget title
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $args['before_widget'] . (!empty($title) ? $args['before_title'] . $title . $args['after_title'] : "");

		//get posts from database
		global $wpdb;

		$where = " WHERE " . ($instance['author']!="all" ? " author='{$instance['author']}' AND " : "")." display>0 ";
		$length = (is_numeric($instance['length']) ? (int) $instance['length'] : 200);
		$articles = $wpdb->get_results("SELECT * FROM ".LINKEDINCLUDE_TABLE." {$where} ORDER BY date DESC LIMIT {$instance['postcount']}");

		//iterate
		if(!empty($articles)) {
			echo "<ul>";
			$authorurl = false;
			$lastauthor = false;
			foreach( $articles as $article ){
				$a = (array) $article;
				
				if($authorurl && ""!=trim($a['authorurl']) && $authorurl!=$a['authorurl'] && ""!=trim($lastauthor)){
					echo "<li><p><a href='{$authorurl}' title='All Articles on LinkedIn' target='_blank' class='limore'>View All Articles by {$lastauthor} &raquo;</a></p></li>";
				}
				
				$content = substr($a['content'], 0, @strpos($a['content'], ' ', $length));
				if(strlen($content)>0) $content.="...";
				
				echo "<li><a href='{$a['permalink']}' title='".esc_attr($a['title'])."' target='_blank'>
				<img class='scale-with-grid align-left wp-post-image' 
					alt='".esc_attr((""!=$a['image_caption']?$a['image_caption']:$a['title']))."' 
					src='{$a['image_url']}'>
				</a>
				<h5><a href='{$a['permalink']}' title='".esc_attr($a['title'])."' target='_blank'>".esc_attr($a['title'])."</a></h5>
				<p>{$content}<a href='{$a['permalink']}' class='limore' target='_blank' title='".esc_attr($a['title'])."'>Read More &raquo;</a></p></li>";
				
				if(""!=trim($a['authorurl'])) $authorurl = $a['authorurl'];
				$lastauthor = trim($a['author']);
			}
			if($authorurl) echo "<li><p><a href='{$authorurl}' title='All Articles on LinkedIn' target='_blank' class='limore'>View All Articles by {$lastauthor} &raquo;</a></p></li>";
			echo "</ul>";
		}
		//finish
		echo $args['after_widget'];
	}

	// Widget Backend
	public function form( $instance ) {
		global $wpdb;
		
		$title = 		( isset( $instance[ 'title' ] ) ?  $instance[ 'title' ] : "");
		$author = 		( isset( $instance[ 'author' ] ) ?  $instance[ 'author' ] : "");
		$postcount = 	( isset( $instance[ 'postcount' ] ) ?  $instance[ 'postcount' ] : 5);
		$length = 		( isset( $instance[ 'length' ] ) ?  $instance[ 'length' ] : 200);

		//get authors for selector
		$authors = $wpdb->get_results("SELECT DISTINCT author FROM ".LINKEDINCLUDE_TABLE." ORDER BY author ASC");

		//form options
		echo "<p><label for='".$this->get_field_id( 'title' )."'>Title</label>
				<input class='widefat' 
				 id='".$this->get_field_id( 'title' )."' 
				 name='".$this->get_field_name( 'title' )."' type='text' 
				 value='".esc_attr( $title )."' /></p>";
		
		echo "<p><label for='".$this->get_field_id( 'author' )."'>Author</label>
				<select class='widefat'
				 id='".$this->get_field_id( 'author' )."'
				 name='".$this->get_field_name( 'author' )."'>
				 		<option value='all'>All Authors</option>\n";
					foreach($authors as $author) {
						echo "<option value='".esc_attr($author->author)."' ".
						($author == esc_attr($author->author) ? "selected='selected'":"").
						">".esc_attr($author->author)."</option>\n";
					}
				 echo "</select></p>";
		echo "<p><label for='".$this->get_field_id( 'postcount' )."'>Posts to Show</label>
				<input class='widefat'
				 id='".$this->get_field_id( 'postcount' )."'
				 name='".$this->get_field_name( 'postcount' )."' type='text'
				 value='".esc_attr( $postcount )."' /></p>";
		echo "<p><label for='".$this->get_field_id( 'length' )."'>Excerpt Length</label>
				<input class='widefat'
				 id='".$this->get_field_id( 'length' )."'
				 name='".$this->get_field_name( 'length' )."' type='text'
				 value='".esc_attr( $length )."' /></p>";
}
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = 	( ! empty( $new_instance['title'])  	? strip_tags( $new_instance['title'] ) : '');
		$instance['author'] = 	( ! empty( $new_instance['author']) 	? strip_tags( $new_instance['author'] ) : '');
		$instance['postcount']= ( ! empty( $new_instance['postcount']) 	? strip_tags( $new_instance['postcount'] ) : '');
		$instance['length']= 	( ! empty( $new_instance['length']) 	? strip_tags( $new_instance['length'] ) : '');
		return $instance;
	}
	
} // Class wpb_widget ends here


// Register and load the widget
function linkedinclude_widget_load_widget() {
	register_widget( 'linkedinclude_widget' );
}
add_action( 'widgets_init', 'linkedinclude_widget_load_widget' );

?>