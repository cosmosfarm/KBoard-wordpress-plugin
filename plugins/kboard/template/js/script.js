/**
 * @author http://www.cosmosfarm.com/
 */

var kboard_ajax_lock = false;

function kboard_editor_open_media(){
	var w = 900;
	var h = 500;
	
	if(kbaord_current.board_id){
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
		
		wrapper.append(jQuery('<iframe frameborder="0"></iframe>').attr('src', '?action=kboard_media&board_id='+kbaord_current.board_id+'&media_group='+kboard_settings.media_group+'&content_uid='+kbaord_current.content_uid));
		jQuery('body').append(background).append(wrapper);
		
		if(!jQuery('input[name=media_group]').filter(function(){return this.value==kboard_settings.media_group}).length){
			jQuery('[name="board_id"]').parents('form').append(jQuery('<input type="hidden" name="media_group">').val(kboard_settings.media_group));
		}
	}
}

function kboard_editor_insert_media(url){
	if(typeof tinyMCE != 'undefined' && typeof tinyMCE.activeEditor != 'undefined'){
		tinyMCE.activeEditor.execCommand('mceInsertRawHTML', false, '<img src="'+url+'" alt="">');
		tinyMCE.activeEditor.selection.select(tinyMCE.activeEditor.getBody(), true);
		tinyMCE.activeEditor.selection.collapse(false);
	}
	else{
		jQuery('#kboard_content').val(function(index, value){
		     return value + (!value?'':' ') + '<img src="'+url+'" alt="">';
		});
	}
}

function kboard_media_close(){
	jQuery('#kboard_media_background').remove();
	jQuery('#kboard_media_wrapper').remove();
}

function kboard_document_print(url){
	window.open(url, 'kboard_document_print');
	return false;
}

function kboard_document_like(button){
	if(!kboard_ajax_lock){
		kboard_ajax_lock = true;
		jQuery.post(kboard_settings.alax_url, {'action':'kboard_document_like', 'document_uid':jQuery(button).data('uid')}, function(res){
			kboard_ajax_lock = false;
			if(res){
				jQuery('.kboard-document-like-count', button).text(res);
			}
			else{
				alert(kboard_localize_strings.you_have_already_voted);
			}
		});
	}
	else{
		alert(kboard_localize_strings.please_wait);
	}
	return false;
}

function kboard_document_unlike(button){
	if(!kboard_ajax_lock){
		kboard_ajax_lock = true;
		jQuery.post(kboard_settings.alax_url, {'action':'kboard_document_unlike', 'document_uid':jQuery(button).data('uid')}, function(res){
			kboard_ajax_lock = false;
			if(res){
				jQuery('.kboard-document-unlike-count', button).text(res);
			}
			else{
				alert(kboard_localize_strings.you_have_already_voted);
			}
		});
	}
	else{
		alert(kboard_localize_strings.please_wait);
	}
	return false;
}

function kboard_comment_like(button){
	if(!kboard_ajax_lock){
		kboard_ajax_lock = true;
		jQuery.post(kboard_settings.alax_url, {'action':'kboard_comment_like', 'comment_uid':jQuery(button).data('uid')}, function(res){
			kboard_ajax_lock = false;
			if(res){
				jQuery('.kboard-comment-like-count', button).text(res);
			}
			else{
				alert(kboard_localize_strings.you_have_already_voted);
			}
		});
	}
	else{
		alert(kboard_localize_strings.please_wait);
	}
	return false;
}

function kboard_comment_unlike(button){
	if(!kboard_ajax_lock){
		kboard_ajax_lock = true;
		jQuery.post(kboard_settings.alax_url, {'action':'kboard_comment_unlike', 'comment_uid':jQuery(button).data('uid')}, function(res){
			kboard_ajax_lock = false;
			if(res){
				jQuery('.kboard-comment-unlike-count', button).text(res);
			}
			else{
				alert(kboard_localize_strings.you_have_already_voted);
			}
		});
	}
	else{
		alert(kboard_localize_strings.please_wait);
	}
	return false;
}