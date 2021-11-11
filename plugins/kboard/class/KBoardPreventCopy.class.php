<?php
/**
 * KBoard 복사 방지 스크립트 
* @link www.cosmosfarm.com
* @copyright Copyright 2021 Cosmosfarm. All rights reserved.
* @license http://www.gnu.org/licenses/gpl.html
*/
class KBoardPreventCopy {
	
	/**
	 * 드래그, 우클릭 방지 스크립트를 반환한다.
	 * @return mixed
	 */
	public function getDragRightScript(){
		$script = "jQuery('body').attr('ondragstart','return false');";
		$script .= "jQuery('body').attr('onselectstart','return false');";
		$script .= "jQuery('body').attr('oncontextmenu','return false');";
		return $script;
	}
	
	/**
	 * 키보드 입력(F12, Ctrl, shift) 방지 스크립트를 반환한다.
	 * @return mixed
	 */
	public function getKeyboardScript(){
		$script = "jQuery(document).on('keydown',function(e){";
		$script .= "if(e.keyCode == 123){";
		$script .= "return false;}";
		$script .= "else if(e.ctrlKey && e.shiftKey){";
		$script .= "return false;}";
		$script .= "});";
		return $script;
	}
	
	/**
	 * 복사 방지 스크립트 문구를 반환한다.
	 * @return string
	 */
	public function getCopyText(){
		return apply_filters('kboard_prevent_copy_text', '복사가 금지되어 있습니다.');
	}
	
	/**
	 * 복사 방지 스크립트를 반환한다.
	 * @return mixed
	 */
	public function getCopyScript(){
		$prevent_copy_text = $this->getCopyText();
		$script = "window.addEventListener('copy', (e) => {";
		$script .= "e.preventDefault();";
		$script .= "e.clipboardData.setData('Text', '{$prevent_copy_text}');";
		$script .= "});";
		return $script;
	}
}