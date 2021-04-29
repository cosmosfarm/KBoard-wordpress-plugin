/**
 * @author https://www.cosmosfarm.com
 */

function kboard_postcode_address_search(postcode, address_1, address_2){
	var width = 500;
	var height = 600;
	new daum.Postcode({
		width: width,
		height: height,
		oncomplete: function(data){
			jQuery('#'+postcode).val(data.zonecode);
			jQuery('#'+address_1).val(data.roadAddress);
			
			setTimeout(function(){
				jQuery('#'+address_2).focus();
			});
		}
	}).open({
		left: (screen.availWidth-width)*0.5,
		top: (screen.availHeight-height)*0.5
	});
}