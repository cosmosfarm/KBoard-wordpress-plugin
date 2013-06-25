<?php
/**
 * KBoard 워드프레스 게시판 스킨
 * @author www.cosmosfarm.com
 */
class KBoardSkin {
	
	static private $instance;
	static $list;

	private function __construct(){
		$dir = plugin_dir_path(__FILE__) . 'skin';
		if ($dh = @opendir($dir)){
			while(($file = readdir($dh)) !== false){
				if($file == "." || $file == "..") continue;
				$this->list[] = $file;
			}
		}
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
}
?>