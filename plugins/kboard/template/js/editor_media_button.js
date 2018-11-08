/**
 * @author https://www.cosmosfarm.com
 */
(function(){
	tinymce.create('tinymce.plugins.KBoard_Media_Button', {
		init:function(ed, url){
			ed.addButton('kboard_media', {
				title : kboard_localize_strings.kboard_add_media,
				image : kboard_settings.plugin_url+'/images/media-button-icon.png',
				onclick : kboard_editor_open_media
			});
		},
		createControl : function(n,cm){
			return null;
		}
	});
	tinymce.PluginManager.add('kboard_media_button_script', tinymce.plugins.KBoard_Media_Button);
})();