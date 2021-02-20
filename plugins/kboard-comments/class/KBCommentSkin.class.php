<?php
/**
 * KBoard 댓글 스킨
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentSkin {
	
	static private $instance;
	private $active = array();
	private $list;
	
	private function __construct(){
		$dir = KBOARD_COMMENTS_DIR_PATH . '/skin';
		if($dh = @opendir($dir)){
			while(($name = readdir($dh)) !== false){
				if($name == '.' || $name == '..' || $name == 'readme.txt' || $name == '__MACOSX' || $name == '.git') continue;
				$skin = new stdClass();
				$skin->name = $name;
				$skin->dir = KBOARD_COMMENTS_DIR_PATH . "/skin/{$name}";
				$skin->url = KBOARD_COMMENTS_URL_PATH . "/skin/{$name}";
				$this->list[$name] = $skin;
			}
		}
		closedir($dh);
		
		$this->list = apply_filters('kboard_comments_skin_list', $this->list);
		
		ksort($this->list);
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
		
		if(isset($this->list[$skin_name])){
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
				include "{$this->list[$skin_name]->dir}/{$file}";
			}
		}
		
		return ob_get_clean();
	}
	
	/**
	 * 스킨의 functions.php 파일을 불러온다.
	 * @param string $skin_name
	 */
	public function loadFunctions($skin_name){
		if(isset($this->list[$skin_name]) && file_exists("{$this->list[$skin_name]->dir}/functions.php")){
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
		if(isset($this->list[$skin_name])){
			return "{$this->list[$skin_name]->url}" . ($file ? "/{$file}" : '');
		}
		return '';
	}
	
	/**
	 * 스킨 DIR 경로를 반환한다.
	 * @param string $skin_name
	 * @param string $file
	 * @return string
	 */
	public function dir($skin_name, $file=''){
		if(isset($this->list[$skin_name])){
			return "{$this->list[$skin_name]->dir}" . ($file ? "/{$file}" : '');
		}
		return '';
	}
	
	/**
	 * 사용 중인 스킨 리스트를 반환한다.
	 * @return array
	 */
	public function getActiveList(){
		global $wpdb;
		
		if(!$this->active){
			$results = $wpdb->get_results("SELECT DISTINCT `value` FROM `{$wpdb->prefix}kboard_board_meta` WHERE `key`='comment_skin'");
			
			foreach($results as $row){
				if(!empty($row->value)){
					$this->active[] = $row->value;
				}
			}
		}
		
		return apply_filters('kboard_comments_skin_active_list', $this->active);
	}
}