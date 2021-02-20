<?php
/**
 * KBoard 메일
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBMail {
	
	var $headers;
	var $from_name;
	var $from;
	var $to;
	var $title;
	var $content;
	var $url;
	var $url_name;
	var $attachments = array();
	
	public function __construct(){
		global $wpms_options;
		if($wpms_options === null){
			$this->from_name = get_option('blogname');
			$this->from = get_option('admin_email');
		}
	}
	
	public function send(){
		add_filter('wp_mail_content_type', array($this, 'getHtmlContentType'));
		add_filter('wp_mail', array($this, 'message_template'));
		
		$content_dir_name = basename(WP_CONTENT_DIR);
		$kboard_attched_dir = "/{$content_dir_name}/uploads/kboard_attached";
		
		$message = kboard_content_paragraph_breaks($this->content);
		$message = str_replace($kboard_attched_dir, site_url($kboard_attched_dir), $message);
		
		$result = wp_mail($this->to, $this->title, $message, $this->headers, $this->attachments);
		
		remove_filter('wp_mail', array($this, 'message_template'));
		remove_filter('wp_mail_content_type', array($this, 'getHtmlContentType'));
		
		return $result;
	}
	
	public function getHtmlContentType(){
		return 'text/html';
	}
	
	public function message_template($args){
		$subject = $args['subject'];
		$message = $args['message'];
		$message = str_replace('<p>', "<p style=\"font-family: 'Apple SD Gothic Neo','Malgun Gothic',arial,sans-serif; font-size: 14px; font-weight: normal; margin: 0; margin-bottom: 15px;\">", $message);
		
		$call_to_actions = array();
		if($this->url){
			$call_to_actions = array(
				($this->url_name ? $this->url_name : $this->url) => esc_url($this->url)
			);
		}
		
		ob_start();
		include KBOARD_DIR_PATH . '/assets/email/template.php';
		$args['message'] = ob_get_clean();
		
		return $args;
	}
}