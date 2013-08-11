<?php
/**
 * KBoard 워드프레스 게시판 메타
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBoardMeta {
	
	private $board_id;
	private $meta;
	
	public function __construct($board_id=''){
		$this->meta = new stdClass();
		if($board_id) $this->setBoardID($board_id);
	}
	
	public function __destruct(){
		$this->clear();
	}
	
	public function __get($name){
		$name = addslashes($name);
		
		if($this->board_id){
			if($this->meta->{$name}){
				return stripslashes($this->meta->{$name});
			}
			else{
				$this->meta->{$name} = @reset(mysql_fetch_row(kboard_query("SELECT value FROM ".KBOARD_DB_PREFIX."kboard_board_meta WHERE `board_id`='$this->board_id' AND `key`='$name'")));
				return stripslashes($this->meta->{$name});
			}
		}
		
		return '';
	}
	
	public function __set($name, $value){
		$name = addslashes($name);
		$value = addslashes($value);
		
		if($this->board_id){
			kboard_query("INSERT INTO ".KBOARD_DB_PREFIX."kboard_board_meta (`board_id`, `key`, `value`) VALUE ('$this->board_id', '$name', '$value') ON DUPLICATE KEY UPDATE `value`='$value'");
			$this->meta->{$name} = $value;
		}
	}
	
	/**
	 * 게시판 아이디를 입력받는다.
	 * @param int $id
	 */
	public function setBoardID($board_id){
		$this->meta = new stdClass();
		$this->board_id = intval($board_id);
	}
	
	/**
	 * 모든 빈 값들을 제거한다.
	 */
	public function clear(){
		kboard_query("DELETE FROM ".KBOARD_DB_PREFIX."kboard_board_meta WHERE value=''");
	}
}
?>