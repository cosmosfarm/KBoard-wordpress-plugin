<?php
if(!defined('ABSPATH')) exit;
if(!defined('KBOARD_COMMNETS_VERSION')){
	die('<script>alert("KBoard 댓글 플러그인을 추가로 설치해주세요.\n코스모스팜 홈페이지(http://www.cosmosfarm.com/)에서 다운로드 가능합니다.");history.go(-1);</script>');
}
?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h2>
		KBoard : 게시판 관리
		<a href="http://www.cosmosfarm.com/products/kboard" class="add-new-h2" onclick="window.open(this.href); return false;">홈페이지</a>
		<a href="http://www.cosmosfarm.com/threads" class="add-new-h2" onclick="window.open(this.href); return false;">커뮤니티</a>
		<a href="http://www.cosmosfarm.com/support" class="add-new-h2" onclick="window.open(this.href); return false;">고객지원</a>
	</h2>
	<form action="<?php echo admin_url('/admin-post.php')?>" method="post">
		<input type="hidden" name="action" value="kboard_update_action">
		<input type="hidden" name="board_id" value="<?php echo $board->uid?>">
		<input type="hidden" name="tab_kboard_setting" value="">
		
		<h2 class="nav-tab-wrapper">
			<a href="#tab-kboard-setting-0" class="tab-kboard nav-tab<?php if(1):?> nav-tab-active<?php endif?>" onclick="kboard_setting_tab_chnage(0);">기본설정</a>
			<a href="#tab-kboard-setting-1" class="tab-kboard nav-tab" onclick="kboard_setting_tab_chnage(1);">권한설정</a>
			<?php if($board->uid):?>
			<a href="#tab-kboard-setting-2" class="tab-kboard nav-tab" onclick="kboard_setting_tab_chnage(2);">고급설정</a>
			<a href="#tab-kboard-setting-3" class="tab-kboard nav-tab" onclick="kboard_setting_tab_chnage(3);">소셜댓글</a>
			<?php endif?>
		</h2>
		
		<div class="tab-kboard-setting tab-kboard-setting-active">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="board_name">게시판 이름</label></th>
						<td>
							<input type="text" name="board_name" size="30" tabindex="1" value="<?php if(!$board->board_name):?>무명게시판 <?php echo date("Y-m-d", current_time('timestamp'))?><?php else:?><?php echo $board->board_name?><?php endif?>" id="board_name">
						</td>
					</tr>
					<?php if($board->uid):?>
					<tr valign="top">
						<th scope="row"><label for="auto_page">게시판 자동 설치</label></th>
						<td>
							<select name="auto_page" id="auto_page" class="">
								<option value="">— 선택하기 —</option>
								<?php foreach(get_pages() AS $key => $page):?>
								<option value="<?php echo $page->ID?>"<?php if($meta->auto_page == $page->ID):?> selected<?php endif?>><?php echo $page->post_title?></option>
								<?php endforeach?>
							</select>
							<p class="description">선택된 페이지에 자동으로 게시판이 설치됩니다. 또는 아래의 게시판 입력코드(Shortcode)로 설치 하실 수 있습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="shortcode">게시판 숏코드(Shortcode)</label></th>
						<td>
							<textarea style="width: 350px" id="shortcode">[kboard id=<?php echo $board->uid?>]</textarea>
							<p class="description">이 코드를 포스팅 또는 페이지에 입력하세요. 자동으로 게시판이 추가됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="shortcode">최신글 숏코드(Shortcode)</label></th>
						<td>
							<textarea style="width: 350px" id="shortcode">[kboard_latest id=<?php echo $board->uid?> url=페이지주소 rpp=5]</textarea>
							<p class="description">최신글 리스트를 생성합니다. 페이지주소 부분에 게시판이 설치된 페이지의 전체 URL을 입력하고, 이 숏코드를 메인페이지 또는 사이드바에 입력하세요.</p>
						</td>
					</tr>
					<?php endif?>
					
					<tr valign="top">
						<th scope="row"><label for="skin">게시판 스킨 선택</label></th>
						<td>
							<select name="skin" id="skin" class="">
								<?php
								if(!$board->skin) $board->skin = 'default';
								foreach($skin->list AS $key => $value):
								?>
								<option value="<?php echo $value?>"<?php if($board->skin == $value):?> selected<?php endif?>>
									<?php echo $value?>
								</option>
								<?php endforeach?>
							</select>
							<p class="description">게시판 스킨에 따라 모양과 기능이 변합니다. <a href="<?php echo admin_url('admin.php?page=kboard_store&kbstore_category=kboard')?>">스킨 더보기</a></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="page_rpp">게시물 표시</label></th>
						<td>
							<select name="page_rpp" id="page_rpp" class="">
								<?php if(!$board->page_rpp) $board->page_rpp=10;?>
								<option value="1"<?php if($board->page_rpp == 1):?> selected<?php endif?>>1개</option>
								<option value="2"<?php if($board->page_rpp == 2):?> selected<?php endif?>>2개</option>
								<option value="3"<?php if($board->page_rpp == 3):?> selected<?php endif?>>3개</option>
								<option value="4"<?php if($board->page_rpp == 4):?> selected<?php endif?>>4개</option>
								<option value="5"<?php if($board->page_rpp == 5):?> selected<?php endif?>>5개</option>
								<option value="6"<?php if($board->page_rpp == 6):?> selected<?php endif?>>6개</option>
								<option value="7"<?php if($board->page_rpp == 7):?> selected<?php endif?>>7개</option>
								<option value="8"<?php if($board->page_rpp == 8):?> selected<?php endif?>>8개</option>
								<option value="9"<?php if($board->page_rpp == 9):?> selected<?php endif?>>9개</option>
								<option value="10"<?php if($board->page_rpp == 10):?> selected<?php endif?>>10개</option>
								<option value="12"<?php if($board->page_rpp == 12):?> selected<?php endif?>>12개</option>
								<option value="15"<?php if($board->page_rpp == 15):?> selected<?php endif?>>15개</option>
								<option value="17"<?php if($board->page_rpp == 17):?> selected<?php endif?>>17개</option>
								<option value="20"<?php if($board->page_rpp == 20):?> selected<?php endif?>>20개</option>
								<option value="25"<?php if($board->page_rpp == 25):?> selected<?php endif?>>25개</option>
								<option value="30"<?php if($board->page_rpp == 30):?> selected<?php endif?>>30개</option>
								<option value="50"<?php if($board->page_rpp == 50):?> selected<?php endif?>>50개</option>
								<option value="100"<?php if($board->page_rpp == 100):?> selected<?php endif?>>100개</option>
							</select>
							<p class="description">한 페이지에 보여지는 게시물 숫자를 정합니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="use_comment">댓글 사용</label></th>
						<td>
							<?php if(defined('KBOARD_COMMNETS_VERSION')):?>
								<select name="use_comment" id="use_comment" class="">
										<option value="">비활성화</option>
										<option value="yes"<?php if($board->use_comment == 'yes'):?> selected<?php endif?>>활성화</option>
								</select>
								<p class="description">게시글에 댓글 쓰기를 활성화 합니다. (KBoard 댓글 플러그인 사용)</p>
							<?php else:?>
								<select name="use_comment" id="use_comment" class="">
										<option value="" selected>비활성화</option>
								</select>
								<p class="description">KBoard 댓글 플러그인을 설치하세요.</p>
							<?php endif?>
						</td>
					</tr>
					<?php if(defined('KBOARD_COMMNETS_VERSION')):?>
					<tr valign="top">
						<th scope="row"><label for="comment_skin">댓글 스킨 선택</label></th>
						<td>
							<select name="comment_skin" id="comment_skin" class="">
								<?php
								if(!$meta->comment_skin) $meta->comment_skin = 'default';
								foreach($comment_skin->list AS $key => $value):
								?>
								<option value="<?php echo $value?>"<?php if($meta->comment_skin == $value):?> selected<?php endif?>><?php echo $value?></option>
								<?php endforeach?>
							</select>
							<p class="description">댓글의 모양을 선택합니다. (KBoard 댓글 플러그인 사용)</p>
						</td>
					</tr>
					<?php endif?>
					<tr valign="top">
						<th scope="row"><label for="use_editor">글 작성 에디터</label></th>
						<td>
							<select name="use_editor" id="use_editor" class="">
								<option value="">textarea 사용</option>
								<option value="yes"<?php if($board->use_editor == 'yes'):?> selected<?php endif?>>워드프레스 내장 에디터 사용</option>
							</select>
							<p class="description">에디터를 사용해 게시물을 작성할 수 있습니다. 워드프레스에 내장된 에디터를 사용합니다. 다른 에디터 플러그인을 설치하면 호환 됩니다.</p>
						</td>
					</tr>
					<?php if(!$board->use_editor):?>
					<tr valign="top">
						<th scope="row"><label for="autolink">게시글 본문 자동링크 사용</label></th>
						<td>
							<select name="autolink" id="autolink" class="">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->autolink == '1'):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">에디터 textarea를 사용할때 url주소에 자동으로 링크를 생성합니다.</p>
						</td>
					</tr>
					<?php endif?>
					<tr valign="top">
						<th scope="row"><label for="use_category">카테고리 사용</label></th>
						<td>
							<select name="use_category" id="use_category" class="">
								<option value="">비활성화</option>
								<option value="yes"<?php if($board->use_category == 'yes'):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">카테고리를 사용해서 게시물을 분리할 수 있습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="category1_list">카테고리1</label></th>
						<td>
							<input type="text" style="width: 350px;" name="category1_list" id="category1_list" value="<?php echo $board->category1_list?>">
							<p class="description">카테고리를 입력하세요. 여러 카테고리를 입력하실 경우 콤마(,)로 구분됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="category2_list">카테고리2</label></th>
						<td>
							<input type="text" style="width: 350px;" name="category2_list" id="category2_list" value="<?php echo $board->category2_list?>">
							<p class="description">카테고리를 입력하세요. 여러 카테고리를 입력하실 경우 콤마(,)로 구분됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="latest_alerts">최신글 이메일 알림</label></th>
						<td>
							<input type="text" style="width: 350px;" name="latest_alerts" id="latest_alerts" value="<?php echo $meta->latest_alerts?>">
							<p class="description">최신글이 등록되면 입력된 이메일로 알려드립니다. 여러명을 입력하실 경우 콤마(,)로 구분됩니다. 서버 환경에 따라서 메일이 전송되지 못 할 수도 있습니다.</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<div class="tab-kboard-setting">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="admin_user">선택된 관리자</label></th>
						<td>
							<input type="text" style="width: 350px;" name="admin_user" id="admin_user" value="<?php echo $board->admin_user?>">
							<p class="description">사용자 아이디를 입력하세요. 여러명을 입력하실 경우 콤마(,)로 구분됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="permission_read">읽기권한</label></th>
						<td>
							<select name="permission_read" id="permission_read" class="">
								<option value="all"<?php if($board->permission_read == 'all'):?> selected<?php endif?>>
									<?php echo kboard_permission('all')?>
								</option>
								<option value="author"<?php if($board->permission_read == 'author'):?> selected<?php endif?>>
									<?php echo kboard_permission('author')?>
								</option>
								<option value="editor"<?php if($board->permission_read == 'editor'):?> selected<?php endif?>>
									<?php echo kboard_permission('editor')?>
								</option>
								<option value="administrator"<?php if($board->permission_read == 'administrator'):?> selected<?php endif?>>
									<?php echo kboard_permission('administrator')?>
								</option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="permission_write">쓰기권한</label></th>
						<td>
							<select name="permission_write" id="permission_write" class="">
								<option value="all"<?php if($board->permission_read == 'all'):?> selected<?php endif?>>
									<?php echo kboard_permission('all')?>
								</option>
								<option value="author"<?php if($board->permission_write == 'author'):?> selected<?php endif?>>
									<?php echo kboard_permission('author')?>
								</option>
								<option value="editor"<?php if($board->permission_write == 'editor'):?> selected<?php endif?>>
									<?php echo kboard_permission('editor')?>
								</option>
								<option value="administrator"<?php if($board->permission_write == 'administrator'):?> selected<?php endif?>>
									<?php echo kboard_permission('administrator')?>
								</option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="permission_comment_write">댓글쓰기권한</label></th>
						<td>
							<select name="permission_comment_write" id="permission_comment_write" class="">
								<option value=""<?php if(!$meta->permission_comment_write):?> selected<?php endif?>>
									<?php echo kboard_permission('all')?>
								</option>
								<option value="1"<?php if($meta->permission_comment_write == '1'):?> selected<?php endif?>>
									<?php echo kboard_permission('author')?>
								</option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<?php if($board->uid):?>
		<div class="tab-kboard-setting">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="shortcode_execute">게시글 숏코드(Shortcode) 실행</label></th>
						<td>
							<select name="shortcode_execute" id="shortcode_execute" class="">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->shortcode_execute == '1'):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">게시글 본문에 글쓴이가 입력한 워드프레스 숏코드(Shortcode)를 실행합니다. 사용자가 워드프레스 내장 기능을 사용할 수 있어 보안에 주의해야 합니다.  <a href="http://blog.cosmosfarm.com/50179426321" onclick="window.open(this.href); return false;">더보기</a></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="default_content">본문 기본 양식</label></th>
						<td>
							<?php if($board->use_editor):?>
								<?php wp_editor($meta->default_content, 'default_content'); ?>
							<?php else:?>
								<textarea name="default_content" id="default_content" style="width: 600px; height: 300px; max-width: 100%;"><?php echo $meta->default_content?></textarea>
							<?php endif;?>
							<p class="description">게시판 글 작성시 보여질 기본 양식입니다. 기본값은 빈 값입니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="reply_copy_content">답글 기본 내용</label></th>
						<td>
							<select name="reply_copy_content" id="reply_copy_content" class="">
								<option value="">빈 내용</option>
								<option value="1"<?php if($meta->reply_copy_content == '1'):?> selected<?php endif?>>원글 내용</option>
								<option value="2"<?php if($meta->reply_copy_content == '2'):?> selected<?php endif?>>본문 기본 양식</option>
							</select>
							<p class="description">답글 작성시 원글의 내용을 가져오거나 본문 기본 양식을 보여줍니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="use_direct_url">고급 사용자용 고유주소</label></th>
						<td>
							<select name="use_direct_url" id="use_direct_url">
								<option value="">사용중지</option>
								<option value="1"<?php if($meta->use_direct_url):?> selected<?php endif?>>사용하기</option>
							</select>
							<?php if($meta->use_direct_url):?>
							<a href="<?php echo site_url("?kboard_id=$board->uid")?>" onclick="window.open(this.href); return false;"><?php echo site_url("?kboard_id=$board->uid")?></a>
							<?php endif?>
							<p class="description">고유주소는 독립적 레이아웃 편집 및 아이프레임 삽입 등 고급 사용자를 위한 편의 기능입니다. 일반 사용자는 입력코드(Shortcode)를 사용해 게시판을 생성하세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="pass_autop">특정 테마 레이아웃 깨짐 방지</label></th>
						<td>
							<select name="pass_autop" id="pass_autop" class="">
								<option value="disable"<?php if($meta->pass_autop == 'disable'):?> selected<?php endif?>>비활성화</option>
								<option value="enable"<?php if($meta->pass_autop == 'enable'):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">문제가 없다면 활성화 하지 마세요. 특정 테마에서 content에 자동으로 P태그가 추가되어 레이아웃이 깨지는 현상이 발생됩니다. 활성화시 content에 P태그가 추가되기 전에 게시판을 출력시킵니다. <a href="http://blog.cosmosfarm.com/50178536050" onclick="window.open(this.href); return false;">더보기</a></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="view_iframe">아이프레임으로 보기</label></th>
						<td>
							<select name="view_iframe" id="view_iframe">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->view_iframe):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">문제가 없다면 활성화 하지 마세요. 원페이지 테마 또는 게시판이 심하게 깨질 때 아이프레임으로 보기를 사용해주세요.</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="tab-kboard-setting">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"></th>
						<td>
							먼저 <a href="http://www.cosmosfarm.com/plugin/comments" onclick="window.open(this.href);return false;">소셜댓글 플러그인</a> 관리사이트에서 이 워드프레스 사이트를 <a href="http://www.cosmosfarm.com/plugin/comments/create" onclick="window.open(this.href);return false;">등록</a>해주세요.
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="comments_plugin_id">소셜댓글 플러그인 ID</label></th>
						<td>
							<input type="text" name="comments_plugin_id" id="comments_plugin_id" value="<?php echo $meta->comments_plugin_id?>">
							<p class="description"><a href="http://www.cosmosfarm.com/plugin/comments/sites" onclick="window.open(this.href);return false;">등록된 사이트</a> » 설치하기 페이지에 나와있는 ID값을 입력해주세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="use_comments_plugin">소셜댓글 플러그인 사용</label></th>
						<td>
							<select name="use_comments_plugin" id="use_comments_plugin">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->use_comments_plugin):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">게시판에 KBoard 댓글을 비활성화 하고 소셜댓글 플러그인을 사용합니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="comments_plugin_row">댓글 표시</label></th>
						<td>
							<select name="comments_plugin_row" id="comments_plugin_row" class="">
								<?php if(!$meta->comments_plugin_row) $meta->comments_plugin_row=10;?>
								<option value="10"<?php if($meta->comments_plugin_row == 10):?> selected<?php endif?>>10개</option>
								<option value="20"<?php if($meta->comments_plugin_row == 20):?> selected<?php endif?>>20개</option>
								<option value="30"<?php if($meta->comments_plugin_row == 30):?> selected<?php endif?>>30개</option>
								<option value="50"<?php if($meta->comments_plugin_row == 50):?> selected<?php endif?>>50개</option>
								<option value="100"<?php if($meta->comments_plugin_row == 100):?> selected<?php endif?>>100개</option>
							</select>
							<p class="description">한 페이지에 보여지는 댓글 숫자를 정합니다.</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php endif?>
		
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button-primary" value="변경 사항 저장">
		</p>
	</form>
</div>

<script>
function kboard_setting_tab_init(){
	var index = location.hash.slice(1).replace('tab-kboard-setting-', '');
	kboard_setting_tab_chnage(index);
};
kboard_setting_tab_init();

function kboard_setting_tab_chnage(index){
	jQuery('.tab-kboard').removeClass('nav-tab-active').eq(index).addClass('nav-tab-active');
	jQuery('.tab-kboard-setting').removeClass('tab-kboard-setting-active').eq(index).addClass('tab-kboard-setting-active');
	jQuery('input[name=tab_kboard_setting]').val(index);
}
</script>