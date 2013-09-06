<?php
/**
 * KBoard 워드프레스 게시판 URL
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Url {
	
	private $data;
	
	public function __construct(){
		return $this->init();
	}
	
	/**
	 * MOD, UID 값 초기화, URL을 재사용 할 때 오류를 방지한다.
	 * @return Url
	 */
	public function init(){
		$this->data = kboard_htmlclear($_GET);
		$this->data['mod'] = null;
		$this->data['uid'] = null;
		return $this;
	}
	
	/**
	 * GET 데이터를 입력한다.
	 * @param string $key
	 * @param string $value
	 * @return Url
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
		foreach($this->data AS $key => $value){
			if($value) $query_strings[$key] = $key . '=' . $value;
		}
		$this->init();
		$url = parse_url($_SERVER['REQUEST_URI']);
		return $url['path'] . '?' . @implode('&', $query_strings);
	}

	/**
	 * 경로를 입력받아 URL 반환한다.
	 * @return string
	 */
	public function toStringWithPath($path){
		foreach($this->data AS $key => $value){
			if($value) $query_strings[$key] = $key . '=' . $value;
		}
		
		// 입력받은 경로를 처리한다.
		$url = parse_url($path);
		$query  = explode('&', html_entity_decode($url['query']));
		foreach($query as $value){
			list($key, $value) = explode('=', $value);
			// 중복된 get 값이 있으면 덮어 씌운다.
			if($value) $query_strings[$key] = $key . '=' . $value;
		}
		
		$this->init();
		return $url['path'] . '?' . @implode('&', $query_strings);
	}
	
	/**
	 * INPUT으로 반환한다.
	 * @return string
	 */
	public function toInput(){
		foreach($this->data AS $key => $value){
			if($value) $input[] = '<input type="hidden" name="' . $key .'" value="' . $value . '">';
		}
		$this->init();
		return @implode('', $input);
	}
	
	/**
	 * 첨부파일 삭제 URL을 반환한다.
	 * @param int $uid
	 * @param string $key
	 * @return string
	 */
	public function getDeleteURLWithAttach($uid, $key='thumbnail'){
		return plugins_url() . "/kboard/execute/delete.php?uid={$uid}&file={$key}";
	}
	
	/**
	 * 첨부파일 다운로드 URL을 반환한다.
	 * @param int $uid
	 * @param string $key
	 * @return string
	 */
	public function getDownloadURLWithAttach($uid, $key){
		return plugins_url() . "/kboard/execute/download.php?uid={$uid}&file={$key}";
	}
	
	/**
	 * 게시물 주소를 반환한다.
	 * @param int $uid
	 * @return string
	 */
	public function getDocumentURLWithUID($uid){
		$uid = intval($uid);
		if($uid){
			$this->data['uid'] = $uid;
			$this->data['mod'] = 'document';
			return $this->toString();
		}
		else{
			return "javascript:alert('글이 없습니다.')";
		}
	}
}
?>