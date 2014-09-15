<?php
/**
 * KBoard 댓글 스킨
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentSkin {
	
	static private $instance;
	static $list;
	
	private function __construct(){
		$dir = KBOARD_COMMENTS_DIR_PATH . '/skin';
		if($dh = @opendir($dir)){
			while(($file = readdir($dh)) !== false){
				if($file == "." || $file == "..") continue;
				$this->list[] = $file;
			}
		}
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
}
?>