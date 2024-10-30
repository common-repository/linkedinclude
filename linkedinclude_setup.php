<?php
$linkedinclude_tablename = $wpdb->prefix . "linkedinclude_posts";

function linkedinclude_install() {
	if(!current_user_can( 'activate_plugins' )) return;
	
	global $wpdb;
	$linkedinclude_tablename = $wpdb->prefix . "linkedinclude_posts";
	
	$sql = "CREATE TABLE {$linkedinclude_tablename} (
	article_id		int(10) unsigned NOT NULL auto_increment primary key,

	image_width		int(10) DEFAULT NULL,
	image_height	int(10) DEFAULT NULL,
	image_caption	text CHARACTER SET utf8 DEFAULT NULL,
	image_url		text CHARACTER SET utf8 DEFAULT NULL,

	authorurl		text CHARACTER SET utf8 DEFAULT NULL,
	author			text CHARACTER SET utf8 DEFAULT NULL,
	permalink		text CHARACTER SET utf8 NOT NULL,
	title 			varchar(255) CHARACTER SET utf8 DEFAULT NULL,
	date_hr			text CHARACTER SET utf8 DEFAULT NULL,
	date			int(10) DEFAULT NULL,

	shares			varchar(10) DEFAULT '0',
	likes			varchar(10) DEFAULT '0',
	comments		varchar(10) DEFAULT '0',

	content			text,
	display			tinyint(1) NOT NULL DEFAULT '-1',

	UNIQUE KEY title (title)

	) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	return;
}

/**************************************************************************************************
 *	Uninstall Functions - Garbage Cleanup
 **************************************************************************************************/
function linkedinclude_uninstall(){
	if(!current_user_can( 'activate_plugins' )) return;
	
	global $wpdb;
	$linkedinclude_tablename = $wpdb->prefix . "linkedinclude_posts";
	$wpdb->query("DROP TABLE {$linkedinclude_tablename}");
}

/**************************************************************************************************
*	Check for Older Database Schemas
**************************************************************************************************/
function checkIfUpgradeNeeded(){
	global $wpdb, $linkedinclude_tablename;
	//table exists?
	if($wpdb->get_var("SHOW TABLES LIKE '$linkedinclude_tablename'") == $linkedinclude_tablename){
		//column exists?
		$column = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
			DB_NAME, $linkedinclude_tablename, "authorurl"
		));
		//drop & re-create
		if(!$column){ if($wpdb->query("DROP TABLE {$linkedinclude_tablename}")){ linkedinclude_install(); }}
	} else {
		//just create
		linkedinclude_install();
	}
}
add_action('init','checkIfUpgradeNeeded');

?>
