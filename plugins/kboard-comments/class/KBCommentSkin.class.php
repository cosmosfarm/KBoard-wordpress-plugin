<?php
/**
 * KBoard 댓글 스킨
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentSkin {
	
	static private $instance;
	private $list;
	
	private function __construct(){
		$dir = KBOARD_COMMENTS_DIR_PATH . '/skin';
		if($dh = @opendir($dir)){
			while(($file = readdir($dh)) !== false){
				if($file == "." || $file == "..") continue;
				$this->list[] = $file;
			}
		}
		$this->list = apply_filters('kboard_comments_skin_list', $this->list);
		closedir($dh);
	}
	
	/**
	 * 인스턴스를 반환한다.
	 * @return KBCommentSkin
	 */
	static public function getInstance(){
		if(!self::$instance) self::$instance = new KBCommentSkin();
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
}
?>