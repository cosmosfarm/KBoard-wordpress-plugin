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
		global $post;
		
		$mod = isset($_REQUEST['mod'])?sanitize_key($_REQUEST['mod']):'';
		if($mod == 'document'){
			$this->content = new KBContent();
			$this->content->initWithUID(kboard_uid());
			$board = $this->content->getBoard();
			
			if($this->content->uid){
				$is_display = false;
				
				if($board->meta->view_iframe && kboard_id()){
					$is_display = true;
				}
				else if($board->meta->add_menu_page && is_admin()){
					$is_display = true;
				}
				else if($board->meta->auto_page || $board->meta->latest_target_page){
					if($post && $post->ID == $board->meta->auto_page){
						$is_display = true;
					}
					else if($post && $post->ID == $board->meta->latest_target_page){
						$is_display = true;
					}
				}
				else{
					$is_display = true;
				}
				
				if($is_display){
					if(current_theme_supports('title-tag')){
						add_filter('document_title_parts', array($this, 'title'), 10, 1);
					}
					else{
						add_filter('wp_title', array($this, 'title'), 10, 1);
					}
					
					$is_display = false;
					
					if($board->isReader($this->content->member_uid, $this->content->secret)){
						$is_display = true;
					}
					else if($board->permission_write=='all' && ($board->permission_read=='all' || $board->permission_read=='author')){
						if($board->isConfirm($this->content->password, $this->content->uid)){
							$is_display = true;
						}
					}
					
					if($is_display){
						$this->init();
					}
				}
			}
		}
		add_action('wp_head', array($this, 'head'), 1);
		add_action('kboard_head', array($this, 'rss'), 20);
	}
	
	/**
	 * SEO 정보를 초기화한다.
	 * @return KBSeo;
	 */
	public function init(){
		static $check_kboard_seo_init_once;
		
		if($this->content->uid && !$check_kboard_seo_init_once){
			$check_kboard_seo_init_once = true;
			
			remove_action('wp_head', 'rel_canonical');
			remove_action('wp_head', 'wp_shortlink_wp_head');
			remove_action('wp_head', 'adjacent_posts_rel_link', 10);
			remove_action('wp_head', 'wlwmanifest_link');
			remove_action('template_redirect', 'wp_shortlink_header', 11);
			
			add_action('kboard_head', array($this, 'ogp'));
			add_action('kboard_head', array($this, 'twitter'));
			add_action('kboard_head', array($this, 'description'));
			add_action('kboard_head', array($this, 'author'));
			add_action('kboard_head', array($this, 'date'));
			add_action('kboard_head', array($this, 'canonical'));
			add_action('kboard_head', array($this, 'rss'));
			
			// Jetpack Open Graph Tags
			add_filter('jetpack_enable_open_graph', '__return_false');
			
			// Yoast SEO
			add_filter('wpseo_title', '__return_false');
			add_filter('wpseo_metadesc', '__return_false');
			add_filter('wpseo_opengraph_title', '__return_false');
			add_filter('wpseo_opengraph_desc', '__return_false');
			add_filter('wpseo_opengraph_image', '__return_false');
			add_filter('wpseo_opengraph_image_size', '__return_false');
			add_filter('wpseo_opengraph_url', '__return_false');
			add_filter('wpseo_twitter_title', '__return_false');
			add_filter('wpseo_twitter_description', '__return_false');
			add_filter('wpseo_twitter_card_type', array($this, 'twitter_card_summary'));
			add_filter('wpseo_twitter_image', '__return_false');
			add_filter('wpseo_twitter_image_size', '__return_false');
			add_filter('wpseo_canonical', '__return_false');
			
			// All in One SEO Pack
			add_filter('aioseop_title_page', '__return_false');
			add_filter('aioseop_description', '__return_false');
			add_filter('aioseop_canonical_url', '__return_false');
		}
		return $this;
	}
	
	/**
	 * 워드프레스 사이트 제목에 게시물 제목을 추가한다.
	 * @param string|array $title
	 * @return string|array
	 */
	public function title($title){
		if(is_array($title)){
			$title['title'] = $this->getTitle();
			return $title;
		}
		else{
			return $this->getTitle();
		}
	}
	
	/**
	 * head에 SEO 정보를 추가한다.
	 */
	public function head(){
		echo "\n<!-- WordPress KBoard plugin " . KBOARD_VERSION . " - https://www.cosmosfarm.com/products/kboard -->\n";
		do_action('kboard_head');
		echo "<!-- WordPress KBoard plugin " . KBOARD_VERSION . " - https://www.cosmosfarm.com/products/kboard -->\n\n";
	}
	
	/**
	 * 게시물 정보 Open Graph protocol(OGP)을 추가한다.
	 */
	public function ogp(){
		echo '<meta property="og:title" content="' . $this->getTitle() . '">';
		echo "\n";
		echo '<meta property="og:description" content="' . $this->getDescription() . '">';
		echo "\n";
		echo '<meta property="og:url" content="' . $this->getCanonical() . '">';
		echo "\n";
		
		$image = $this->getImage();
		if($image){
			echo '<meta property="og:image" content="' . $image . '">';
			echo "\n";
		}
	}
	
	/**
	 * Twitter 정보를 추가한다.
	 */
	public function twitter(){
		echo '<meta name="twitter:description" content="' . $this->getDescription() . '">';
		echo "\n";
		echo '<meta name="twitter:title" content="' . $this->getTitle() . '">';
		echo "\n";
		
		$image = $this->getImage();
		if($image){
			add_filter('wpseo_twitter_card_type', array($this, 'twitter_card_summary_large_image'));
			
			echo '<meta name="twitter:card" content="summary_large_image">';
			echo "\n";
			echo '<meta name="twitter:image" content="' . $image . '">';
			echo "\n";
		}
		else{
			add_filter('wpseo_twitter_card_type', array($this, 'twitter_card_summary'));
			
			echo '<meta name="twitter:card" content="summary">';
			echo "\n";
		}
	}
	
	/**
	 * twitter:card summary_large_image
	 * @param string $type
	 * @return string
	 */
	public function twitter_card_summary_large_image($type){
		$type = 'summary_large_image';
		return $type;
	}
	
	/**
	 * twitter:card summary
	 * @param string $type
	 * @return string
	 */
	public function twitter_card_summary($type){
		$type = 'summary';
		return $type;
	}
	
	/**
	 * 게시물 정보 메타태그를 추가한다.
	 */
	public function description(){
		echo '<meta name="title" content="' . $this->getTitle() . '">';
		echo "\n";
		echo '<meta name="description" content="' . $this->getDescription() . '">';
		echo "\n";
	}
	
	/**
	 * 작성자 메타태그를 추가한다.
	 */
	public function author(){
		echo '<meta name="author" content="' . $this->getUsername() . '">';
		echo "\n";
		echo '<meta name="article:author" content="' . $this->getUsername() . '">';
		echo "\n";
	}
	
	/**
	 * 작성일 메타태그를 추가한다.
	 */
	public function date(){
		$timezone_string = get_option('timezone_string');
		
		echo '<meta name="article:published_time" content="' . date('c', strtotime("{$timezone_string} {$this->content->date}")) . '">';
		echo "\n";
		echo '<meta name="article:modified_time" content="' . date('c', strtotime("{$timezone_string} {$this->content->update}")) . '">';
		echo "\n";
		echo '<meta name="og:updated_time" content="' . date('c', strtotime("{$timezone_string} {$this->content->update}")) . '">';
		echo "\n";
	}
	
	/**
	 * Canonical 주소를 추가한다.
	 */
	public function canonical(){
		echo '<link rel="canonical" href="' . $this->getCanonical() . '">';
		echo "\n";
		echo '<link rel="shortlink" href="' . $this->getCanonical() . '">';
		echo "\n";
	}
	
	/**
	 * RSS 피드 주소를 추가한다.
	 */
	public function rss(){
		global $check_kboard_seo_rss_once;
		if(!$check_kboard_seo_rss_once){
			$name = get_bloginfo('name');
			echo '<link rel="alternate" href="' . plugins_url() . '/kboard/rss.php" type="application/rss+xml" title="' . $name . ' &raquo; KBoard ' . __('Integration feed', 'kboard') . '">';
			echo "\n";
			$check_kboard_seo_rss_once = true;
		}
	}
	
	/**
	 * 페이지의 제목을 반환한다.
	 * @param string $title
	 * @return string
	 */
	public function getTitle($title=''){
		if($this->content->title){
			return esc_attr(sanitize_text_field($this->content->title));
		}
		return esc_attr($title);
	}
	
	/**
	 * 페이지의 내용을 반환한다.
	 * @param string $description
	 * @return string
	 */
	public function getDescription($description=''){
		if($this->content->content){
			return esc_attr(sanitize_text_field($this->content->content));
		}
		return esc_attr($description);
	}
	
	/**
	 * 페이지의 대표 이미지 주소를 반환한다.
	 * @param string $image
	 * @return string
	 */
	public function getImage($image=''){
		if($this->content->getThumbnail()){
			return esc_attr($this->content->getThumbnail());
		}
		return esc_attr($image);
	}
	
	/**
	 * 글 작성자 이름을 반환한다.
	 * @param string $username
	 * @return string
	 */
	public function getUsername($username=''){
		if($this->content->member_display){
			return esc_attr(sanitize_text_field($this->content->member_display));
		}
		return esc_attr($username);
	}
	
	/**
	 * Canonical 주소를 반환한다.
	 * @param string $canonical_url
	 * @return string
	 */
	public function getCanonical($canonical_url=''){
		if($this->content->uid){
			$url = new KBUrl();
			return esc_url_raw($url->getDocumentRedirect($this->content->uid));
		}
		return esc_url_raw($canonical_url);
	}
}
?>