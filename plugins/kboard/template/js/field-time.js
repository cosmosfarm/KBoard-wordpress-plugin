/**
 * @author https://www.cosmosfarm.com
 */

jQuery(document).ready(function(){
	if(typeof jQuery('.timepicker').timepicker == 'function'){
		jQuery('.timepicker').timepicker({timeFormat:'H:mm', interval:60, zindex:'1'});
	}
});