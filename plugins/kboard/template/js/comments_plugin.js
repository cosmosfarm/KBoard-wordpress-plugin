/**
 * @author http://www.cosmosfarm.com/
 */
jQuery(document).ready(function($){
	if(cosmosfarm_comments_plugin_id){
		cosmosfarm_comments.init({plugin_id:cosmosfarm_comments_plugin_id});
		$('.cosmosfarm-comments-plugin-count').each(function(){
			var count = $(this);
			var url = count.attr('data-url');
			if(url){
				cosmosfarm_comments.count(url, function(res){
					if(res.count){
						count.text('(' + res.count + ')');
					}
				});
			}
		});
	}
});