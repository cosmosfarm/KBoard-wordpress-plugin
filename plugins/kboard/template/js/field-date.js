/**
 * @author https://www.cosmosfarm.com
 */

jQuery(document).ready(function(){
	if(typeof jQuery('.datepicker').datepicker == 'function'){
		if(kboard_settings.locale == 'ko_KR'){
			jQuery('.datepicker').datepicker({
				closeText : '닫기',
				prevText : '이전달',
				nextText : '다음달',
				currentText : '오늘',
				monthNames : [ '1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월' ],
				monthNamesShort : [ '1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월' ],
				dayNames : [ '일', '월', '화', '수', '목', '금', '토' ],
				dayNamesShort : [ '일', '월', '화', '수', '목', '금', '토' ],
				dayNamesMin : [ '일', '월', '화', '수', '목', '금', '토' ],
				weekHeader : 'Wk',
				dateFormat : 'yy-mm-dd',
				firstDay : 0,
				isRTL : false,
				duration : 0,
				showAnim : 'show',
				showMonthAfterYear : true,
				yearSuffix : '년'
			});
		}
		else{
			jQuery('.datepicker').datepicker({dateFormat : 'yy-mm-dd'});
		}
	}
});