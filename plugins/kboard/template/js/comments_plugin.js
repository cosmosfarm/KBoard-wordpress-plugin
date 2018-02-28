/**
 * @author http://www.cosmosfarm.com/
 */
jQuery(document).ready(function(){
	if(cosmosfarm_comments_plugin_id){
		cosmosfarm_comments.init({plugin_id:cosmosfarm_comments_plugin_id});
		jQuery('.cosmosfarm-comments-plugin-count').each(function(){
			var count_obj = jQuery(this);
			var url = count_obj.attr('data-url');
			if(url){
				cosmosfarm_comments.count(url, function(res){
					var count = res.count?res.count:count_obj.attr('data-default').toString();
					if(count) count_obj.text(count_obj.attr('data-prefix') + count + count_obj.attr('data-endfix'));
				});
			}
		});
	}
});