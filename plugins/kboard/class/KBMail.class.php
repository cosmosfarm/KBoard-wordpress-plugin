<?php
/**
 * KBoard 메일
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBMail {

	var $to;
	var $title;
	var $content;

	public function send(){
		$admin_email = get_option('admin_email');
		
		if(is_array($this->to)){
			$this->to = implode(',', $this->to);
			$this->to = str_replace(' ', '', $this->to);
		}
		
		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=EUC-KR' . "\r\n";
		$headers .= 'To: ' . $this->to . "\r\n";
		$headers .= 'From: ' . $admin_email . "\r\n";
		
		$title = '['.__('KBoard new document', 'kboard').'] ' . $this->title;
		$content = nl2br(kboard_htmlclear($this->content)) . '<br><br><a href="'.site_url().'" onclick="window.open(this.href); return false;">'.site_url().'</a>';
		
		$result = mail($to, iconv('UTF-8', 'EUC-KR', $title), iconv('UTF-8', 'EUC-KR', $content), iconv('UTF-8', 'EUC-KR', $headers));
		return $result;
	}
}
?>