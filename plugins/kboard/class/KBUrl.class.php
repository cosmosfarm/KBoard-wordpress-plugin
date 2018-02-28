<?php
/**
 * KBoard 워드프레스 게시판 URL
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBUrl {
	
	private $path;
	private $data;
	
	var $board;
	
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
		$this->data['action'] = '';
		$this->data['security'] = '';
		$this->data['order_id'] = '';
		$this->data['parent_uid'] = '';
		$this->data['execute_uid'] = '';
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
		$url = parse_url($path);
		if(isset($url['query'])){
			$query  = explode('&', html_entity_decode($url['query']));
			foreach($query as $value){
				list($key, $value) = explode('=', $value);
				// 중복된 get 값이 있으면 덮어 씌운다.
				if($value) $this->set($key, $value);
			}
		}
		$this->path = isset($url['path']) ? $url['path'] : '';
		return $this;
	}
	
	/**
	 * 안전한 쿼리스트링을 반환한다.
	 * @return string
	 */
	public function getCleanQueryStrings(){
		foreach($this->data as $key=>$value){
			if(is_array($value)){
				$query_strings[] = http_build_query(array(sanitize_key($key)=>$value));
			}
			else if($value){
				$query_strings[] = sanitize_key($key).'='.urlencode(sanitize_text_field($value));
			}
		}
		return isset($query_strings) ? implode('&', $query_strings) : '';
	}
	
	/**
	 * GET 데이터를 입력한다.
	 * @param string $key
	 * @param string $value
	 * @return KBUrl
	 */
	public function set($key, $value){
		$this->data[$key] = $value;
		return $this;
	}
	
	/**
	 * URL 반환한다.
	 * @return string
	 */
	public function toString(){
		$query_strings = $this->getCleanQueryStrings();
		$this->init();
		if($this->path){
			return $this->path . ($query_strings ? "?{$query_strings}" : '');
		}
		else{
			$url = parse_url($_SERVER['REQUEST_URI']);
			return (isset($url['path']) ? $url['path'] : '') . ($query_strings ? "?{$query_strings}" : '');
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
		
		// 입력받은 경로를 처리한다.
		$url = parse_url($path);
		if(isset($url['query'])){
			$query  = explode('&', html_entity_decode($url['query']));
			foreach($query as $value){
				list($key, $value) = explode('=', $value);
				// 중복된 get 값이 있으면 덮어 씌운다.
				if($value) $this->set($key, $value);
			}
		}
		
		$query_strings = $this->getCleanQueryStrings();
		$this->init();
		return (isset($url['path']) ? $url['path'] : '') . ($query_strings ? "?{$query_strings}" : '');
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
			return add_query_arg('kboard-file-download-nonce', wp_create_nonce('kboard-file-download'), site_url("?action=kboard_file_download&uid={$content_uid}&file={$file_key}"));
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
			$url = add_query_arg('kboard-file-delete-nonce', wp_create_nonce('kboard-file-delete'), site_url("?action=kboard_file_delete&uid={$content_uid}&file={$file_key}"));
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
			$url = site_url("?action=kboard_file_download&uid={$content_uid}&file={$file_key}&order_item_id={$order_item_id}");
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
	public function getIamportEndpoint($display=''){
		if($display){
			$url = site_url("?action=kboard_iamport_endpoint&display=$display");
		}
		else{
			$url = site_url("?action=kboard_iamport_endpoint");
		}
		return apply_filters('kboard_url_iamport_endpoint', $url, $display, $this->board);
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
}
?>