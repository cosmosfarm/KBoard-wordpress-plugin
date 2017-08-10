<?php
/**
 * KBoard 워드프레스 게시판 스킨
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBoardSkin {
	
	static private $instance;
	private $active;
	private $list;
	
	private function __construct(){
		$dir = KBOARD_DIR_PATH . '/skin';
		if($dh = @opendir($dir)){
			while(($name = readdir($dh)) !== false){
				if($name == "." || $name == ".." || $name == "readme.txt") continue;
				$skin = new stdClass();
				$skin->name = $name;
				$skin->dir = KBOARD_DIR_PATH . "/skin/{$name}";
				$skin->url = KBOARD_URL_PATH . "/skin/{$name}";
				$this->list[$name] = $skin;
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
		return $this->list ? $this->list : array();
	}
	
	/**
	 * 스킨 레이아웃을 불러온다.
	 * @param string $skin_name
	 * @param string $file
	 * @param array $vars
	 * @return string
	 */
	public function load($skin_name, $file, $vars=array()){
		ob_start();
		
		extract($vars, EXTR_SKIP);
		
		$is_admin = false;
		if(is_admin()){
			if(file_exists("{$this->list[$skin_name]->dir}/admin-{$file}")){
				$is_admin = true;
			}
		}
		
		if($is_admin){
			include "{$this->list[$skin_name]->dir}/admin-{$file}";
		}
		else{
			if(file_exists("{$this->list[$skin_name]->dir}/{$file}")){
				include "{$this->list[$skin_name]->dir}/{$file}";
			}
			else{
				echo sprintf(__('%s file does not exist.', 'kboard'), $file);
			}
		}
		
		return ob_get_clean();
	}
	
	/**
	 * 스킨의 functions.php 파일을 불러온다.
	 * @param string $skin_name
	 */
	public function loadFunctions($skin_name){
		if(file_exists("{$this->list[$skin_name]->dir}/functions.php")){
			include_once "{$this->list[$skin_name]->dir}/functions.php";
		}
	}
	
	/**
	 * 스킨 URL 주소를 반환한다.
	 * @param string $skin_name
	 * @param string $file
	 * @return string
	 */
	public function url($skin_name, $file=''){
		return "{$this->list[$skin_name]->url}" . ($file ? "/{$file}" : '');
	}
	
	/**
	 * 스킨 DIR 경로를 반환한다.
	 * @param string $skin_name
	 * @param string $file
	 * @return string
	 */
	public function dir($skin_name, $file=''){
		return "{$this->list[$skin_name]->dir}" . ($file ? "/{$file}" : '');
	}
	
	/**
	 * 사용 중인 스킨 리스트를 반환한다.
	 * @return array
	 */
	public function getActiveList(){
		global $wpdb;
		$blog_id = get_current_blog_id();
		if(isset($this->active[$blog_id]) && $this->active[$blog_id]){
			return $this->active[$blog_id];
		}
		$results = $wpdb->get_results("SELECT `skin` FROM `{$wpdb->prefix}kboard_board_setting` UNION SELECT `skin` FROM `{$wpdb->prefix}kboard_board_latestview`");
		foreach($results as $row){
			$this->active[$blog_id][] = $row->skin;
		}
		return (isset($this->active[$blog_id]) && $this->active[$blog_id]) ? $this->active[$blog_id] : array();
	}
	
	public function getOptionSearchFieldKey($key, $compare){
		
	}
	
	public function getOptionSearchFieldValue($key, $value){
		
	}
}
?>