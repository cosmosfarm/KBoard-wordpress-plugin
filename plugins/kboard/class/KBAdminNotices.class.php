<?php
/**
 * KBoard Admin Notices
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBAdminNotices {
	
	public static function kboard_updated_notice(){
		add_action('admin_notices', array('KBAdminNotices', 'kboard_updated_notice_message'));
	}
	
	public static function kboard_updated_notice_message(){
		echo '<div class="notice notice-success"><p>KBoard 게시판 : ' . KBOARD_VERSION . ' 버전으로 업데이트 되었습니다. - <a href="https://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href);return false;">홈페이지 열기</a></p></div>';
	}
	
	public static function get_upload_folder_not_writable_message(){
		echo '<div class="notice notice-error"><p>KBoard 게시판 : 디렉토리 '.WP_CONTENT_DIR.'/uploads'.'에 파일을 쓸 수 없습니다. 디렉토리가 존재하지 않거나 쓰기 권한이 있는지 확인해주세요. - <a href="https://www.cosmosfarm.com/threads" onclick="window.open(this.href);return false;">이 알림에 대해서 질문/검색 하기</a></p></div>';
	}
	
	public static function get_kboard_update_notice_message_message($version){
		echo '<div class="notice notice-info"><p>KBoard 게시판 : ' . $version . ' 버전으로 업데이트 가능합니다. - <a href="'.admin_url('/admin.php?page=kboard_updates').'">업데이트</a> 또는 <a href="https://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href);return false;">홈페이지 열기</a></p></div>';
	}
	
	public static function comments_updated_notice(){
		add_action('admin_notices', array('KBAdminNotices', 'comments_updated_notice_message'));
	}
	
	public static function comments_updated_notice_message(){
		echo '<div class="notice notice-success"><p>KBoard 댓글 : ' . KBOARD_COMMNETS_VERSION . ' 버전으로 업데이트 되었습니다. - <a href="https://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href);return false;">홈페이지 열기</a></p></div>';
	}
	
	public static function get_comments_update_notice_message_message($version){
		return '<div class="notice notice-info"><p>KBoard 댓글 : ' . $version . ' 버전으로 업데이트 가능합니다. - <a href="'.admin_url('/admin.php?page=kboard_updates').'">업데이트</a> 또는 <a href="https://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href);return false;">홈페이지 열기</a></p></div>';
	}
}