<?php
/**
 * 시스템 메일 보내기
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBMail {
	
	var $to;
	var $title;
	var $content;
	
	public function send(){
		$title = '[KBoard 신규등록] ' . $this->title;
		$content = nl2br($this->content) . '<br><br><a href="'.site_url().'" onclick="window.open(this.href); return false;">'.site_url().'</a>';
		wp_mail($this->to, $title, $content, 'Content-type: text/html');
	}
}
?>