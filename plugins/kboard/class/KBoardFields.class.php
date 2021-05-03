<?php
/**
 * KBoard 필드
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBoardFields {
	
	var $board;
	var $default_fields = array();
	var $extends_fields = array();
	var $skin_fields = array();
	
	public function __construct($value){
		if($value){
			$this->setBoardID($value);
		}
		
		$this->default_fields = array(
			'title' => array(
				'field_type' => 'title',
				'field_label' => __('Title', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-title',
				'meta_key' => 'title',
				'permission' => 'all',
				'roles' => array(),
				'default_value' => '',
				'placeholder' => '',
				'description' => '',
				'close_button' => ''
			),
			'option' => array(
				'field_type' => 'option',
				'field_label' => __('Options', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-option',
				'meta_key' => 'option',
				'secret_permission' => '',
				'secret' => array(),
				'notice_permission' => 'roles',
				'notice'=> array('administrator'),
				'description' => '',
				'close_button' => 'yes'
			),
			'author' => array(
				'field_type' => 'author',
				'field_label' => __('Author', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-author',
				'meta_key' => 'author',
				'permission' => '',
				'default_value' => '',
				'placeholder' => '',
				'description' => '',
				'close_button' => ''
			),
			'category1' => array(
				'field_type' => 'category1',
				'field_label' => __('Category', 'kboard').'1',
				'field_name' => '',
				'class' => 'kboard-attr-category1',
				'meta_key' => 'category1',
				'permission' => '',
				'roles' => array(),
				'default_value' => '',
				'description' => '',
				'required' => '',
				'close_button' => 'yes'
			),
			'category2' => array(
				'field_type' => 'category2',
				'field_label' => __('Category', 'kboard').'2',
				'field_name' => '',
				'class' => 'kboard-attr-category2',
				'meta_key' => 'category2',
				'permission' => '',
				'roles' => array(),
				'default_value' => '',
				'description' => '',
				'required' => '',
				'close_button' => 'yes'
			),
			'tree_category' => array(
				'field_type' => 'tree_category',
				'field_label' => __('Tree Category', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-tree-category',
				'meta_key' => 'tree_category',
				'permission' => '',
				'roles' => array(),
				'option_field' => true,
				'description' => '',
				'close_button' => 'yes'
			),
			'captcha' => array(
				'field_type' => 'captcha',
				'field_label' => __('Captcha', 'kboard'),
				'class' => 'kboard-attr-captcha',
				'meta_key' => 'captcha',
				'description' => '',
				'close_button' => 'yes'
			),
			'content' => array(
				'field_type' => 'content',
				'field_label' => __('Content', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-content',
				'meta_key' => 'content',
				'placeholder' => '',
				'description' => '',
				'required' => '',
				'close_button' => 'yes'
			),
			'media' => array(
				'field_type' => 'media',
				'field_label' => __('Photos', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-media',
				'meta_key' => 'media',
				'permission' => '',
				'roles' => array(),
				'description' => '',
				'close_button' => 'yes'
			),
			'thumbnail' => array(
				'field_type' => 'thumbnail',
				'field_label' => __('Thumbnail', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-thumbnail',
				'meta_key' => 'thumbnail',
				'permission' => '',
				'roles' => array(),
				'description' => '',
				'close_button' => 'yes'
			),
			'attach' => array(
				'field_type' => 'attach',
				'field_label' => __('Attachment', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-attach',
				'meta_key' => 'attach',
				'permission' => '',
				'roles' => array(),
				'description' => '',
				'close_button' => 'yes'
			),
			'search' => array(
				'field_type' => 'search',
				'field_label' => __('WP Search', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-search',
				'meta_key' => 'search',
				'permission' => '',
				'roles' => array(),
				'default_value' => '',
				'description' => '',
				'hidden' => '',
				'close_button' => ''
			),
			'ip' => array(
				'field_type' => 'ip',
				'field_label' => __('IP Address', 'kboard'),
				'class' => 'kboard-attr-ip',
				'kboard_extends' => '',
				'meta_key' => 'ip',
				'show_document' => '',
				'option_field' => true,
				'close_button' => 'yes'
			)
		);
		
		$this->extends_fields = array(
			'text' => array(
				'field_type' => 'text',
				'field_label' => __('Text/Hidden', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-text',
				'meta_key' => '',
				'permission' => '',
				'roles' => array(),
				'default_value' => '',
				'placeholder' => '',
				'description' => '',
				'required' => '',
				'show_document' => '',
				'hidden' => '',
				'close_button' => 'yes'
			),
			'select' => array(
				'field_type' => 'select',
				'field_label' => __('Select Box', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-select',
				'meta_key' => '',
				'row' => array(),
				'default_value' => '',
				'permission' => '',
				'roles' => array(),
				'description' => '',
				'required' => '',
				'show_document' => '',
				'close_button' => 'yes'
			),
			'radio' => array(
				'field_type' => 'radio',
				'field_label' => __('Radio Button', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-radio',
				'meta_key' => '',
				'row' => array(),
				'default_value' => '',
				'permission' => '',
				'roles' => array(),
				'description' => '',
				'required' => '',
				'show_document' => '',
				'close_button' => 'yes'
			),
			'checkbox' => array(
				'field_type' => 'checkbox',
				'field_label' => __('Checkbox', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-checkbox',
				'meta_key' => '',
				'row' => array(),
				'permission' => '',
				'roles' => array(),
				'description' => '',
				'required' => '',
				'show_document' => '',
				'close_button' => 'yes'
			),
			'textarea' => array(
				'field_type' => 'textarea',
				'field_label' => __('Textarea', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-textarea',
				'meta_key' => '',
				'permission' => '',
				'roles' => array(),
				'default_value' => '',
				'placeholder' => '',
				'required' => '',
				'show_document' => '',
				'description' => '',
				'close_button' => 'yes'
			),
			'file' => array(
				'field_type' => 'file',
				'field_label' => __('File', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-file',
				'meta_key' => '',
				'permission' => '',
				'roles' => array(),
				'description' => '',
				'show_document' => '',
				'close_button' => 'yes'
			),
			'wp_editor' => array(
				'field_type' => 'wp_editor',
				'field_label' => __('WP Editor', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-wp-editor',
				'meta_key' => '',
				'permission' => '',
				'roles' => array(),
				'default_value' => '',
				'description' => '',
				'show_document' => '',
				'close_button' => 'yes'
			),
			'html' => array(
				'field_type' => 'html',
				'field_label' => __('HTML', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-html',
				'meta_key' => '',
				'permission' => '',
				'roles' => array(),
				'default_value' => '',
				'description' => '',
				'show_document' => '',
				'close_button' => 'yes',
				'html' => ''
			),
			'shortcode' => array(
				'field_type' => 'shortcode',
				'field_label' => __('Shortcode', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-shortcode',
				'meta_key' => '',
				'permission' => '',
				'roles' => array(),
				'default_value' => '',
				'description' => '',
				'show_document' => '',
				'close_button' => 'yes',
				'shortcode' => ''
			),
			'date' => array(
				'field_type' => 'date',
				'field_label' => __('Date Select', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-date',
				'meta_key' => '',
				'permission' => '',
				'roles' => array(),
				'default_value' => '',
				'description' => '',
				'show_document' => '',
				'close_button' => 'yes'
			),
			'time' => array(
				'field_type' => 'time',
				'field_label' => __('Time Select', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-time',
				'meta_key' => '',
				'permission' => '',
				'roles' => array(),
				'default_value' => '',
				'description' => '',
				'show_document' => '',
				'close_button' => 'yes'
			),
			'email' => array(
				'field_type' => 'email',
				'field_label' => __('Email', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-email',
				'meta_key' => '',
				'permission' => '',
				'roles' => array(),
				'default_value' => '',
				'placeholder' => '',
				'description' => '',
				'required' => '',
				'show_document' => '',
				'hidden' => '',
				'close_button' => 'yes'
			),
			'address' => array(
				'field_type' => 'address',
				'field_label' => __('Address', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-address',
				'meta_key' => '',
				'permission' => '',
				'roles' => array(),
				'description' => '',
				'required' => '',
				'show_document' => '',
				'close_button' => 'yes'
			),
			/*
			'color' => array(
				'field_type' => 'color',
				'field_label' => __('Color Select', 'kboard'),
				'field_name' => '',
				'class' => 'kboard-attr-color',
				'meta_key' => '',
				'permission' => '',
				'roles' => array(),
				'default_value' => '',
				'description' => '',
				'show_document' => '',
				'close_button' => 'yes'
			)
			*/
		);
	}
	
	/**
	 * 게시판 아이디값을 입력받는다.
	 * @param int $board_id
	 */
	public function setBoardID($board){
		if(is_int($board)){
			
		}
		else{
			$this->board = $board;
			$this->setFields($board->meta->skin_fields);
		}
	}
	
	/**
	 * 필드 정보를 받는다.
	 * @param array|string $skin_fields
	 */
	public function setFields($skin_fields){
		if(is_array($skin_fields)){
			$this->skin_fields = $skin_fields;
		}
		else{
			$this->skin_fields = unserialize($skin_fields);
		}
		
		if($this->skin_fields){
			foreach($this->skin_fields as $key=>$item){
				if(!(isset($item['meta_key']) && $item['meta_key'])){
					$this->skin_fields[$key]['meta_key'] = $key;
				}
			}
		}
	}
	
	/**
	 * 저장되지 않은 KBoard 기본 필드를 반환한다.
	 * @return array
	 */
	public function getDefaultFields(){
		$default_fields = apply_filters('kboard_admin_default_fields', $this->default_fields, $this->board);
		
		foreach($default_fields as $key=>$value){
			if($this->skin_fields){
				if(isset($this->skin_fields[$key])){
					unset($default_fields[$key]);
				}
			}
			else{
				if(!isset($value['kboard_extends'])){
					unset($default_fields[$key]);
				}
			}
		}
		
		return $default_fields;
	}
	
	/**
	 * 확장 필드를 반환한다.
	 * @return array
	 */
	public function getExtensionFields(){
		return apply_filters('kboard_admin_extends_fields', $this->extends_fields, $this->board);
	}
	
	/**
	 * 스킨 필드를 반환한다.
	 * @return array
	 */
	public function getSkinFields(){
		$fields = array();
		
		if($this->skin_fields){
			$fields = $this->skin_fields;
		}
		else{
			$fields = $this->default_fields;
			foreach($fields as $key=>$value){
				if(isset($value['kboard_extends'])){
					unset($fields[$key]);
				}
			}
		}
		
		return apply_filters('kboard_skin_fields', $fields, $this->board);
	}
	
	/**
	 * KBoard 기본 필드인지 확인한다.
	 * @param string $fields_type
	 * @return string
	 */
	public function isDefaultFields($fields_type){
		$default_fields = apply_filters('kboard_admin_default_fields', $this->default_fields, $this->board);
		
		if(isset($default_fields[$fields_type])){
			return 'default';
		}
		return 'extends';
	}
	
	/**
	 * 입력 필드에 여러 줄을 입력하는 필드인지 확인한다.
	 * @param string $fields_type
	 * @return string
	 */
	public function isMultiLineFields($fields_type){
		$multi_line_fields = apply_filters('kboard_multi_line_fields_fields', array('html', 'shortcode'), $this->board);
		
		if(in_array($fields_type, $multi_line_fields)){
			return true;
		}
		
		return false;
	}
	
	/**
	 * 필드의 레이아웃을 반환한다.
	 * @param array $field
	 * @param KBContent $content
	 * @param KBoardBuilder $boardBuilder
	 * @return string
	 */
	public function getTemplate($field, $content='', $boardBuilder=''){
		$template = '';
		$permission = (isset($field['permission']) && $field['permission']) ? $field['permission'] : '';
		$roles = (isset($field['roles']) && $field['roles']) ? $field['roles'] : '';
		$meta_key = (isset($field['meta_key']) && $field['meta_key']) ? sanitize_key($field['meta_key']) : '';
		
		if($this->isUseFields($permission, $roles) && $meta_key){
			if(!$content){
				$content = new KBContent();
			}
			
			$field = apply_filters('kboard_get_template_field_data', $field, $content, $this->board);
			
			$field_name = (isset($field['field_name']) && $field['field_name']) ? $field['field_name'] : $this->getFieldLabel($field);
			$required = (isset($field['required']) && $field['required']) ? 'required' : '';
			$placeholder = (isset($field['placeholder']) && $field['placeholder']) ? $field['placeholder'] : '';
			$wordpress_search = '';
			$default_value = (isset($field['default_value']) && $field['default_value']) ? $field['default_value'] : '';
			$html = (isset($field['html']) && $field['html']) ? $field['html'] : '';
			$shortcode = (isset($field['shortcode']) && $field['shortcode']) ? $field['shortcode'] : '';
			$row = false;
			
			$default_value_list = array();
			if(isset($field['row']) && $field['row']){
				foreach($field['row'] as $item){
					if(isset($item['label']) && $item['label']){
						$row = true;
						
						if(isset($item['default_value']) && $item['default_value']){
							$default_value_list[] = $item['label'];
						}
					}
				}
			}
			
			if($default_value_list){
				$default_value = $default_value_list;
			}
			
			if($field['field_type'] == 'search'){
				if($content->search){
					$wordpress_search = $content->search;
				}
				else if(isset($field['default_value']) && $field['default_value']){
					$wordpress_search = $field['default_value'];
				}
			}
			
			$order = new KBOrder();
			$order->board = $this->board;
			$order->board_id = $this->board->id;
			
			$url = new KBUrl();
			$url->setBoard($this->board);
			
			$skin = KBoardSkin::getInstance();
			
			if(!$boardBuilder){
				$boardBuilder = new KBoardBuilder($this->board->id);
				$boardBuilder->setSkin($this->board->skin);
				if(wp_is_mobile() && $this->board->meta->mobile_page_rpp){
					$builder->setRpp($this->board->meta->mobile_page_rpp);
				}
				else{
					$boardBuilder->setRpp($this->board->page_rpp);
				}
				$boardBuilder->board = $this->board;
			}
			
			if(strpos($html, '#{ESC_ATTR_VALUE}') !== false){
				$value = $content->option->{$meta_key} ? esc_attr($content->option->{$meta_key}) : esc_attr($default_value);
				$html = str_replace('#{ESC_ATTR_VALUE}', $value, $html);
			}
			if(strpos($html, '#{ESC_TEXTAREA_VALUE}') !== false){
				$value = $content->option->{$meta_key} ? esc_textarea($content->option->{$meta_key}) : esc_textarea($default_value);
				$html = str_replace('#{ESC_TEXTAREA_VALUE}', $value, $html);
			}
			if(strpos($html, '#{ESC_HTML_VALUE}') !== false){
				$value = $content->option->{$meta_key} ? esc_html($content->option->{$meta_key}) : esc_html($default_value);
				$html = str_replace('#{ESC_HTML_VALUE}', $value, $html);
			}
			
			$parent = new KBContent();
			$parent->initWithUID($content->parent_uid);
			
			$vars = array(
				'field' => $field,
				'meta_key' => $meta_key,
				'field_name' => $field_name,
				'required' => $required,
				'placeholder' => $placeholder,
				'row' => $row,
				'wordpress_search' => $wordpress_search,
				'default_value' => $default_value,
				'html' => $html,
				'shortcode' => $shortcode,
				'board' => $this->board,
				'content' => $content,
				'parent' => $parent,
				'fields' => $this,
				'order' => $order,
				'url' => $url,
				'skin' => $skin,
				'skin_path' => $skin->url($this->board->skin),
				'skin_dir' => $skin->dir($this->board->skin),
				'boardBuilder' => $boardBuilder
			);
			
			ob_start();
			
			do_action('kboard_skin_field_before', $field, $content, $this->board);
			do_action("kboard_skin_field_before_{$meta_key}", $field, $content, $this->board);
			
			if($skin->fileExists($this->board->skin, "editor-field-{$meta_key}.php")){
				$field_html = $skin->load($this->board->skin, "editor-field-{$meta_key}.php", $vars);
			}
			else{
				$field_html = $skin->load($this->board->skin, 'editor-fields.php', $vars);
			}
			
			if(!$field_html){
				$field_html = $skin->loadTemplate('editor-fields.php', $vars);
			}
			
			echo apply_filters('kboard_get_template_field_html', $field_html, $field, $content, $this->board);
			
			do_action("kboard_skin_field_after_{$meta_key}", $field, $content, $this->board);
			do_action('kboard_skin_field_after', $field, $content, $this->board);
			
			$template = ob_get_clean();
		}
		
		return $template;
	}
	
	/**
	 * 번역된 필드의 레이블을 반환한다.
	 * @param array $field
	 * @return string
	 */
	public function getFieldLabel($field){
		$field_type = $field['field_type'];
		
		$fields = apply_filters('kboard_admin_default_fields', $this->default_fields, $this->board);
		if(isset($fields[$field_type])){
			return $fields[$field_type]['field_label'];
		}
		
		$fields = apply_filters('kboard_admin_extends_fields', $this->extends_fields, $this->board);
		if(isset($fields[$field_type])){
			return $fields[$field_type]['field_label'];
		}
		
		return $field['field_label'];
	}
	
	/**
	 * 저장된 값이 있는지 체크한다.
	 * @param array $row
	 * @return boolean
	 */
	public function valueExists($row){
		foreach($row as $key=>$item){
			if(isset($item['label']) && $item['label']){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 기본값이나 저장된 값이 있는지 확인한다.
	 * @param array|string $value
	 * @param string $label
	 * @return boolean
	 */
	public function isSavedOption($value, $label){
		if(is_array($value) && in_array($label, $value)){
			return true;
		}
		else if($value == $label){
			return true;
		}
		return false;
	}
	
	/**
	 * 입력 필드 이름을 반환한다.
	 * @param string $name
	 * @return string
	 */
	public function getOptionFieldName($name){
		$name = sanitize_key($name);
		return KBContent::$SKIN_OPTION_PREFIX . $name;
	}
	
	/**
	 * 입력 필드를 사용할 수 있는 권한인지 확인한다.
	 * @param string $name
	 * @return boolean
	 */
	public function isUseFields($permission, $roles){
		$board = $this->board;
		if($board->isAdmin()){
			return true;
		}
		
		switch($permission){
			case 'all': return true;
			case 'author': return is_user_logged_in() ? true : false;
			case 'roles':
				if(is_user_logged_in()){
					if(array_intersect($roles, kboard_current_user_roles())){
						return true;
					}
				}
				return false;
			default: return true;
		}
	}
	
	/**
	 * 게시글 본문 페이지에 표시할 옵션값 태그를 반환한다.
	 * @param KBContent $content
	 * @return string
	 */
	public function getDocumentValuesHTML($content){
		$option_value_list = array();
		
		$board = $this->board;
		$skin_fields = $board->fields()->getSkinFields();
		
		foreach($skin_fields as $key=>$field){
			$field = apply_filters('kboard_document_add_option_value_field_data', $field, $content, $board);
			
			$meta_key = (isset($field['meta_key'])&&$field['meta_key']) ? $field['meta_key'] : $key;
			$field_type = (isset($field['field_type'])&&$field['field_type']) ? $field['field_type'] : '';
			$default_value = (isset($field['default_value'])&&$field['default_value']) ? $field['default_value'] : '';
			
			if($field_type == 'file'){
				$option_value = isset($content->attach->{$meta_key}) ? $content->attach->{$meta_key} : array();
			}
			else if($field_type == 'address'){
				$option_value = $content->option->{$meta_key.'_address_1'};
			}
			else{
				$option_value = $content->option->{$meta_key};
			}
			
			if(isset($field['show_document']) && $field['show_document'] && $option_value){
				if(is_array($option_value) && $field_type != 'file'){
					$separator = apply_filters('kboard_document_add_option_value_separator', ', ', $field, $content, $board);
					$option_value = implode($separator, $option_value);
				}
				
				if(!(isset($field['field_name']) && $field['field_name'])){
					$field['field_name'] = $this->getFieldLabel($field);
				}
				
				$html = '<div class="kboard-document-add-option-value meta-key-' . esc_attr($meta_key) . '"><span class="option-name">' . $field['field_name'] . '</span><span class="option-separator"> : </span>';
				
				if($field_type == 'file'){
					if($content->execute_action == 'insert'){
						$download_button = $option_value[1];
					}
					else{
						$url = new KBUrl();
						$download_button = "<button type=\"button\" class=\"kboard-button-action kboard-button-download\" onclick=\"window.location.href='{$url->getDownloadURLWithAttach($content->uid, $meta_key)}'\" title=\"\">{$option_value[1]}</button>";
					}
					$html .= '<span class="option-value">' . $download_button . '</span></div><hr>';
				}
				else if($field_type == 'address'){
					$html .= '<span class="option-value">(' . $content->option->{$meta_key.'_postcode'} . ') ' . $content->option->{$meta_key.'_address_1'} . ' ' . $content->option->{$meta_key.'_address_2'} . '</span></div><hr>';
				}
				else{
					$html .= '<span class="option-value">' . nl2br($option_value) . '</span></div><hr>';
				}
				
				$option_value_list[$meta_key] = apply_filters('kboard_document_add_option_value_field_html', $html, $field, $content, $board);
			}
		}
		
		if($option_value_list){
			return '<div class="kboard-document-add-option-value-wrap">' . implode('', $option_value_list) . '</div>';
		}
		return '';
	}
	
	/**
	 * 게시글 본문 페이지에 표시할 옵션값을 반환한다.
	 * @param KBContent $content
	 * @return array
	 */
	public function getDocumentValues($content){
		$option_value_list = array();
		
		$board = $this->board;
		$skin_fields = $board->fields()->getSkinFields();
		
		foreach($skin_fields as $key=>$field){
			$field = apply_filters('kboard_document_add_option_value_field_data', $field, $content, $board);
			
			$meta_key = (isset($field['meta_key']) && $field['meta_key']) ? $field['meta_key'] : $key;
			$option_value = $content->option->{$meta_key};
			
			if(isset($field['show_document']) && $field['show_document'] && $option_value){
				if(!(isset($field['field_name']) && $field['field_name'])){
					$field['field_name'] = $this->getFieldLabel($field);
				}
				
				$option_value_list[$meta_key] = array('field'=>$field, 'value'=>$option_value);
			}
		}
		
		return $option_value_list;
	}
	
	/**
	 * 게시글에 표시할 첨부파일을 반환한다.
	 * @param KBContent $content
	 * @return object
	 */
	public function getAttachmentList($content){
		$skin_fields = $this->getSkinFields();
		$attach_list = clone $content->attach;
		
		$meta_key_list = array();
		foreach($skin_fields as $key=>$field){
			$meta_key_list[] = (isset($field['meta_key']) && $field['meta_key']) ? $field['meta_key'] : $key;
		}
		
		foreach($attach_list as $key=>$attach){
			if(!in_array($key, $meta_key_list) && substr($key, 0, 4) != 'file'){
				unset($attach_list->$key);
			}
		}
		
		return $attach_list ? $attach_list : new stdClass();
	}
}