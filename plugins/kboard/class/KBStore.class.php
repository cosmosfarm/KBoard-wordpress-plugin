<?php
/**
 * KBoard 스토어
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBStore {
	
	/**
	 * 상품 리스트 페이지
	 */
	public static function productsList(){
		$category = isset($_GET['kbstore_category'])?kboard_htmlclear($_GET['kbstore_category']):'';
		include KBOARD_DIR_PATH . '/pages/kboard_store.php';
	}
}
?>