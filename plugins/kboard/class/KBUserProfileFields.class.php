<?php
/**
 * KBoard 사용자 프로필 필드 추가
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBUserProfileFields {
	
	public function __construct(){
		if(class_exists('myCRED_Core')){
			if(current_user_can('activate_plugins')){
				add_action('show_user_profile', array($this, 'edit_point_fields'));
				add_action('edit_user_profile', array($this, 'edit_point_fields'));
				
				add_action('personal_options_update', array($this, 'save_point_fields'));
				add_action('edit_user_profile_update', array($this, 'save_point_fields'));
			}
			else{
				add_action('show_user_profile', array($this, 'show_point_fields'));
				add_action('edit_user_profile', array($this, 'show_point_fields'));
			}
		}
	}
	
	public function show_point_fields($user){ ?>
		<h3>KBoard 활동 포인트</h3>
		<table class="form-table">
			<tr>
				<th><label for="kboard_document_mycred_point">게시글 포인트</label></th>
				<td>
					<input type="number" id="kboard_document_mycred_point" name="kboard_document_mycred_point" value="<?php echo intval(get_user_meta($user->ID, 'kboard_document_mycred_point', true))?>" readonly>
					<p class="description">KBoard 게시글로 쌓은 포인트입니다.</p>
				</td>
			</tr>
			<tr>
				<th><label for="kboard_comments_mycred_point">댓글 포인트</label></th>
				<td>
					<input type="number" id="kboard_comments_mycred_point" name="kboard_comments_mycred_point" value="<?php echo intval(get_user_meta($user->ID, 'kboard_comments_mycred_point', true))?>" readonly>
					<p class="description">KBoard 댓글로 쌓은 포인트입니다.</p>
				</td>
			</tr>
		</table>
	<?php }
	
	public function edit_point_fields($user){ ?>
		<h3>KBoard 활동 포인트</h3>
		<table class="form-table">
			<tr>
				<th><label for="kboard_document_mycred_point">게시글 포인트</label></th>
				<td>
					<input type="number" id="kboard_document_mycred_point" name="kboard_document_mycred_point" value="<?php echo intval(get_user_meta($user->ID, 'kboard_document_mycred_point', true))?>">
					<p class="description">KBoard 게시글로 쌓은 포인트입니다.</p>
				</td>
			</tr>
			<tr>
				<th><label for="kboard_comments_mycred_point">댓글 포인트</label></th>
				<td>
					<input type="number" id="kboard_comments_mycred_point" name="kboard_comments_mycred_point" value="<?php echo intval(get_user_meta($user->ID, 'kboard_comments_mycred_point', true))?>">
					<p class="description">KBoard 댓글로 쌓은 포인트입니다.</p>
				</td>
			</tr>
			<?php if(!defined('COSMOSFARM_MEMBERS_VERSION')):?>
			<tr>
				<th>※ 관리자 메시지</th>
				<td>
					<p class="description"><a href="http://www.cosmosfarm.com/wpstore/product/cosmosfarm-members" onclick="window.open(this.href);return false;">코스모스팜 회원관리</a> 플러그인을 사용하시면 자동 등업 기능과 사용자 아바타 이미지 변경 등 커뮤니티 기능을 강화할 수 있습니다.</p>
				</td>
			</tr>
			<?php endif?>
		</table>
	<?php }
	
	public function save_point_fields($user_id){
		if(!current_user_can('activate_plugins')) return false;
		
		update_user_meta($user_id, 'kboard_document_mycred_point', $_POST['kboard_document_mycred_point']);
		update_user_meta($user_id, 'kboard_comments_mycred_point', $_POST['kboard_comments_mycred_point']);
	}
}
?>