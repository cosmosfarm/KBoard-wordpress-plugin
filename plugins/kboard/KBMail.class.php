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
		$headers .= 'Content-type: text/html; charset=euckr' . "\r\n";
		$headers .= "To: " . $this->to . "\r\n";
		$headers .= "From: " . $admin_email . "\r\n";

		$title = '[KBoard 신규등록] ' . $this->title;
		$content = nl2br($this->content) . '<br><br><a href="'.site_url().'" onclick="window.open(this.href); return false;">'.site_url().'</a>';

		$result = mail($to, iconv('utf8', 'euckr', $title), iconv('utf8', 'euckr', $content), iconv('utf8', 'euckr', $headers));
		return $result;
	}
}
?>