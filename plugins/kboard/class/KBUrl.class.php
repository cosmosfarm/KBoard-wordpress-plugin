<?php
/**
 * KBoard 워드프레스 게시판 URL
 * @link www.cosmosfarm.com
 * @copyright Copyright 2020 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBUrl {
	
	private $path;
	private $data;
	
	var $board;
	var $is_latest = false;
	
	public function __construct($path=''){
		$this->board = new KBoard();
		
		if($path){
			$this->setPath($path);
		}
		else{
			$this->path = '';
		}
		
		return $this->init();
	}
	
	/**
	 * MOD, UID 값 초기화, URL을 재사용 할 때 오류를 방지한다.
	 * @return KBUrl
	 */
	public function init(){
		$this->data = $_GET;
		$this->data['mod'] = '';
		$this->data['uid'] = '';
		$this->data['rpp'] = '';
		$this->data['sort'] = '';
		$this->data['skin'] = '';
		$this->data['action'] = '';
		$this->data['base_url'] = '';
		$this->data['security'] = '';
		$this->data['board_id'] = '';
		$this->data['order_id'] = '';
		$this->data['parent_uid'] = '';
		$this->data['execute_uid'] = '';
		$this->data['ajax_builder_type'] = '';
		$this->data['kboard_list_sort'] = '';
		$this->data['kboard_list_sort_remember'] = '';
		$this->data['kboard_comments_sort'] = '';
		$this->data['kboard-content-remove-nonce'] = '';
		return $this;
	}
	
	/**
	 * 데이터를 비운다.
	 * @return KBUrl
	 */
	public function clear(){
		$this->data = array();
		return $this;
	}
	
	/**
	 * 게시판을 입력 받는다.
	 * @param int|KBoard $board
	 */
	public function setBoard($board){
		if(is_numeric($board)){
			$this->board = new KBoard($board);
		}
		else{
			$this->board = $board;
		}
	}
	
	/**
	 * 경로를 입력받는다.
	 * @param string $path
	 */
	public function setPath($path){
		if($path){
			$url = parse_url($path);
			if(isset($url['query'])){
				$query  = explode('&', html_entity_decode($url['query']));
				foreach($query as $value){
					list($key, $value) = explode('=', $value);
					// 중복된 get 값이 있으면 덮어 씌운다.
					if($value) $this->set($key, $value);
				}
			}
		}
		$this->path = $path;
		return $this;
	}
	
	/**
	 * 안전한 쿼리스트링을 반환한다.
	 * @return string
	 */
	public function getCleanQueryString(){
		$query_string = array();
		foreach($this->data as $key=>$value){
			if($key == 'page_id' && $this->is_latest){
				continue;
			}
			if($value){
				$query_string[$key] = map_deep($value, 'urlencode');
			}
		}
		return $query_string;
	}
	
	/**
	 * GET 데이터를 입력한다.
	 * @param string $key
	 * @param string $value
	 * @return KBUrl
	 */
	public function set($key, $value){
		$key = sanitize_key($key);
		$value = sanitize_text_field($value);
		$this->data[$key] = $value;
		return $this;
	}
	
	/**
	 * URL 반환한다.
	 * @return string
	 */
	public function toString(){
		$query_string = $this->getCleanQueryString();
		$this->init();
		if($this->path){
			return add_query_arg($query_string, $this->path);
		}
		else if($this->is_latest){
			return $this->getDocumentRedirect($query_string['uid']);
		}
		else{
			$url = parse_url($_SERVER['REQUEST_URI']);
			return add_query_arg($query_string, $url['path']);
		}
	}
	
	/**
	 * 경로를 입력받아 URL 반환한다.
	 * @return string
	 */
	public function toStringWithPath($path){
		// 경로가 없을경우
		if(!$path && $this->data['uid']){
			return $this->getDocumentRedirect($this->data['uid']);
		}
		
		$this->setPath($path);
		
		$query_string = $this->getCleanQueryString();
		$this->init();
		
		return add_query_arg($query_string, $this->path);
	}
	
	/**
	 * INPUT으로 반환한다.
	 * @return string
	 */
	public function toInput(){
		foreach($this->data as $key=>$value){
			if(is_array($value)){
				
			}
			else if($value){
				$input[] = '<input type="hidden" name="' . sanitize_key($key) .'" value="' . sanitize_text_field($value) . '">';
			}
		}
		$this->init();
		return isset($input) ? implode('', $input) : '';
	}
	
	/**
	 * 첨부파일 다운로드 URL을 반환한다.
	 * @param int $content_uid
	 * @param string $file_key
	 * @return string
	 */
	public function getDownloadURLWithAttach($content_uid, $file_key){
		$content_uid = intval($content_uid);
		if($content_uid){
			$this->data['kboard-file-download-nonce'] = wp_create_nonce('kboard-file-download');
			$this->data['action'] = 'kboard_file_download';
			$this->data['uid'] = $content_uid;
			$this->data['file'] = $file_key;
			
			$url = $this->toString();
		}
		else{
			$url = '';
		}
		return apply_filters('kboard_url_file_download', $url, $content_uid, $file_key, $this->board);
	}
	
	/**
	 * 첨부파일 삭제 URL을 반환한다.
	 * @param int $content_uid
	 * @param string $file_key
	 * @return string
	 */
	public function getDeleteURLWithAttach($content_uid, $file_key='thumbnail'){
		$content_uid = intval($content_uid);
		if($content_uid){
			$this->data['kboard-file-delete-nonce'] = wp_create_nonce('kboard-file-delete');
			$this->data['action'] = 'kboard_file_delete';
			$this->data['uid'] = $content_uid;
			$this->data['file'] = $file_key;
			
			$url = $this->toString();
		}
		else{
			$url = '';
		}
		return apply_filters('kboard_url_file_delete', $url, $content_uid, $file_key, $this->board);
	}
	
	/**
	 * 첨부파일 다운로드 URL을 반환한다.
	 * @param int $content_uid
	 * @param string $file_key
	 * @param int $order_item_id
	 * @return string
	 */
	public function getDownloadURLWithAttachAndOderItemID($content_uid, $file_key, $order_item_id){
		$content_uid = intval($content_uid);
		if($content_uid){
			$this->data['kboard-file-download-nonce'] = wp_create_nonce('kboard-file-download');
			$this->data['action'] = 'kboard_file_download';
			$this->data['uid'] = $content_uid;
			$this->data['file'] = $file_key;
			$this->data['order_item_id'] = $order_item_id;
			
			$url = $this->toString();
		}
		else{
			$url = '';
		}
		return apply_filters('kboard_url_file_download_order', $url, $content_uid, $file_key, $order_item_id, $this->board);
	}
	
	/**
	 * 게시글 주소를 반환한다.
	 * @param int $content_uid
	 * @return string
	 */
	public function getDocumentURLWithUID($content_uid){
		$content_uid = intval($content_uid);
		if($content_uid){
			$this->data['uid'] = $content_uid;
			$this->data['mod'] = 'document';
			$url = $this->toString();
		}
		else{
			$url = "javascript:alert('".__('No document.', 'kboard')."')";
		}
		return apply_filters('kboard_url_document_uid', $url, $content_uid, $this->board);
	}
	
	/**
	 * 라우터를 이용해 글게시 본문으로 이동한다.
	 * @param int $content_uid
	 * @return string
	 */
	public function getDocumentRedirect($content_uid){
		$content_uid = intval($content_uid);
		if($content_uid){
			$url = site_url("?kboard_content_redirect={$content_uid}");
		}
		else{
			$url = '';
		}
		return apply_filters('kboard_url_document_redirect', $url, $content_uid, $this->board);
	}
	
	/**
	 * 라우터를 이용해 게시판으로 이동한다.
	 * @param int $board_id
	 * @return string
	 */
	public function getBoardRedirect($board_id){
		$board_id = intval($board_id);
		if($board_id){
			$url = site_url("?kboard_redirect={$board_id}");
		}
		else{
			$url = '';
		}
		return apply_filters('kboard_url_board_redirect', $url, $board_id, $this->board);
	}
	
	/**
	 * 글 저장 페이지 URL을 반환한다.
	 */
	public function getContentEditorExecute(){
		return '';
	}
	
	/**
	 * 주문 저장 페이지 URL을 반환한다.
	 */
	public function getOrderExecute(){
		return '';
	}
	
	/**
	 * 소셜댓글 플러그인에서 사용할 게시글 주소를 반환한다.
	 * @param int $content_uid
	 * @return string
	 */
	public function getCommentsPluginURLWithUID($content_uid){
		$content_uid = intval($content_uid);
		if($content_uid){
			return $this->getDocumentRedirect($content_uid);
		}
		return '';
	}
	
	/**
	 * 게시글을 프린트하기 위한 주소를 반환한다.
	 * @param int $content_uid
	 * @return string
	 */
	public function getDocumentPrint($content_uid){
		$content_uid = intval($content_uid);
		if($content_uid){
			$url = site_url("?action=kboard_document_print&uid={$content_uid}");
		}
		else{
			$url = '';
		}
		return apply_filters('kboard_url_document_print', $url, $content_uid, $this->board);
	}
	
	/**
	 * 아임포트 결제 결과 저장 주소를 반환한다.
	 * @param string $display
	 * @return string
	 */
	public function getIamportEndpoint($display='', $salt=''){
		if($display){
			$url = site_url("?action=kboard_iamport_endpoint&display={$display}");
		}
		else{
			$url = site_url("?action=kboard_iamport_endpoint");
		}
		$url = add_query_arg(array('kboard-iamport-endpoint-nonce' => wp_create_nonce('kboard-iamport-endpoint-' . $salt)), $url);
		return apply_filters('kboard_url_iamport_endpoint', $url, $display, $salt, $this->board);
	}
	
	/**
	 * 아임포트 Notification URL을 반환한다.
	 * @return string
	 */
	public function getIamportNotification(){
		$iamport = kboard_iamport();
		if($iamport->imp_id && $iamport->imp_key && $iamport->imp_secret){
			$url = site_url("?action=kboard_iamport_notification");
			$security = hash('sha512', $iamport->imp_id . $iamport->imp_key . $iamport->imp_secret);
			$security = hash('sha256', $security);
			$security = hash('md5', $security);
			$url = add_query_arg(array('security' => $security), $url);
		}
		else{
			$url = '';
		}
		return apply_filters('kboard_url_iamport_notification', $url, $this->board);
	}
	
	/**
	 * 게시글 삭제 주소를 반환한다.
	 * @param int $content_uid
	 * @return string
	 */
	public function getContentRemove($content_uid){
		$content_uid = intval($content_uid);
		if($content_uid){
			$this->data['uid'] = $content_uid;
			$this->data['mod'] = 'remove';
			$url = add_query_arg('kboard-content-remove-nonce', wp_create_nonce('kboard-content-remove'), $this->toString());
		}
		else{
			$url = '';
		}
		return apply_filters('kboard_url_content_remove', $url, $content_uid, $this->board);
	}
	
	/**
	 * 게시글 작성 주소를 반환한다.
	 * @param int $content_uid
	 * @return string
	 */
	public function getContentEditor($content_uid=''){
		$content_uid = intval($content_uid);
		if($content_uid){
			$this->data['uid'] = $content_uid;
			$this->data['mod'] = 'editor';
			$url = $this->toString();
		}
		else{
			$this->data['mod'] = 'editor';
			$url = $this->toString();
		}
		return apply_filters('kboard_url_content_editor', $url, $content_uid, $this->board);
	}
	
	/**
	 * 게시글 목록 주소를 반환한다.
	 * @return string
	 */
	public function getBoardList(){
		$this->data['mod'] = 'list';
		$url = $this->toString();
		
		return apply_filters('kboard_url_board_list', $url, $this->board);
	}
	
	/**
	 * 게시글의 비밀번호를 다시 확인하는 주소를 반환한다.
	 * @param int $content_uid
	 * @return string
	 */
	public function getConfirmExecute($content_uid){
		$content_uid = intval($content_uid);
		if(isset($_GET['kboard-content-remove-nonce']) && $_GET['kboard-content-remove-nonce']){
			$url = $this->getContentRemove($content_uid);
		}
		else{
			$this->data['mod'] = kboard_mod();
			$this->data['uid'] = $content_uid;
			$url = $this->toString();
		}
		return apply_filters('kboard_url_content_editor', $url, $content_uid, $this->board);
	}
}