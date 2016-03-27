<?php
/**
 * KBoard 워드프레스 게시판 스킨
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBoardSkin {
	
	static private $instance;
	private $list;
	
	private function __construct(){
		$dir = KBOARD_DIR_PATH . '/skin';
		if($dh = @opendir($dir)){
			while(($file = readdir($dh)) !== false){
				if($file == "." || $file == ".." || $file == "readme.txt") continue;
				$this->list[] = $file;
			}
		}
		$this->list = apply_filters('kboard_skin_list', $this->list);
		closedir($dh);
	}
	
	/**
	 * 인스턴스를 반환한다.
	 * @return KBoardSkin
	 */
	static public function getInstance(){
		if(!self::$instance) self::$instance = new KBoardSkin();
		return self::$instance;
	}
	
	/**
	 * 모든 스킨 리스트를 반환한다.
	 * @return array
	 */
	public function getList(){
		if($this->list) return $this->list;
		else array();
	}
	
	/**
	 * 사용 중인 스킨 리스트를 반환한다.
	 * @return array
	 */
	public function getActiveList(){
		global $wpdb;
		$result = $wpdb->get_results("SELECT `skin` FROM `{$wpdb->prefix}kboard_board_setting` UNION SELECT `skin` FROM `{$wpdb->prefix}kboard_board_latestview`");
		foreach($result as $row){
			$list[] = stripslashes($row->skin);
		}
		return isset($list) && $list?$list:array();
	}
}
?>