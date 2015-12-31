/**
 * @author http://www.cosmosfarm.com/
 */

(function(){
	tinymce.create('tinymce.plugins.KBoard_Media_Button', {
		init:function(ed, url){
			ed.addButton('kboard_media', {
				title : kboard_localize_strings.kboard_add_media,
				image : kbaord_plugin_url+'/images/media-button-icon.png',
				onclick : kboard_editor_open_media
			});
		},
		createControl : function(n,cm){
			return null;
		}
	});
	tinymce.PluginManager.add('kboard_media_button_script', tinymce.plugins.KBoard_Media_Button);
})();

function kboard_editor_open_media(){
	var w = 900;
	var h = 500;
	
	if(kbaord_board_id){
		var wrapper = jQuery('<div id="kboard_media_wrapper"></div>');
		var background = jQuery('<div id="kboard_media_background"></div>').css({opacity:'0.5'}).click(function(){
			kboard_media_close();
		});
		
		var init_window_size = function(){
			if(window.innerWidth <= 900){
				wrapper.css({left:0, top:0, margin:'10px', width:(window.innerWidth-20), height:(window.innerHeight-20)});
			}
			else{
				wrapper.css({left:'50%', top:'50%', margin:0, 'margin-left':(w/2)*-1, 'margin-top':(h/2)*-1, width:w, height:h});
			}
		}
		init_window_size();
		jQuery(window).resize(init_window_size);
		
		wrapper.append(jQuery('<iframe></iframe>').attr('src', '?action=kboard_media&board_id='+kbaord_board_id+'&media_group='+kbaord_media_group+'&content_uid='+kbaord_content_uid));
		jQuery('body').append(background).append(wrapper);
		
		if(!jQuery('input[name=media_group]').filter(function(){return this.value==kbaord_media_group}).length){
			jQuery('[name="board_id"]').parents('form').append(jQuery('<input type="hidden" name="media_group">').val(kbaord_media_group));
		}
	}
}

function kboard_editor_insert_media(url){
	if(tinyMCE && tinyMCE.activeEditor){
		tinyMCE.activeEditor.execCommand('mceInsertRawHTML', false, '<img src="'+url+'">');
		tinyMCE.activeEditor.selection.select(tinyMCE.activeEditor.getBody(), true);
		tinyMCE.activeEditor.selection.collapse(false);
    }
}

function kboard_media_close(){
	jQuery('#kboard_media_background').remove();
	jQuery('#kboard_media_wrapper').remove();
}