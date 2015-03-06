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
		$mod = isset($_GET['mod'])?kboard_htmlclear($_GET['mod']):'';
		$uid = isset($_GET['uid'])?intval($_GET['uid']):'';
		if($mod == 'document' && $uid){
			$this->content = new KBContent();
			$this->content->initWithUID($uid);
			if($this->content->uid){
				add_filter('wp_title', array($this, 'title'), 1);
				
				$is_display = false;
				$board = new KBoard($this->content->board_id);
				if($board->isReader($this->content->member_uid, $this->content->secret)){
					$is_display = true;
				}
				else if($board->permission_write=='all' && ($board->permission_read=='all' || $board->permission_read=='author')){
					if($board->isConfirm($this->content->password, $this->content->uid)){
						$is_display = true;
					}
				}
				
				if($is_display){
					add_action('kboard_head', array($this, 'ogp'), 2);
					add_action('kboard_head', array($this, 'description'), 3);
					add_action('kboard_head', array($this, 'author'), 4);
					add_action('kboard_head', array($this, 'date'), 5);
				}
			}
		}
		add_action('kboard_head', array($this, 'rss'), 6);
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
	 * 게시물 정보 Open Graph protocol(OGP)을 추가한다.
	 */
	public function ogp(){
		echo '<meta property="og:title" content="'.kboard_htmlclear($this->content->title).'">';
		echo "\n";
		echo '<meta property="og:description" content="'.kboard_htmlclear($this->content->content).'">';
		echo "\n";
	}
	
	/**
	 * 게시물 정보 메타태그를 추가한다.
	 */
	public function description(){
		echo '<meta name="title" content="'.kboard_htmlclear($this->content->title).'">';
		echo "\n";
		echo '<meta name="description" content="'.kboard_htmlclear($this->content->content).'">';
		echo "\n";
	}

	/**
	 * 작성자 메타태그를 추가한다.
	 */
	public function author(){
		echo '<meta name="author" content="'.kboard_htmlclear($this->content->member_display).'">';
		echo "\n";
	}
	
	/**
	 * 작성일 메타태그를 추가한다.
	 */
	public function date(){
		echo '<meta name="author-date(date)" content="'.date("Y-m-d H:i:s", strtotime($this->content->date)).'">';
		echo "\n";
	}
	
	/**
	 * RSS 피드 주소를 추가한다.
	 */
	public function rss(){
		$name = get_bloginfo('name');
		echo '<link rel="alternate" href="'.plugins_url().'/kboard/rss.php" type="application/rss+xml" title="'.$name.' &raquo; KBoard '.__('Integration feed', 'kboard').'">';
		echo "\n";
	}
}
?>