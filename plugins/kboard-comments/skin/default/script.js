/**
 * @author http://www.cosmosfarm.com/
 */

var console = window.console || { log: function() {} };
jQuery.fn.exists = function(){
	return this.length>0;
}

function kboard_comments_execute(form){
	var $ = jQuery;
	
	if($('input[name=member_display]', form).exists() && !$('input[name=member_display]', form).val()){
		alert('Missing Name!');
		$('[name=member_display]', form).focus();
		return false;
	}
	else if($('input[name=password]', form).exists() && !$('input[name=password]', form).val()){
		alert('Missing Password!');
		$('input[name=password]', form).focus();
		return false;
	}
	else if($('textarea[name=content]', form).exists() && !$('textarea[name=content]', form).val()){
		alert('Missing Content!');
		$('textarea[name=content]', form).focus();
		return false;
	}
	
	return true;
}

function open_confirm(url){
	var width = 300;
	var height = 150;
	window.open(url, '', 'top='+(screen.availHeight*0.5-height*0.5)+',left='+(screen.availWidth*0.5-width*0.5)+',width='+width+',height='+height+',resizable=0,scrollbars=1');
	return false;
}