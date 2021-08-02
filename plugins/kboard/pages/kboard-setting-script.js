/**
 * @author https://www.cosmosfarm.com
 */

jQuery(document).ready(function(){
	jQuery('ul.sortable').nestedSortable({
		update: function(){
			kboard_tree_category_sortable(jQuery('ul.sortable').nestedSortable('toArray'));
		},
		listType: 'ul',
		forcePlaceholderSize: true,
		items: 'li',
		opacity: 0.5,
		placeholder: 'placeholder',
		revert: 0,
		tabSize: 25,
		toleranceElement: '> div',
		maxLevels: 100,
		isTree: true,
		expandOnHover: 700,
		startCollapsed: false
	});
	
	jQuery('ul.kboard-fields-list, ul.kboard-fields-sortable').sortable({
		connectWith: '.connected-sortable',
		handle: '.kboard-field-handle',
		cancel: '',
		forcePlaceholderSize: true,
		placeholder: 'placeholder',
		remove: function(event, li){
			var type = jQuery(li.item).hasClass('default');
			var field = jQuery(li.item).find('.field_data.field_type').val();
			var uniq_id;

			if(type){
				uniq_id = jQuery(li.item).find('.field_data.field_type').val();
			}
			else{
				uniq_id = uniqid();
			}

			jQuery(li.item).find('.field_type').attr('name', 'fields['+uniq_id+'][field_type]');
			jQuery(li.item).find('.field_name').attr('name', 'fields['+uniq_id+'][field_name]');
			jQuery(li.item).find('.meta_key').attr('name', 'fields['+uniq_id+'][meta_key]');
			jQuery(li.item).find('.role').attr('name', 'fields['+uniq_id+'][role]');
			jQuery(li.item).find('.placeholder').attr('name', 'fields['+uniq_id+'][placeholder]');
			jQuery(li.item).find('.required').attr('name', 'fields['+uniq_id+'][required]');
			jQuery(li.item).find('.show_document').attr('name', 'fields['+uniq_id+'][show_document]');
			jQuery(li.item).find('.field_label').attr('name', 'fields['+uniq_id+'][field_label]');
			jQuery(li.item).find('.class').attr('name', 'fields['+uniq_id+'][class]');
			jQuery(li.item).find('.default_value').attr('name', 'fields['+uniq_id+'][default_value]');
			jQuery(li.item).find('.html').attr('name', 'fields['+uniq_id+'][html]');
			jQuery(li.item).find('.shortcode').attr('name', 'fields['+uniq_id+'][shortcode]');
			jQuery(li.item).find('.hidden').attr('name', 'fields['+uniq_id+'][hidden]');
			jQuery(li.item).find('.option_field').attr('name', 'fields['+uniq_id+'][option_field]');
			jQuery(li.item).find('.field_description').attr('name', 'fields['+uniq_id+'][description]');
			jQuery(li.item).find('.close_button').attr('name', 'fields['+uniq_id+'][close_button]');
			
			if(jQuery(li.item).find('.option-wrap').length){
				jQuery(li.item).find('.option-wrap').each(function(index, element){
					var option_id = uniqid();
					jQuery(element).find('.option_label').attr('name', 'fields['+uniq_id+'][row]['+option_id+'][label]');
					jQuery(element).find('.default_value').attr('name', 'fields['+uniq_id+'][default_value]');
					if(field == 'checkbox'){
						jQuery(element).find('.default_value').attr('name', 'fields['+uniq_id+'][row]['+option_id+'][default_value]');
					}
					if(field == 'radio' || field == 'select'){
						jQuery(element).find('.default_value').val(option_id);
					}
				});
			}
			
			jQuery(li.item).find('.field_data.roles').attr('name', 'fields['+uniq_id+'][permission]');
			jQuery(li.item).find('.field_data.secret-roles').attr('name', 'fields['+uniq_id+'][secret_permission]');
			jQuery(li.item).find('.field_data.notice-roles').attr('name', 'fields['+uniq_id+'][notice_permission]');
			jQuery(li.item).find('.roles_checkbox').each(function(index, element){
				jQuery(element).attr('name', 'fields['+uniq_id+'][roles][]');
			});
			jQuery(li.item).find('.secret_checkbox').each(function(index, element){
				jQuery(element).attr('name', 'fields['+uniq_id+'][secret][]');
			});
			jQuery(li.item).find('.notice_checkbox').each(function(index, element){
				jQuery(element).attr('name', 'fields['+uniq_id+'][notice][]');
			});
			
			jQuery(li.item).addClass(uniq_id);
			jQuery(li.item).prepend('<input type="hidden" class="parent_id" value="'+uniq_id+'">');

			if(!type){
				li.item.clone().insertAfter(li.item);
 				jQuery(this).sortable('cancel');
 				jQuery(li.item).find('.field_data').attr('name', '');
			}
			jQuery(li.item).removeClass(uniq_id);

			return li.item.clone();
		},
	});
	
	jQuery('.kboard-fields-header').click(function(){
		kboard_fields_toggle(this, 'list-active');
	});
	
	jQuery('.kboard-fields').on('click', '.toggle', function(){
		kboard_fields_toggle(this, 'active');
	});
	
	jQuery('.kboard-fields').on('click', '.fields-remove', function(event){
		if(jQuery(this).closest('li').hasClass('default')){
			jQuery('.kboard-fields-default').addClass('list-active');
			jQuery(this).closest('li').removeClass('active');
			jQuery(this).closest('li').find('.field_data').attr('name', '');
			jQuery('.kboard-fields-default .kboard-fields-list').append(jQuery(this).closest('li'));
		}
		else{
			jQuery(this).closest('li').remove();
		}
	});
	
	jQuery('#new-category-name').keypress(function(event){
		if(event.keyCode === 10 || event.keyCode === 13){
			event.preventDefault();
		}
	});
	
	jQuery('#update-category-name').keypress(function(event){
		if(event.keyCode === 10 || event.keyCode === 13){
			event.preventDefault();
		}
	});

	jQuery(document).on('focus', '.extends.date .default_value', function(){
		jQuery(this).datepicker({
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
			yearSuffix : '년',
			changeYear:true,
			changeMonth:true
		});
	});
	jQuery(document).on('focus', '.extends.time .default_value', function(){
		jQuery(this).timepicker({'timeFormat': 'HH:mm:ss'});
	});
});

function kboard_radio_reset(obj){
	jQuery(obj).parents('.kboard-radio-reset').find('input[type=radio]').each(function(){
		jQuery(this).prop('checked', false);
	});
}
function kboard_skin_fields_reset(){
	if(confirm('입력필드 설정을 기본값으로 되돌릴까요? 기존에 저장된 내용을 잃을 수 있습니다.')){
		jQuery('.kboard-skin-fields').slideUp(function(){
			jQuery(this).html('');
			jQuery('#kboard-setting-form').submit();
		});
	}
}
function kboard_fields_toggle(element, active){
	if(jQuery(element).closest('li').hasClass(active)){
		jQuery(element).closest('li').removeClass(active);
	}
	else{
		jQuery(element).closest('li').addClass(active);
	}
}
function kboard_setting_tab_init(){
	var index = location.hash.slice(1).replace('tab-kboard-setting-', '');
	kboard_setting_tab_change(index);
}
kboard_setting_tab_init();
function kboard_setting_tab_change(index){
	jQuery('.tab-kboard').removeClass('nav-tab-active').eq(index).addClass('nav-tab-active');
	jQuery('.tab-kboard-setting').removeClass('tab-kboard-setting-active').eq(index).addClass('tab-kboard-setting-active');
	jQuery('input[name=tab_kboard_setting]').val(index);

	if(index == 3){
		jQuery('#kboard-setting-form .submit').hide();
	}
	else{
		jQuery('#kboard-setting-form .submit').show();
	}
}
function kboard_permission_roles_view(bind, value){
	if(value == 'roles'){
		jQuery(bind).removeClass('kboard-hide');
	}
	else{
		jQuery(bind).addClass('kboard-hide');
	}
	kboard_permission_list_check();
}
function kboard_page_open(){
	var permalink = jQuery('option:selected', 'select[name=auto_page]').data('permalink');
	if(permalink){
		window.open(permalink);
	}
}
function kboard_latest_target_page_open(){
	var permalink = jQuery('option:selected', 'select[name=latest_target_page]').data('permalink');
	if(permalink){
		window.open(permalink);
	}
}
function kboard_permission_list_check(message){
	if(jQuery('select[name=permission_list]').val()){
		jQuery('.kboard-permission-list-options-view').removeClass('kboard-hide');
		
		if(jQuery('select[name=permission_read]').val() == 'all' || jQuery('select[name=permission_write]').val() == 'all'){
			if(message){
				alert('읽기권한과 쓰기권한을 모두 로그인 사용자 이상으로 변경해주세요.');
			}
			jQuery('select[name=permission_list]').val('');
			jQuery('.kboard-permission-list-options-view').addClass('kboard-hide');
		}
	}
	else{
		jQuery('.kboard-permission-list-options-view').addClass('kboard-hide');
	}
}
function add_option(element){
	var label = '';
	var parent = jQuery(element).closest('.kboard-fields-sortable.connected-sortable').length;
	var parent_id = jQuery(element).closest('li').find('.parent_id').val();
	var uniq_id = uniqid();
	var field_type = 'radio';
	var name = jQuery(element).closest('li').find('.field_data.field_type').val();
	var value = uniq_id;

	if(parent){
		label = 'fields['+parent_id+'][row]['+uniq_id+'][label]';
		name = 'fields['+parent_id+'][default_value]';
	}
	
	if(jQuery(element).closest('li').find('.field_data.field_type').val() == 'checkbox'){
		field_type = 'checkbox';
		name = 'fields['+parent_id+'][row]['+uniq_id+'][default_value]';
		value = '1';
	}
	
	jQuery(element).closest('.attr-row').after('<div class="attr-row option-wrap">'+
		'<div class="attr-name option"><label for="'+uniq_id+'_label">라벨</label></div>'+
		'<div class="attr-value">'+
		'<input type="text" name="'+label+'" id="'+uniq_id+'_label" class="field_data option_label"> '+
		'<button type="button" class="'+field_type+'" onclick="add_option(this)">+</button> '+
		'<button type="button" class="'+field_type+'" onclick="remove_option(this)">-</button> '+
		'<label><input type="'+field_type+'" name="'+name+'" class="field_data default_value" value="'+value+'"> 기본값'+
		'</label></div></div>'
	);
}
function remove_option(element){
	if(jQuery(element).closest('li').find('.attr-row.option-wrap').length == 1) return false;
	jQuery(element).parents('.attr-row').remove();
}
function kboard_fields_permission_roles_view(element){
	if(jQuery(element).val() == 'roles'){
		jQuery(element).siblings('.kboard-permission-read-roles-view').removeClass('kboard-hide');
	}
	else{
		jQuery(element).siblings('.kboard-permission-read-roles-view').addClass('kboard-hide');
	}
}
function kboard_tree_category_sortable(tree_category_serialize){
	var board_id = jQuery('input[name=board_id]').val();

	tree_category_serialize = JSON.stringify(tree_category_serialize);
	
	jQuery.post(ajaxurl, {action:'kboard_tree_category_sortable', board_id:board_id, tree_category_serialize:tree_category_serialize}, function(data){
		jQuery('.sortable li').remove();
		jQuery('.sortable').append(data.table_body);
	});
}
function kboard_tree_category_update(sub_action){
	var board_id = jQuery('input[name=board_id]').val();
	var category_name = '';
	var category_id = jQuery('#category-id').val();
	var current_parent_id = jQuery('#parent-id').val();
	var new_category_id = uniqid();
	
	if(sub_action == 'kboard_tree_category_create'){
		category_name = jQuery('#new-category-name').val();
		if(!category_name){
			return false;
		}
		else{
			jQuery('.sortable').append('<input type="hidden" name="tree_category['+new_category_id+'][id]" value="'+new_category_id+'">');
			jQuery('.sortable').append('<input type="hidden" name="tree_category['+new_category_id+'][category_name]" value="'+category_name+'">');
			jQuery('.sortable').append('<input type="hidden" name="tree_category['+new_category_id+'][parent_id]" value="">');
		}
	}
	
	if(sub_action == 'kboard_tree_category_update'){
		category_name = jQuery('#update-category-name').val();
		if(!category_name){
			return false;
		}
		else{
			jQuery('#tree-category-name-'+category_id).val(category_name);
		}
	}

	if(sub_action == 'kboard_tree_category_remove'){
		if(!category_id){
			return false;
		}
		else{
			jQuery('.kboard-tree-category-parents').each(function(index, element){
				if(category_id == jQuery(element).val()){
					jQuery(element).val(current_parent_id);
				}
			});
			jQuery('input[name="tree_category['+category_id+'][id]"]').remove();
			jQuery('input[name="tree_category['+category_id+'][category_name]"]').remove();
			jQuery('input[name="tree_category['+category_id+'][parent_id]"]').remove();
		}
	}

	var tree_category = jQuery('.sortable').find('input[name^="tree_category"]').serialize();
	
	jQuery.post(ajaxurl, {action:'kboard_tree_category_update', tree_category:tree_category, board_id:board_id, category_id:category_id}, function(data){
		jQuery('#new-category-name').val('');
		jQuery('#update-category-name').val('');
		jQuery('#new-category-name').focus();
		jQuery('.sortable li').remove();
		jQuery('.sortable input').remove();
		jQuery('.sortable').prepend(data.table_body);
	});
	return false;
}
function kboard_tree_category_edit_toggle(category_id, category_name, parent_id){
	jQuery('#parent-id').val(parent_id);
	
	jQuery('li .parent-id'+category_id).val(parent_id);
	jQuery('.kboard-update-tree-category').css('display', 'block');
		
	jQuery('#category-id').val(category_id);
	jQuery('.update_category_name').val(category_name);
	jQuery('#update-category-name').focus();
}
function kboard_csv_upload(){
	jQuery('input[name=action]', '#kboard-setting-form').val('kboard_csv_upload_execute');
	jQuery('#kboard-setting-form').submit();
}
function kboard_csv_upload(){
	jQuery('input[name=action]', '#kboard-setting-form').val('kboard_csv_upload_execute');
	jQuery('#kboard-setting-form').submit();
}
/**
 * JavaScript alternative of PHP uniqid()
 * original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 * revised by: Kankrelune (http://www.webfaktory.info/)
 * more: https://gist.github.com/ain/5638966
 */
function uniqid(prefix,more_entropy){if(typeof prefix==='undefined'){prefix=""}var retId;var formatSeed=function(seed,reqWidth){seed=parseInt(seed,10).toString(16);if(reqWidth<seed.length){return seed.slice(seed.length-reqWidth)}if(reqWidth>seed.length){return Array(1+(reqWidth-seed.length)).join('0')+seed}return seed};if(!this.php_js){this.php_js={}}if(!this.php_js.uniqidSeed){this.php_js.uniqidSeed=Math.floor(Math.random()*0x75bcd15)}this.php_js.uniqidSeed++;retId=prefix;retId+=formatSeed(parseInt(new Date().getTime()/1000,10),8);retId+=formatSeed(this.php_js.uniqidSeed,5);if(more_entropy){retId+=(Math.random()*10).toFixed(8).toString()}return retId}