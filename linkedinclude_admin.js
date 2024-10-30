jQuery(document).ready(function() {
	/**************************************************************************************************
	*	Instructions Tab
	**************************************************************************************************/
	var instr = jQuery('#linkedinclude_instructions');
	var instrh = instr.height();
	instr.css({ 'top': (-1* (instrh+42)) +"px", 'display':'block' });
	jQuery("#linkedinclude_instructions div.litab").on("click", function(){
		if(instr.data("expanded")<1){
			instr.css({ top: "-21px" }).data('expanded',1); jQuery(this).addClass("expanded");
		} else {
			instr.css({ top: (-1* (instrh+42)) +"px" }).data('expanded',0); jQuery(this).removeClass("expanded");
		}
	});
	/**************************************************************************************************
	*	Fetch Content (and Activate an Article)
	**************************************************************************************************/	
	jQuery(".li_display").on("change",function(){
		var item = jQuery(this);
		var article_id = item.data("article-id"), showhide = item.prop("checked");
		var article = item.closest("article");
		
		//if it needs content, show the updating icon
		article.addClass("updating");

		var req = {
				action: 'showhide',
				article_id: article_id,
				showhide: showhide
		};

		jQuery.post(ajax_object.ajaxurl, req, function(data) {
			
			if(typeof(data.res)!="undefined"){
				//successes
				if(data.res>0){
					//content is updated
					if(data.res==2 && typeof(data.content)!="undefined"){
						article.find("span.content").html(data.content);
						article.removeClass("unchecked");
					}
					//article is toggled
					if(data.res==1){
						if(data.lish<1) { article.addClass("unchecked"); }
						else { article.removeClass("unchecked"); }
					}
				//fails
				} else {
					var content = article.find("span.content").html();
					article.find("span.content").html( data.msg + "<br />" + content );
				}
			} else {
				//no results apparently?
			}
			article.removeClass("updating");

		});
		return;		
	});

	//donate form ;]
	var $donateform = jQuery("#linkedinclude div.donate").html();
	jQuery("#linkedinclude div.donate").html("<form action='https://www.paypal.com/cgi-bin/webscr' method='post' id='donate' target='_blank'>"+$donateform+"</form>").css("display","block");

});