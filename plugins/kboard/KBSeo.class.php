<?php
/**
 * KBoard SEO (검색 엔진 최적화)
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBSeo {
	
	private $content;
	
	public function __construct(){
		$mod = $_GET['mod'];
		$uid = intval($_GET['uid']);
		if($mod == 'document' && $uid){
			$this->content = new Content();
			$this->content->initWithUID($uid);
				
			add_filter('wp_title', array($this, 'title'), 1);
			add_action('kboard_head', array($this, 'description' ), 2);
		}
		add_action('kboard_head', array($this, 'rss'), 3);
		add_action('wp_head', array($this, 'head'), 1);
	}
	
	/**
	 * 워드프레스 사이트 제목에 게시물 제목을 추가한다.
	 * @param string $title
	 * @return string
	 */
	public function title($title){
		return kboard_htmlclear($this->content->title) . ' | ' . $title;
	}
	
	/**
	 * head에 SEO 정보를 추가한다.
	 */
	public function head(){
		echo "\n<!-- WordPress KBoard plugin " . KBOARD_VERSION . " - http://www.cosmosfarm.com/products/kboard -->\n";
		do_action('kboard_head');
		echo "<!-- WordPress KBoard plugin " . KBOARD_VERSION . " - http://www.cosmosfarm.com/products/kboard -->\n\n";
	}
	
	/**
	 * 메타태그를 추가한다.
	 */
	public function description(){
		echo '<meta name="title" content="'.kboard_htmlclear($this->content->title).'">';
		echo "\n";
		echo '<meta name="description" content="'.kboard_htmlclear($this->content->content).'">';
		echo "\n";
	}
	/**
	 * RSS 피드 주소를 추가한다.
	 */
	public function rss(){
		$name = get_bloginfo('name');
		echo '<link rel="alternate" href="'.plugins_url().'/kboard/rss.php" type="application/rss+xml" title="'.$name.' &raquo; KBoard 통합 피드">';
		echo "\n";
	}
}
?>