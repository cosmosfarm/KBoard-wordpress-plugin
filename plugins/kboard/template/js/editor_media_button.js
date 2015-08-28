/**
 * @author http://www.cosmosfarm.com/
 */

(function(){
	tinymce.create('tinymce.plugins.KBoard_Media_Button', {
		init:function(ed, url){
			ed.addButton('kboard_media', {
				title : 'KBoard 이미지 삽입하기',
				image : kbaord_plugin_url+'/images/media-button-icon.png',
				onclick : kboard_editor_open_media
			});
		},
		createControl : function(n, cm){
			return null;
		}
	});
	tinymce.PluginManager.add('kboard_media_button_script', tinymce.plugins.KBoard_Media_Button);
})();

function kboard_editor_open_media(){
	var w = 900;
	var h = 500;
	
	if(kbaord_board_id){
		window.open('?action=kboard_media&board_id='+kbaord_board_id+'&media_group='+kbaord_media_group+'&content_uid='+kbaord_content_uid, 'kboard_media', 'width='+w+',height='+h+',left='+(screen.availWidth-w)*0.5+',top='+(screen.availHeight-h)*0.5);
		
		if(!jQuery('input[name=media_group]').filter(function(){return this.value==kbaord_media_group}).length){
			jQuery('[name="board_id"]').parents('form').append(jQuery('<input type="hidden" name="media_group">').val(kbaord_media_group));
		}
	}
}

function kboard_editor_insert_media(url){
	if(tinyMCE && tinyMCE.activeEditor){
		tinyMCE.activeEditor.execCommand("mceInsertRawHTML", false, '<img src="'+url+'">');
    }
}