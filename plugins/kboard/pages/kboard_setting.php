<?php
if(!defined('ABSPATH')) exit;
if(!defined('KBOARD_COMMNETS_VERSION')){
	die('<script>alert("KBoard 댓글 플러그인을 추가로 설치해주세요.\n코스모스팜 홈페이지(http://www.cosmosfarm.com/)에서 다운로드 가능합니다.");history.go(-1);</script>');
}
?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1>
		<?php echo __('KBoard : 게시판 관리', 'kboard')?>
		<a href="http://www.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
		<a href="http://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
		<a href="http://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
		<a href="http://blog.cosmosfarm.com/" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
	</h1>
	<form id="kboard-setting-form" action="<?php echo admin_url('admin-post.php')?>" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field('kboard-setting-execute', 'kboard-setting-execute-nonce');?>
		<input type="hidden" name="action" value="kboard_update_execute">
		<input type="hidden" name="board_id" value="<?php echo $board->id?>">
		<input type="hidden" name="tab_kboard_setting" value="">
		
		<h2 class="nav-tab-wrapper">
			<a href="#tab-kboard-setting-0" class="tab-kboard nav-tab nav-tab-active" onclick="kboard_setting_tab_chnage(0);"><?php echo __('기본설정', 'kboard')?></a>
			<a href="#tab-kboard-setting-1" class="tab-kboard nav-tab" onclick="kboard_setting_tab_chnage(1);"><?php echo __('권한설정', 'kboard')?></a>
			<?php if($board->id):?>
			<a href="#tab-kboard-setting-2" class="tab-kboard nav-tab" onclick="kboard_setting_tab_chnage(2);"><?php echo __('고급설정', 'kboard')?></a>
			<a href="#tab-kboard-setting-3" class="tab-kboard nav-tab" onclick="kboard_setting_tab_chnage(3);"><?php echo __('소셜댓글', 'kboard')?></a>
			<a href="#tab-kboard-setting-4" class="tab-kboard nav-tab" onclick="kboard_setting_tab_chnage(4);"><?php echo __('포인트설정', 'kboard')?></a>
			<a href="#tab-kboard-setting-5" class="tab-kboard nav-tab" onclick="kboard_setting_tab_chnage(5);"><?php echo __('대량관리', 'kboard')?></a>
			<a href="#tab-kboard-setting-6" class="tab-kboard nav-tab" onclick="kboard_setting_tab_chnage(6);"><?php echo __('확장설정', 'kboard')?></a>
			<?php endif?>
		</h2>
		
		<div class="tab-kboard-setting tab-kboard-setting-active">
			<table class="form-table">
				<tbody>
					<?php if(!$board->id):?>
					<tr valign="top">
						<th scope="row"></th>
						<td>
							※ 게시판 생성을 완료하면 추가 설정이 표시됩니다.
						</td>
					</tr>
					<?php endif?>
					<tr valign="top">
						<th scope="row"><label for="board_name">게시판 이름</label></th>
						<td>
							<input type="text" name="board_name" size="30" value="<?php if(!$board->board_name):?>무명게시판 <?php echo date('Y-m-d', current_time('timestamp'))?><?php else:?><?php echo $board->board_name?><?php endif?>" id="board_name">
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="auto_page">게시판 자동설치</label></th>
						<td>
							<select name="auto_page" id="auto_page">
								<option value="">— 선택하기 —</option>
								<?php foreach(get_pages() as $key=>$page):?>
								<option value="<?php echo $page->ID?>" data-permalink="<?php echo esc_url(get_permalink($page->ID))?>"<?php if($meta->auto_page == $page->ID):?> selected<?php endif?>><?php echo $page->post_title?></option>
								<?php endforeach?>
							</select>
							<button type="button" class="button button-small" onclick="kboard_page_open()">페이지 보기</button>
							<p class="description">선택된 페이지에 자동으로 게시판이 설치됩니다. 게시판 자동설치에 문제가 있을 경우 게시판 숏코드를 사용해서 페이지에 게시판을 추가해주세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="latest_target_page">최신글 이동 페이지</label></th>
						<td>
							<select name="latest_target_page" id="latest_target_page">
								<option value="">— 선택하기 —</option>
								<?php foreach(get_pages() as $key=>$page):?>
								<option value="<?php echo $page->ID?>" data-permalink="<?php echo esc_url(get_permalink($page->ID))?>"<?php if($meta->latest_target_page == $page->ID):?> selected<?php endif?>><?php echo $page->post_title?></option>
								<?php endforeach?>
							</select>
							<button type="button" class="button button-small" onclick="kboard_latest_target_page_open()">페이지 보기</button>
							<p class="description">최신글을 클릭하면 선택된 페이지로 이동합니다. 최신글 숏코드를 사용하면 메인페이지 또는 사이드바에 새로 등록된 게시글을 표시할 수 있습니다.</p>
						</td>
					</tr>
					<?php if($board->id):?>
					<tr valign="top">
						<th scope="row"><label for="shortcode">게시판 숏코드(Shortcode)</label></th>
						<td>
							<textarea style="width:600px;max-width:100%;" id="shortcode">[kboard id=<?php echo $board->id?>]</textarea>
							<p class="description">게시판 자동설치에 문제가 있을 경우 이 숏코드를 페이지에 입력하세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="latest_shortcode">최신글 숏코드(Shortcode)</label></th>
						<td>
							<textarea style="width:600px;max-width:100%;" id="latest_shortcode">[kboard_latest id="<?php echo $board->id?>" url="<?php echo $meta->latest_target_page?esc_url(get_permalink($meta->latest_target_page)):'최신글이동페이지주소'?>" rpp="5"]</textarea>
							<p class="description">최신글 리스트를 생성합니다. <span style="font-weight:bold">url</span> 부분에 게시판이 설치된 페이지의 전체 URL을 입력하고 이 숏코드를 메인페이지 또는 사이드바에 입력하세요.</p>
							<p class="description">카테고리 추가 예제: <code>[kboard_latest id="<?php echo $board->id?>" url="<?php echo $meta->latest_target_page?esc_url(get_permalink($meta->latest_target_page)):'최신글이동페이지주소'?>" rpp="5" category1="유머" category2="동영상"]</code></p>
							<p class="description">정렬순서 변경 예제: <code>[kboard_latest id="<?php echo $board->id?>" url="<?php echo $meta->latest_target_page?esc_url(get_permalink($meta->latest_target_page)):'최신글이동페이지주소'?>" rpp="5" sort="newest|best|viewed|updated"]</code></p>
							<p class="description">공지글 제외 예제: <code>[kboard_latest id="<?php echo $board->id?>" url="<?php echo $meta->latest_target_page?esc_url(get_permalink($meta->latest_target_page)):'최신글이동페이지주소'?>" rpp="5" with_notice="false"]</code></p>
							<p class="description">여러 게시판의 최신글을 모아서 하나의 최신글에 보여주려면 <a href="<?php echo admin_url('admin.php?page=kboard_latestview')?>">최신글 모아보기</a> 기능을 사용하세요.</p>
						</td>
					</tr>
					<?php endif?>
					<tr valign="top">
						<th scope="row"><label for="add_menu_page">관리자 페이지에서 게시판 보기</label></th>
						<td>
							<select name="add_menu_page" id="add_menu_page">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->add_menu_page):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">워드프레스 관리자 페이지의 KBoard 메뉴에 게시판을 추가합니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="skin">게시판 스킨 선택</label></th>
						<td>
							<select name="skin" id="skin">
								<?php
								if(!$board->skin) $board->skin = 'default';
								foreach($skin->getList() as $skin_item):
								?>
								<option value="<?php echo $skin_item->name?>"<?php if($board->skin == $skin_item->name):?> selected<?php endif?>><?php echo $skin_item->name?></option>
								<?php endforeach?>
							</select>
							<a class="button button-small" href="<?php echo admin_url('admin.php?page=kboard_store&kbstore_category=kboard')?>">스킨 더보기</a>
							<p class="description">게시판 스킨에 따라 모양과 기능이 변합니다.</p>
							<p class="description"><a href="http://blog.naver.com/chan2rrj/220885880601" onclick="window.open(this.href);return false;">contact-form 스킨 설정 방법 알아보기</a></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="page_rpp">게시글 표시 수</label></th>
						<td>
							<select name="page_rpp" id="page_rpp">
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
								<option value="11"<?php if($board->page_rpp == 11):?> selected<?php endif?>>11개</option>
								<option value="12"<?php if($board->page_rpp == 12):?> selected<?php endif?>>12개</option>
								<option value="13"<?php if($board->page_rpp == 13):?> selected<?php endif?>>13개</option>
								<option value="14"<?php if($board->page_rpp == 14):?> selected<?php endif?>>14개</option>
								<option value="15"<?php if($board->page_rpp == 15):?> selected<?php endif?>>15개</option>
								<option value="16"<?php if($board->page_rpp == 16):?> selected<?php endif?>>16개</option>
								<option value="17"<?php if($board->page_rpp == 17):?> selected<?php endif?>>17개</option>
								<option value="18"<?php if($board->page_rpp == 18):?> selected<?php endif?>>18개</option>
								<option value="19"<?php if($board->page_rpp == 19):?> selected<?php endif?>>19개</option>
								<option value="20"<?php if($board->page_rpp == 20):?> selected<?php endif?>>20개</option>
								<option value="21"<?php if($board->page_rpp == 21):?> selected<?php endif?>>21개</option>
								<option value="22"<?php if($board->page_rpp == 22):?> selected<?php endif?>>22개</option>
								<option value="23"<?php if($board->page_rpp == 23):?> selected<?php endif?>>23개</option>
								<option value="24"<?php if($board->page_rpp == 24):?> selected<?php endif?>>24개</option>
								<option value="25"<?php if($board->page_rpp == 25):?> selected<?php endif?>>25개</option>
								<option value="26"<?php if($board->page_rpp == 26):?> selected<?php endif?>>26개</option>
								<option value="27"<?php if($board->page_rpp == 27):?> selected<?php endif?>>27개</option>
								<option value="28"<?php if($board->page_rpp == 28):?> selected<?php endif?>>28개</option>
								<option value="29"<?php if($board->page_rpp == 29):?> selected<?php endif?>>29개</option>
								<option value="30"<?php if($board->page_rpp == 30):?> selected<?php endif?>>30개</option>
								<option value="40"<?php if($board->page_rpp == 40):?> selected<?php endif?>>40개</option>
								<option value="50"<?php if($board->page_rpp == 50):?> selected<?php endif?>>50개</option>
								<option value="60"<?php if($board->page_rpp == 60):?> selected<?php endif?>>60개</option>
								<option value="70"<?php if($board->page_rpp == 70):?> selected<?php endif?>>70개</option>
								<option value="80"<?php if($board->page_rpp == 80):?> selected<?php endif?>>80개</option>
								<option value="90"<?php if($board->page_rpp == 90):?> selected<?php endif?>>90개</option>
								<option value="100"<?php if($board->page_rpp == 100):?> selected<?php endif?>>100개</option>
							</select>
							<p class="description">한 페이지에 보여지는 게시글 개수를 정합니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="use_comment">댓글 사용</label></th>
						<td>
							<?php if(defined('KBOARD_COMMNETS_VERSION')):?>
								<select name="use_comment" id="use_comment">
										<option value="">비활성화</option>
										<option value="yes"<?php if($board->use_comment == 'yes'):?> selected<?php endif?>>활성화</option>
								</select>
								<p class="description">게시글에 댓글 쓰기를 활성화 합니다. (KBoard 댓글 플러그인 사용)</p>
							<?php else:?>
								<select name="use_comment" id="use_comment">
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
							<select name="comment_skin" id="comment_skin">
								<?php
								if(!$meta->comment_skin) $meta->comment_skin = 'default';
								foreach($comment_skin->getList() as $comment_skin_item):
								?>
								<option value="<?php echo $comment_skin_item->name?>"<?php if($meta->comment_skin == $comment_skin_item->name):?> selected<?php endif?>><?php echo $comment_skin_item->name?></option>
								<?php endforeach?>
							</select>
							<p class="description">댓글의 모양을 선택합니다. (KBoard 댓글 플러그인 사용)</p>
						</td>
					</tr>
					<?php endif?>
					<tr valign="top">
						<th scope="row"><label for="use_editor">글 작성 에디터</label></th>
						<td>
							<select name="use_editor" id="use_editor">
								<option value="">textarea 사용</option>
								<option value="yes"<?php if($board->use_editor == 'yes'):?> selected<?php endif?>>워드프레스 내장 에디터 사용</option>
							</select>
							<p class="description">에디터를 사용해 게시물을 작성할 수 있습니다. 워드프레스에 내장된 에디터를 사용합니다. 다른 에디터 플러그인을 설치하면 호환 됩니다.</p>
							<p class="description">워드프레스 내장 에디터가 제대로 동작하지 않는다면 사용하고 있는 테마 또는 플러그인들을 점검해보세요.</p>
						</td>
					</tr>
					<?php if(!$board->use_editor):?>
					<tr valign="top">
						<th scope="row"><label for="autolink">게시글 본문 자동링크 사용</label></th>
						<td>
							<select name="autolink" id="autolink">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->autolink):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">에디터 textarea를 사용할때 url주소에 자동으로 링크를 생성합니다.</p>
						</td>
					</tr>
					<?php endif?>
					<tr valign="top">
						<th scope="row"><label for="use_category">카테고리 사용</label></th>
						<td>
							<select name="use_category" id="use_category">
								<option value="">비활성화</option>
								<option value="yes"<?php if($board->use_category == 'yes'):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">카테고리를 사용해서 게시물을 분리할 수 있습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="category1_list">카테고리1</label></th>
						<td>
							<input type="text" style="width:350px" name="category1_list" id="category1_list" value="<?php echo $board->category1_list?>">
							<p class="description">카테고리를 입력하세요. 특수문자는 사용할 수 없습니다. 여러 카테고리를 입력하실 경우 콤마(,)로 구분됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="category2_list">카테고리2</label></th>
						<td>
							<input type="text" style="width:350px" name="category2_list" id="category2_list" value="<?php echo $board->category2_list?>">
							<p class="description">카테고리를 입력하세요. 특수문자는 사용할 수 없습니다. 여러 카테고리를 입력하실 경우 콤마(,)로 구분됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="latest_alerts">최신글 이메일 알림</label></th>
						<td>
							<input type="text" style="width:350px" name="latest_alerts" id="latest_alerts" value="<?php echo $meta->latest_alerts?>">
							<p class="description">최신글이 등록되면 입력된 이메일로 알려드립니다. 여러명을 입력하실 경우 콤마(,)로 구분됩니다.</p>
							<p class="description">서버 환경에 따라서 이메일이 전송되지 못 할 수도 있습니다. 이메일 전송에 문제가 있다면 <a href="https://wordpress.org/plugins/wp-mail-smtp/" onclick="window.open(this.href);return false;">WP Mail SMTP</a> 플러그인을 사용해서 이메일 전송 환경을 세팅해보세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="always_view_list">리스트 항상 보기</label></th>
						<td>
							<select name="always_view_list" id="always_view_list">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->always_view_list):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">글 읽기 화면에서도 하단에 게시판 리스트를 보여줍니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="max_attached_count">최대 첨부파일 개수</label></th>
						<td>
							<select name="max_attached_count" id="max_attached_count">
								<option value="">없음</option>
								<option value="1"<?php if($meta->max_attached_count == 1):?> selected<?php endif?>>1개</option>
								<option value="2"<?php if($meta->max_attached_count == 2):?> selected<?php endif?>>2개</option>
								<option value="3"<?php if($meta->max_attached_count == 3):?> selected<?php endif?>>3개</option>
								<option value="4"<?php if($meta->max_attached_count == 4):?> selected<?php endif?>>4개</option>
								<option value="5"<?php if($meta->max_attached_count == 5):?> selected<?php endif?>>5개</option>
								<option value="6"<?php if($meta->max_attached_count == 6):?> selected<?php endif?>>6개</option>
								<option value="7"<?php if($meta->max_attached_count == 7):?> selected<?php endif?>>7개</option>
								<option value="8"<?php if($meta->max_attached_count == 8):?> selected<?php endif?>>8개</option>
								<option value="9"<?php if($meta->max_attached_count == 9):?> selected<?php endif?>>9개</option>
								<option value="10"<?php if($meta->max_attached_count == 10):?> selected<?php endif?>>10개</option>
								<option value="11"<?php if($meta->max_attached_count == 11):?> selected<?php endif?>>11개</option>
								<option value="12"<?php if($meta->max_attached_count == 12):?> selected<?php endif?>>12개</option>
								<option value="13"<?php if($meta->max_attached_count == 13):?> selected<?php endif?>>13개</option>
								<option value="14"<?php if($meta->max_attached_count == 14):?> selected<?php endif?>>14개</option>
								<option value="15"<?php if($meta->max_attached_count == 15):?> selected<?php endif?>>15개</option>
								<option value="16"<?php if($meta->max_attached_count == 16):?> selected<?php endif?>>16개</option>
								<option value="17"<?php if($meta->max_attached_count == 17):?> selected<?php endif?>>17개</option>
								<option value="18"<?php if($meta->max_attached_count == 18):?> selected<?php endif?>>18개</option>
								<option value="19"<?php if($meta->max_attached_count == 19):?> selected<?php endif?>>19개</option>
								<option value="20"<?php if($meta->max_attached_count == 20):?> selected<?php endif?>>20개</option>
								<option value="25"<?php if($meta->max_attached_count == 25):?> selected<?php endif?>>25개</option>
								<option value="30"<?php if($meta->max_attached_count == 30):?> selected<?php endif?>>30개</option>
								<option value="50"<?php if($meta->max_attached_count == 50):?> selected<?php endif?>>50개</option>
								<option value="100"<?php if($meta->max_attached_count == 100):?> selected<?php endif?>>100개</option>
							</select>
							<p class="description">게시글당 최대 첨부파일 개수를 정합니다. 일부 스킨에서는 적용되지 않습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="list_sort_numbers">리스트 게시글 번호 표시</label></th>
						<td>
							<select name="list_sort_numbers" id="list_sort_numbers">
								<option value="desc">내림차순 (3,2,1)</option>
								<option value="asc"<?php if($meta->list_sort_numbers == 'asc'):?> selected<?php endif?>>오름차순 (1,2,3)</option>
							</select>
							<p class="description">리스트에서 게시글 번호를 내림차순 또는 오름차순으로 표시할 수 있습니다. 실제 게시글 정렬과는 무관하게 번호 표시만 바뀝니다. 번호 표시가 없는 스킨은 적용되지 않습니다.</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="tab-kboard-setting">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="permission_admin">최고관리자그룹</label></th>
						<td>
							<input type="hidden" name="permission_admin_roles" value="">
							<?php $admin_roles = $board->getAdminRoles();?>
							<?php foreach(get_editable_roles() as $key=>$value):?>
								<label><input type="checkbox" name="permission_admin_roles[]" value="<?php echo $key?>"<?php if($key=='administrator'):?> onclick="return false"<?php endif?><?php if($key=='administrator' || in_array($key, $admin_roles)):?> checked<?php endif?>><?php echo _x($value['name'], 'User role')?></label>
							<?php endforeach?>
							<p class="description">글쓴이는 실제 글쓴이를 지칭하는게 아니라 워드프레스 역할(Role) 명칭입니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="admin_user">선택된 관리자</label></th>
						<td>
							<input type="text" style="width:350px" name="admin_user" id="admin_user" value="<?php echo $board->admin_user?>">
							<p class="description">사용자 아이디를 입력하세요. 여러명을 입력하실 경우 콤마(,)로 구분됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="permission_read">읽기권한</label></th>
						<td>
							<select name="permission_read" id="permission_read" onchange="kboard_permission_roles_view('.kboard-permission-read-roles-view', this.value)">
								<option value="all"<?php if($board->permission_read == 'all'):?> selected<?php endif?>>
									<?php echo kboard_permission('all')?>
								</option>
								<option value="author"<?php if($board->permission_read == 'author'):?> selected<?php endif?>>
									<?php echo kboard_permission('author')?>
								</option>
								<!--
								<option value="editor"<?php if($board->permission_read == 'editor'):?> selected<?php endif?>>
									<?php echo kboard_permission('editor')?>
								</option>
								<option value="administrator"<?php if($board->permission_read == 'administrator'):?> selected<?php endif?>>
									<?php echo kboard_permission('administrator')?>
								</option>
								-->
								<option value="roles"<?php if($board->permission_read == 'roles'):?> selected<?php endif?>>
									<?php echo kboard_permission('roles')?>
								</option>
							</select>
							<div class="kboard-permission-read-roles-view<?php if($board->permission_read != 'roles'):?> kboard-hide<?php endif?>">
								<input type="hidden" name="permission_read_roles" value="">
								<?php $read_roles = $board->getReadRoles();?>
								<?php foreach(get_editable_roles() as $key=>$value):?>
									<label><input type="checkbox" name="permission_read_roles[]" value="<?php echo $key?>"<?php if($key=='administrator'):?> onclick="return false"<?php endif?><?php if($key=='administrator' || in_array($key, $read_roles)):?> checked<?php endif?>><?php echo _x($value['name'], 'User role')?></label>
								<?php endforeach?>
							</div>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="permission_write">쓰기권한</label></th>
						<td>
							<select name="permission_write" id="permission_write" onchange="kboard_permission_roles_view('.kboard-permission-write-roles-view', this.value)">
								<option value="all"<?php if($board->permission_read == 'all'):?> selected<?php endif?>>
									<?php echo kboard_permission('all')?>
								</option>
								<option value="author"<?php if($board->permission_write == 'author'):?> selected<?php endif?>>
									<?php echo kboard_permission('author')?>
								</option>
								<!--
								<option value="editor"<?php if($board->permission_write == 'editor'):?> selected<?php endif?>>
									<?php echo kboard_permission('editor')?>
								</option>
								<option value="administrator"<?php if($board->permission_write == 'administrator'):?> selected<?php endif?>>
									<?php echo kboard_permission('administrator')?>
								</option>
								-->
								<option value="roles"<?php if($board->permission_write == 'roles'):?> selected<?php endif?>>
									<?php echo kboard_permission('roles')?>
								</option>
							</select>
							<div class="kboard-permission-write-roles-view<?php if($board->permission_write != 'roles'):?> kboard-hide<?php endif?>">
								<input type="hidden" name="permission_write_roles" value="">
								<?php $write_roles = $board->getWriteRoles();?>
								<?php foreach(get_editable_roles() as $key=>$value):?>
									<label><input type="checkbox" name="permission_write_roles[]" value="<?php echo $key?>"<?php if($key=='administrator'):?> onclick="return false"<?php endif?><?php if($key=='administrator' || in_array($key, $write_roles)):?> checked<?php endif?>><?php echo _x($value['name'], 'User role')?></label>
								<?php endforeach?>
							</div>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="permission_reply">답글쓰기권한</label></th>
						<td>
							<select name="permission_reply" id="permission_reply" onchange="kboard_permission_roles_view('.kboard-permission-reply-roles-view', this.value)">
								<option value=""<?php if(!$meta->permission_reply):?> selected<?php endif?>>
									<?php echo kboard_permission('all')?>
								</option>
								<option value="1"<?php if($meta->permission_reply == '1'):?> selected<?php endif?>>
									<?php echo kboard_permission('author')?>
								</option>
								<option value="roles"<?php if($meta->permission_reply == 'roles'):?> selected<?php endif?>>
									<?php echo kboard_permission('roles')?>
								</option>
							</select>
							<div class="kboard-permission-reply-roles-view<?php if($meta->permission_reply != 'roles'):?> kboard-hide<?php endif?>">
								<input type="hidden" name="permission_reply_roles" value="">
								<?php $reply_roles = $board->getReplyRoles();?>
								<?php foreach(get_editable_roles() as $key=>$value):?>
									<label><input type="checkbox" name="permission_reply_roles[]" value="<?php echo $key?>"<?php if($key=='administrator'):?> onclick="return false"<?php endif?><?php if($key=='administrator' || in_array($key, $reply_roles)):?> checked<?php endif?>><?php echo _x($value['name'], 'User role')?></label>
								<?php endforeach?>
							</div>
							<p class="description">일부 스킨에서는 적용되지 않습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="permission_comment_write">댓글쓰기권한</label></th>
						<td>
							<select name="permission_comment_write" id="permission_comment_write" onchange="kboard_permission_roles_view('.kboard-permission-comment-write-roles-view', this.value)">
								<option value=""<?php if(!$meta->permission_comment_write):?> selected<?php endif?>>
									<?php echo kboard_permission('all')?>
								</option>
								<option value="1"<?php if($meta->permission_comment_write == '1'):?> selected<?php endif?>>
									<?php echo kboard_permission('author')?>
								</option>
								<option value="roles"<?php if($meta->permission_comment_write == 'roles'):?> selected<?php endif?>>
									<?php echo kboard_permission('roles')?>
								</option>
							</select>
							<div class="kboard-permission-comment-write-roles-view<?php if($meta->permission_comment_write != 'roles'):?> kboard-hide<?php endif?>">
								<input type="hidden" name="permission_comment_write_roles" value="">
								<?php $comment_roles = $board->getCommentRoles();?>
								<?php foreach(get_editable_roles() as $key=>$value):?>
									<label><input type="checkbox" name="permission_comment_write_roles[]" value="<?php echo $key?>"<?php if($key=='administrator'):?> onclick="return false"<?php endif?><?php if($key=='administrator' || in_array($key, $comment_roles)):?> checked<?php endif?>><?php echo _x($value['name'], 'User role')?></label>
								<?php endforeach?>
							</div>
						</td>
					</tr>
					<!--
					<tr valign="top">
						<th scope="row"><label for="permission_order">주문하기권한</label></th>
						<td>
							<select name="permission_order" id="permission_order" onchange="kboard_permission_roles_view('.kboard-permission-order-roles-view', this.value)">
								<option value=""<?php if(!$meta->permission_order):?> selected<?php endif?>>
									<?php echo kboard_permission('all')?>
								</option>
								<option value="1"<?php if($meta->permission_order == '1'):?> selected<?php endif?>>
									<?php echo kboard_permission('author')?>
								</option>
								<option value="roles"<?php if($meta->permission_order == 'roles'):?> selected<?php endif?>>
									<?php echo kboard_permission('roles')?>
								</option>
							</select>
							<div class="kboard-permission-order-roles-view<?php if($meta->permission_order != 'roles'):?> kboard-hide<?php endif?>">
								<input type="hidden" name="permission_order_roles" value="">
								<?php $order_roles = $board->getOrderRoles();?>
								<?php foreach(get_editable_roles() as $key=>$value):?>
									<label><input type="checkbox" name="permission_order_roles[]" value="<?php echo $key?>"<?php if($key=='administrator'):?> onclick="return false"<?php endif?><?php if($key=='administrator' || in_array($key, $order_roles)):?> checked<?php endif?>><?php echo _x($value['name'], 'User role')?></label>
								<?php endforeach?>
							</div>
						</td>
					</tr>
					-->
					<tr valign="top">
						<th scope="row"><label for="permission_vote">추천권한</label></th>
						<td>
							<select name="permission_vote" id="permission_vote" onchange="kboard_permission_roles_view('.kboard-permission-vote-roles-view', this.value)">
								<option value=""<?php if(!$meta->permission_vote):?> selected<?php endif?>>
									<?php echo kboard_permission('all')?>
								</option>
								<option value="1"<?php if($meta->permission_vote == '1'):?> selected<?php endif?>>
									<?php echo kboard_permission('author')?>
								</option>
								<option value="roles"<?php if($meta->permission_vote == 'roles'):?> selected<?php endif?>>
									<?php echo kboard_permission('roles')?>
								</option>
							</select>
							<div class="kboard-permission-vote-roles-view<?php if($meta->permission_vote != 'roles'):?> kboard-hide<?php endif?>">
								<input type="hidden" name="permission_vote_roles" value="">
								<?php $vote_roles = $board->getVoteRoles();?>
								<?php foreach(get_editable_roles() as $key=>$value):?>
									<label><input type="checkbox" name="permission_vote_roles[]" value="<?php echo $key?>"<?php if($key=='administrator'):?> onclick="return false"<?php endif?><?php if($key=='administrator' || in_array($key, $vote_roles)):?> checked<?php endif?>><?php echo _x($value['name'], 'User role')?></label>
								<?php endforeach?>
							</div>
							<p class="description">게시판에서 좋아요, 싫어요 기능을 제한할 수 있습니다. 스킨에 따라서 버튼이 숨겨지거나 그렇지 않을 수 있습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="permission_list">리스트 보기(Beta)</label></th>
						<td>
							<select name="permission_list" id="permission_list" onchange="kboard_permission_list_check(true)">
								<option value="">
									전체글 보기
								</option>
								<option value="1"<?php if($meta->permission_list):?> selected<?php endif?>>
									본인의 글만 보기
								</option>
							</select>
							<div class="kboard-permission-list-options-view<?php if(!$meta->permission_list):?> kboard-hide<?php endif?>">
								<input type="hidden" name="permission_access" value="">
								<label><input type="checkbox" name="permission_access" value="1"<?php if($meta->permission_list && $meta->permission_access):?> checked<?php endif?>>비로그인 사용자는 로그인 페이지로 이동</label>
							</div>
							<p class="description">본인의 글만 보기로 설정하면 관리자와의 1:1 게시판으로 운영이 가능합니다. 공지사항은 항상 표시됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="permit">관리자 승인(Beta)</label></th>
						<td>
							<select name="permit" id="permit">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->permit):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">읽기/쓰기 권한과는 관계없이 관리자가 승인한 게시글만 정상적으로 보입니다. <a href="<?php echo admin_url('admin.php?page=kboard_content_list')?>">전체 게시글 관리</a></p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php if($board->id):?>
		<div class="tab-kboard-setting">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="shortcode_execute">게시글 숏코드(Shortcode) 실행</label></th>
						<td>
							<select name="shortcode_execute" id="shortcode_execute">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->shortcode_execute):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">게시글 본문에 글쓴이가 입력한 워드프레스 숏코드를 실행합니다. 사용자가 워드프레스 내장 기능을 사용할 수 있어 보안에 주의해야 합니다.  <a href="http://blog.naver.com/chan2rrj/50179426321" onclick="window.open(this.href);return false;">더보기</a></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="default_content">본문 기본 양식</label></th>
						<td>
							<?php if($board->use_editor):?>
								<?php wp_editor($meta->default_content, 'default_content')?>
							<?php else:?>
								<textarea name="default_content" id="default_content" style="width:600px;max-width:100%;height:300px;"><?php echo $meta->default_content?></textarea>
							<?php endif;?>
							<p class="description">게시판 글 작성시 보여질 기본 양식입니다. 기본값은 빈 값입니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="reply_copy_content">답글 기본 내용</label></th>
						<td>
							<select name="reply_copy_content" id="reply_copy_content">
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
							<a href="<?php echo home_url("?kboard_id={$board->id}")?>" onclick="window.open(this.href);return false;"><?php echo home_url("?kboard_id={$board->id}")?></a>
							<?php endif?>
							<p class="description">고유주소는 독립적 레이아웃 편집 및 아이프레임 삽입 등 고급 사용자를 위한 편의 기능입니다. 일반 사용자는 자동설치 또는 숏코드(Shortcode)를 사용해 게시판을 생성하세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="pass_autop">특정 테마 레이아웃 깨짐 방지</label></th>
						<td>
							<select name="pass_autop" id="pass_autop">
								<option value="disable"<?php if($meta->pass_autop == 'disable'):?> selected<?php endif?>>비활성화</option>
								<option value="enable"<?php if($meta->pass_autop == 'enable'):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">문제가 없다면 활성화 하지 마세요. 특정 테마에서 content에 자동으로 P태그가 추가되어 레이아웃이 깨지는 현상이 발생됩니다. 활성화시 content에 P태그가 추가되기 전에 게시판을 출력시킵니다. <a href="http://blog.naver.com/chan2rrj/50178536050" onclick="window.open(this.href);return false;">더보기</a></p>
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
					<tr valign="top">
						<th scope="row"><label for="conversion_tracking_code">전환추적 코드</label></th>
						<td>
							<textarea name="conversion_tracking_code" id="conversion_tracking_code" style="width:600px;max-width:100%;height:100px;"><?php echo $meta->conversion_tracking_code?></textarea>
							<p class="description">게시글 등록 전환추적을 위한 코드(HTML 태그 또는 자바스크립트 소스)를 입력해주세요. 이 코드가 존재하면 새로운 게시글이 저장된 직후 실행됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="default_build_mod">게시판 기본 화면(Beta)</label></th>
						<td>
							<select name="default_build_mod" id="default_build_mod">
								<option value="">글목록 화면</option>
								<option value="editor"<?php if($meta->default_build_mod == 'editor'):?> selected<?php endif?>>글쓰기 화면</option>
							</select>
							<p class="description">게시판에서 첫 번째로 보일 화면을 정합니다. 별다른 이유가 없다면 글목록 화면으로 선택해주세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="after_executing_mod">글 쓴 후 이동 화면(Beta)</label></th>
						<td>
							<select name="after_executing_mod" id="after_executing_mod">
								<option value="">작성된 글 화면</option>
								<option value="list"<?php if($meta->after_executing_mod == 'list'):?> selected<?php endif?>>글목록 화면</option>
								<option value="editor"<?php if($meta->after_executing_mod == 'editor'):?> selected<?php endif?>>글쓰기 화면</option>
							</select>
							<p class="description">글쓰기를 완료하고 보일 화면을 정합니다. 보통의 경우라면 작성된 글 화면으로 이동해주세요.</p>
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
							먼저 <a href="http://www.cosmosfarm.com/plugin/comments" onclick="window.open(this.href);return false;">코스모스팜 소셜댓글</a> 관리사이트에서 이 워드프레스 사이트를 <a href="http://www.cosmosfarm.com/plugin/comments/create" onclick="window.open(this.href);return false;">등록</a>해주세요.
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="comments_plugin_id">소셜댓글 ID</label></th>
						<td>
							<input type="text" name="comments_plugin_id" id="comments_plugin_id" value="<?php echo $meta->comments_plugin_id?>">
							<p class="description"><a href="http://www.cosmosfarm.com/plugin/comments/sites" onclick="window.open(this.href);return false;">등록된 사이트</a> » 설치하기 페이지에 나와있는 ID값을 입력해주세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="use_comments_plugin">소셜댓글 사용</label></th>
						<td>
							<select name="use_comments_plugin" id="use_comments_plugin">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->use_comments_plugin):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">게시판에 KBoard 댓글을 비활성화 하고 코스모스팜 소셜댓글을 사용합니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="comments_plugin_row">댓글 표시</label></th>
						<td>
							<select name="comments_plugin_row" id="comments_plugin_row">
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
		<div class="tab-kboard-setting">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"></th>
						<td>
							KBoard의 포인트는 <a href="https://ko.wordpress.org/plugins/mycred/" onclick="window.open(this.href);return false;">myCRED</a> 플러그인의 기반으로 동작하기 때문에 포인트 기능을 사용하시려면 반드시 <a href="https://ko.wordpress.org/plugins/mycred/" onclick="window.open(this.href);return false;">myCRED</a> 플러그인을 설치해주세요.
						</td>
					</tr>
					<!-- 글쓰기 증가 포인트 -->
					<tr valign="top">
						<th scope="row"><label for="document_insert_up_point"><?php echo __('Writing increase points', 'kboard')?></label></th>
						<td>
							<input type="number" name="document_insert_up_point" id="document_insert_up_point" value="<?php echo $meta->document_insert_up_point?>" placeholder="<?php echo __('Please enter only numbers', 'kboard')?>">
							<p class="description">새로운 글을 쓰면 작성자에게 포인트를 지급합니다.</p>
						</td>
					</tr>
					<!-- 글쓰기 감소 포인트 -->
					<tr valign="top">
						<th scope="row"><label for="document_insert_down_point"><?php echo __('Writing decrease points', 'kboard')?></label></th>
						<td>
							<input type="number" name="document_insert_down_point" id="document_insert_down_point" value="<?php echo $meta->document_insert_down_point?>" placeholder="<?php echo __('Please enter only numbers', 'kboard')?>">
							<p class="description">새로운 글을 쓰면 작성자의 포인트를 차감합니다. 작성자는 포인트가 있어야 글을 쓸 수 있습니다.</p>
						</td>
					</tr>
					<!-- 글삭제 증가 포인트 -->
					<tr valign="top">
						<th scope="row"><label for="document_delete_up_point"><?php echo __('Deleted increment points', 'kboard')?></label></th>
						<td>
							<input type="number" name="document_delete_up_point" id="document_delete_up_point" value="<?php echo $meta->document_delete_up_point?>" placeholder="<?php echo __('Please enter only numbers', 'kboard')?>">
							<p class="description">게시글이 삭제되면 작성자에게 포인트를 지급합니다.</p>
						</td>
					</tr>
					<!-- 글삭제 감소 포인트 -->
					<tr valign="top">
						<th scope="row"><label for="document_delete_down_point"><?php echo __('Deleted decrease points', 'kboard')?></label></th>
						<td>
							<input type="number" name="document_delete_down_point" id="document_delete_down_point" value="<?php echo $meta->document_delete_down_point?>" placeholder="<?php echo __('Please enter only numbers', 'kboard')?>">
							<p class="description">게시글이 삭제되면 작성자의 포인트를 차감합니다.</p>
						</td>
					</tr>
					<!-- 글읽기 감소 포인트 -->
					<tr valign="top">
						<th scope="row"><label for="document_read_down_point"><?php echo __('Reading decrease points', 'kboard')?></label></th>
						<td>
							<input type="number" name="document_read_down_point" id="document_read_down_point" value="<?php echo $meta->document_read_down_point?>" placeholder="<?php echo __('Please enter only numbers', 'kboard')?>">
							<p class="description">게시판에 글을 읽으면 사용자의 포인트를 차감합니다. 사용자는 포인트가 있어야 글을 읽을 수 있습니다. 처음 읽을 때만 포인트가 차감됩니다.</p>
						</td>
					</tr>
					<!-- 첨부파일 다운로드 감소 포인트 -->
					<tr valign="top">
						<th scope="row"><label for="attachment_download_down_point"><?php echo __('Attachment download decrease points', 'kboard')?></label></th>
						<td>
							<input type="number" name="attachment_download_down_point" id="attachment_download_down_point" value="<?php echo $meta->attachment_download_down_point?>" placeholder="<?php echo __('Please enter only numbers', 'kboard')?>">
							<p class="description">첨부파일 다운로드시 사용자의 포인트를 차감합니다. 사용자는 포인트가 있어야 첨부파일을 다운로드할 수 있습니다.</p>
						</td>
					</tr>
					<!-- 댓글쓰기 증가 포인트 -->
					<tr valign="top">
						<th scope="row"><label for="comment_insert_up_point"><?php echo __('Writing comment increase points', 'kboard')?></label></th>
						<td>
							<input type="number" name="comment_insert_up_point" id="comment_insert_up_point" value="<?php echo $meta->comment_insert_up_point?>" placeholder="<?php echo __('Please enter only numbers', 'kboard')?>">
							<p class="description">새로운 댓글을 쓰면 작성자에게 포인트를 지급합니다.</p>
						</td>
					</tr>
					<!-- 댓글쓰기 감소 포인트 -->
					<tr valign="top">
						<th scope="row"><label for="comment_insert_down_point"><?php echo __('Writing comment decrease points', 'kboard')?></label></th>
						<td>
							<input type="number" name="comment_insert_down_point" id="comment_insert_down_point" value="<?php echo $meta->comment_insert_down_point?>" placeholder="<?php echo __('Please enter only numbers', 'kboard')?>">
							<p class="description">새로운 댓글을 쓰면 작성자의 포인트를 차감합니다. 작성자는 포인트가 있어야 댓글을 쓸 수 있습니다.</p>
						</td>
					</tr>
					<!-- 댓글삭제 증가 포인트 -->
					<tr valign="top">
						<th scope="row"><label for="comment_delete_up_point"><?php echo __('Deleted comment increment points', 'kboard')?></label></th>
						<td>
							<input type="number" name="comment_delete_up_point" id="comment_delete_up_point" value="<?php echo $meta->comment_delete_up_point?>" placeholder="<?php echo __('Please enter only numbers', 'kboard')?>">
							<p class="description">등록된 댓글이 삭제되면 작성자에게 포인트를 지급합니다.</p>
						</td>
					</tr>
					<!-- 댓글삭제 감소 포인트 -->
					<tr valign="top">
						<th scope="row"><label for="comment_delete_down_point"><?php echo __('Deleted comment decrease points', 'kboard')?></label></th>
						<td>
							<input type="number" name="comment_delete_down_point" id="comment_delete_down_point" value="<?php echo $meta->comment_delete_down_point?>" placeholder="<?php echo __('Please enter only numbers', 'kboard')?>">
							<p class="description">등록된 댓글이 삭제되면 작성자의 포인트를 차감합니다.</p>
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
							대량으로 게시판의 게시글을 등록하거나 다운로드 할 수 있습니다.
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">CSV 파일 다운로드</th>
						<td>
							<input type="button" class="button-primary" value="<?php echo __('Download', 'kboard')?>" onclick="window.location.href='<?php echo wp_nonce_url(add_query_arg(array('action'=>'kboard_csv_download_execute', 'board_id'=>$board->id), admin_url('admin-post.php')), 'kboard-csv-download-execute', 'kboard-csv-download-execute-nonce')?>'">
							<p class="description">대략 <?php echo number_format($board->getTotal())?>개의 게시글 정보를 다운로드합니다. 게시글 양이 많다면 웹호스팅의 트래픽 사용량이 높아지니 주의해주세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">CSV 파일 업로드</th>
						<td>
							<input type="hidden" name="board_id" value="<?php echo $board->id?>">
							<select name="kboard_csv_upload_option">
								<option value="keep">기존 게시글을 유지하고 추가 등록</option>
								<option value="update">같은 uid 값 기준으로 기존 게시글 정보를 업데이트</option>
								<option value="delete">등록된 모든 게시글과 첨부파일을 삭제하고 새로 등록</option>
							</select>
							<br>
							<input type="file" name="kboard_csv_upload_file" accept=".csv">
							<br>
							<input type="button" class="button-primary" value="<?php echo __('Upload', 'kboard')?>" onclick="kboard_csv_upload()">
							<p class="description">너무 많은 데이터를 한 번에 업로드하게 되면 에러가 발생될 수 있으니 가급적 나눠서 여러 번 업로드해주세요.<br>댓글과 첨부파일은 등록되지 않습니다.</p>
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
							KBoard는 직접 확장 플러그인 개발이 가능하며 추가된 게시판 기능을 이곳에 표시 할 수 있습니다. <a href="http://www.cosmosfarm.com/products/kboard/hooks" onclick="window.open(this.href);return false;">더보기</a> 
						</td>
					</tr>
				</tbody>
			</table>
			<?php
			// 관리자 페이지에서 게시판 확장설정 탭에 내용을 추가합니다.
			$html = '';
			$html = apply_filters('kboard_extends_setting', $html, $meta, $board->id);
			$html = apply_filters("kboard_{$board->skin}_extends_setting", $html, $meta, $board->id);
			echo $html;
			?>
		</div>
		<?php endif?>
		
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php echo __('변경 사항 저장', 'kboard')?>">
		</p>
	</form>
</div>

<script>
function kboard_setting_tab_init(){
	var index = location.hash.slice(1).replace('tab-kboard-setting-', '');
	kboard_setting_tab_chnage(index);
}
kboard_setting_tab_init();
function kboard_setting_tab_chnage(index){
	jQuery('.tab-kboard').removeClass('nav-tab-active').eq(index).addClass('nav-tab-active');
	jQuery('.tab-kboard-setting').removeClass('tab-kboard-setting-active').eq(index).addClass('tab-kboard-setting-active');
	jQuery('input[name=tab_kboard_setting]').val(index);
}
function kboard_permission_roles_view(bind, value){
	if(value == 'roles'){
		jQuery(bind).removeClass('kboard-hide');
	}
	else{
		jQuery(bind).addClass('kboard-hide');
	}
	kboard_permission_list_check();
}
function kboard_page_open(){
	var permalink = jQuery('option:selected', 'select[name=auto_page]').data('permalink');
	if(permalink){
		window.open(permalink);
	}
}
function kboard_latest_target_page_open(){
	var permalink = jQuery('option:selected', 'select[name=latest_target_page]').data('permalink');
	if(permalink){
		window.open(permalink);
	}
}
function kboard_permission_list_check(message){
	if(jQuery('select[name=permission_list]').val()){
		jQuery('.kboard-permission-list-options-view').removeClass('kboard-hide');
		
		if(jQuery('select[name=permission_read]').val() == 'all' || jQuery('select[name=permission_write]').val() == 'all'){
			if(message){
				alert('읽기권한과 쓰기권한을 모두 로그인 사용자 이상으로 변경해주세요.');
			}
			jQuery('select[name=permission_list]').val('');
			jQuery('.kboard-permission-list-options-view').addClass('kboard-hide');
		}
	}
	else{
		jQuery('.kboard-permission-list-options-view').addClass('kboard-hide');
	}
}
function kboard_csv_upload(){
	jQuery('input[name=action]', '#kboard-setting-form').val('kboard_csv_upload_execute');
	jQuery('#kboard-setting-form').submit();
}
</script>