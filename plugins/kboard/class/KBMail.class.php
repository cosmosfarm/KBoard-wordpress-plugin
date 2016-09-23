<?php
/**
 * KBoard 메일
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBMail {
	
	var $from_name;
	var $from;
	var $to;
	var $title;
	var $content;
	var $url;
	
	public function __construct(){
		global $wpms_options;
		if($wpms_options === null){
			$this->from_name = get_option('blogname');
			$this->from = get_option('admin_email');
		}
	}
	
	public function send(){
		add_filter('wp_mail_content_type', array($this, 'getHtmlContentType'));
		
		if($this->from_name && $this->from) $headers[] = "From: {$this->from_name} <{$this->from}>";
		else if($this->from) $headers[] = "From: {$this->from}";
		else $headers = '';
		
		$message = preg_replace("/(<(|\/)(table|th|tr|td).*>)(<br \/>)/","\$1", nl2br($this->content)) . '<p><a href="' . $this->url . '" target="_blank">' . $this->url . '</a></p>';
		
		$result = wp_mail($this->to, $this->title, $message, $headers);
		
		remove_filter('wp_mail_content_type', array($this, 'getHtmlContentType'));
		return $result;
	}
	
	public function getHtmlContentType(){
		return 'text/html';
	}
}
?>