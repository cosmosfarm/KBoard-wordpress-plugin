<?php
if(!defined('ABSPATH')) exit;
if(!defined('KBOARD_COMMNETS_VERSION')){
	die('<script>alert("KBoard 댓글 플러그인을 추가로 설치해주세요.\n코스모스팜 홈페이지(https://www.cosmosfarm.com/)에서 다운로드 가능합니다.");history.go(-1);</script>');
}
?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1 class="wp-heading-inline"><?php echo __('KBoard : 게시판 관리', 'kboard')?></h1>
	<a href="https://www.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
	<a href="https://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
	<a href="https://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
	<a href="https://blog.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
	
	<hr class="wp-header-end">
	
	<form id="kboard-setting-form" action="<?php echo admin_url('admin-post.php')?>" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field('kboard-setting-execute', 'kboard-setting-execute-nonce');?>
		<input type="hidden" name="action" value="kboard_update_execute">
		<input type="hidden" name="board_id" value="<?php echo $board->id?>">
		<input type="hidden" name="tab_kboard_setting" value="">
		
		<h2 class="nav-tab-wrapper">
			<a href="#tab-kboard-setting-0" class="tab-kboard nav-tab nav-tab-active" onclick="kboard_setting_tab_change(0);"><?php echo __('기본설정', 'kboard')?></a>
			<a href="#tab-kboard-setting-1" class="tab-kboard nav-tab" onclick="kboard_setting_tab_change(1);"><?php echo __('권한설정', 'kboard')?></a>
			<?php if($board->id):?>
			<a href="#tab-kboard-setting-2" class="tab-kboard nav-tab" onclick="kboard_setting_tab_change(2);"><?php echo __('입력 필드', 'kboard')?></a>
			<a href="#tab-kboard-setting-3" class="tab-kboard nav-tab" onclick="kboard_setting_tab_change(3);"><?php echo __('기본 카테고리(NEW)', 'kboard')?></a>
			<a href="#tab-kboard-setting-4" class="tab-kboard nav-tab" onclick="kboard_setting_tab_change(4);"><?php echo __('계층형 카테고리', 'kboard')?></a>
			<a href="#tab-kboard-setting-5" class="tab-kboard nav-tab" onclick="kboard_setting_tab_change(5);"><?php echo __('고급설정', 'kboard')?></a>
			<a href="#tab-kboard-setting-6" class="tab-kboard nav-tab" onclick="kboard_setting_tab_change(6);"><?php echo __('사이드톡 연동(NEW)', 'kboard')?></a>
			<a href="#tab-kboard-setting-7" class="tab-kboard nav-tab" onclick="kboard_setting_tab_change(7);"><?php echo __('포인트설정', 'kboard')?></a>
			<a href="#tab-kboard-setting-8" class="tab-kboard nav-tab" onclick="kboard_setting_tab_change(8);"><?php echo __('대량관리', 'kboard')?></a>
			<a href="#tab-kboard-setting-9" class="tab-kboard nav-tab" onclick="kboard_setting_tab_change(9);"><?php echo __('인기글 표시', 'kboard')?></a>
			<a href="#tab-kboard-setting-10" class="tab-kboard nav-tab" onclick="kboard_setting_tab_change(10);"><?php echo __('알림(NEW)', 'kboard')?></a>
			<a href="#tab-kboard-setting-11" class="tab-kboard nav-tab" onclick="kboard_setting_tab_change(11);"><?php echo __('확장설정', 'kboard')?></a>
			<?php endif?>
		</h2>
		
		<div class="tab-kboard-setting tab-kboard-setting-active">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"></th>
						<td>
							<a href="https://blog.naver.com/PostView.nhn?blogId=chan2rrj&logNo=221273415599" onclick="window.open(this.href);return false;">워드프레스 페이지에 게시판 삽입하기 방법</a>
						</td>
					</tr>
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
							<input type="text" name="board_name" id="board_name" class="regular-text" value="<?php if(!$board->board_name):?>무명게시판 <?php echo date('Y-m-d', current_time('timestamp'))?><?php else:?><?php echo $board->board_name?><?php endif?>">
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="auto_page">게시판 자동설치</label> <span style="font-size:12px;color:gray;">(선택)</span></th>
						<td>
							<select name="auto_page" id="auto_page">
								<option value="">— 선택하기 —</option>
								<?php foreach(get_pages() as $key=>$page):?>
								<option value="<?php echo $page->ID?>" data-permalink="<?php echo esc_url(get_permalink($page->ID))?>"<?php if($meta->auto_page == $page->ID):?> selected<?php endif?>><?php echo $page->post_title?></option>
								<?php endforeach?>
							</select>
							<button type="button" class="button button-small" onclick="kboard_page_open()">페이지 보기</button>
							<p class="description">선택된 페이지에 자동으로 게시판이 설치됩니다.</p>
							<p class="description">게시판 자동설치에 문제가 있을 경우 게시판 숏코드를 사용해서 페이지에 게시판을 추가해주세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="latest_target_page">최신글 이동 페이지</label> <span style="font-size:12px;color:red;">(필수)</span></th>
						<td>
							<select name="latest_target_page" id="latest_target_page">
								<option value="">— 선택하기 —</option>
								<?php foreach(get_pages() as $key=>$page):?>
								<option value="<?php echo $page->ID?>" data-permalink="<?php echo esc_url(get_permalink($page->ID))?>"<?php if($meta->latest_target_page == $page->ID):?> selected<?php endif?>><?php echo $page->post_title?></option>
								<?php endforeach?>
							</select>
							<button type="button" class="button button-small" onclick="kboard_latest_target_page_open()">페이지 보기</button>
							<p class="description">최신글을 클릭하면 선택된 페이지로 이동합니다.</p>
							<p class="description">최신글 숏코드를 사용하면 메인페이지 또는 사이드바에 새로 등록된 게시글을 표시할 수 있습니다.</p>
						</td>
					</tr>
					<?php if($board->id):?>
					<tr valign="top">
						<th scope="row"><label for="shortcode">게시판 숏코드(Shortcode)</label></th>
						<td>
							<textarea id="shortcode" class="kboard-copy-text" style="width:600px;max-width:100%;" readonly>[kboard id=<?php echo $board->id?>]</textarea>
							<p class="description">게시판 자동설치에 문제가 있을 경우 이 숏코드를 페이지에 입력하세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="latest_shortcode">최신글 숏코드(Shortcode)</label></th>
						<td>
							<textarea id="latest_shortcode" class="kboard-copy-text" style="width:600px;max-width:100%;" readonly>[kboard_latest id="<?php echo $board->id?>" url="<?php echo $meta->latest_target_page?esc_url(get_permalink($meta->latest_target_page)):'최신글이동페이지주소'?>" rpp="5"]</textarea>
							<p class="description">최신글 리스트를 생성합니다. <span style="font-weight:bold">url</span> 부분에 게시판이 설치된 페이지의 전체 URL을 입력하고 이 숏코드를 메인페이지 또는 사이드바에 입력하세요.</p>
							<p class="description">여러 게시판의 최신글을 모아서 하나의 최신글에 보여주려면 <a href="<?php echo admin_url('admin.php?page=kboard_latestview')?>" onclick="window.open(this.href);return false;">최신글 모아보기</a> 기능을 사용하세요.</p>
							<p class="description"><a href="https://blog.cosmosfarm.com/?p=1145" onclick="window.open(this.href);return false;">최신글 숏코드 사용 예제 알아보기</a></p>
						</td>
					</tr>
					<?php endif?>
					<!-- <tr valign="top">
						<th scope="row"><label for="latest_list_columns">최신글 리스트에 추가로 표시할 정보[개발중]</label></th>
						<td>
							<input type="hidden" name="latest_list_columns" value="">
							<?php
							$latest_list_columns = $board->getLatestListColumns(); // 👈 함수로 가져옴
							$options = array(
								'author' => '작성자'
							);
							?>
							<?php foreach($options as $key => $label): ?>
								<label>
									<input type="checkbox" name="latest_list_columns[]" value="<?php echo esc_attr($key) ?>" <?php if(in_array($key, $latest_list_columns)): ?>checked<?php endif ?>>
									<?php echo esc_html($label) ?>
								</label>
							<?php endforeach; ?>
							<p class="description">최신글 리스트에 추가로 보여줄 항목을 선택하세요.</p>
						</td>
					</tr> -->
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
							<?php if(KBOARD_CONNECT_COSMOSFARM):?>
							<a class="button button-small" href="<?php echo admin_url('admin.php?page=kboard_store&kbstore_category=kboard')?>" onclick="window.open(this.href);return false;">스킨 더보기</a>
							<?php endif?>
							<p class="description">게시판 스킨에 따라 모양과 기능이 변합니다.</p>
							<p class="description"><a href="https://blog.naver.com/PostView.nhn?blogId=chan2rrj&logNo=220885880601" onclick="window.open(this.href);return false;">contact-form 스킨 설정 방법 알아보기</a></p>
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
						<th scope="row"><label for="notice_invisible_comments">공지사항 글에 댓글 사용</label></th>
						<td>
							<select name="notice_invisible_comments" id="notice_invisible_comments">
								<option value="">활성화</option>
								<option value="1"<?php if($meta->notice_invisible_comments):?> selected<?php endif?>>비활성화</option>
							</select>
							<p class="description">공지사항 글에서 댓글을 사용할지 선택합니다. (KBoard 댓글 플러그인 사용)</p>
						</td>
					</tr>
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
								<?php foreach(kboard_content_editor_list() as $key=>$value):?>
								<option value="<?php echo esc_attr($key)?>"<?php if($board->use_editor == $key):?> selected<?php endif?>><?php echo esc_html($value)?></option>
								<?php endforeach?>
							</select>
							<p class="description">에디터를 사용하시면 내용에 이미지를 삽입, 링크 걸기 등 편리하게 게시글을 작성할 수 있습니다.</p>
							<p class="description">에디터가 제대로 동작하지 않는다면 사용하고 있는 테마를 바꾸거나 플러그인들을 비활성화한 다음 점검해보세요.</p>
							<p class="description">에디터가 깨질 때 고급설정 » <label for="editor_view_iframe" style="font-weight:bold" onclick="kboard_setting_tab_change(5);">글쓰기 아이프레임으로 보기</label> 기능을 사용해보세요.</p>
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
							<p class="description">리스트에서 게시글 번호를 내림차순 또는 오름차순으로 표시할 수 있습니다.</p>
							<p class="description">실제 게시글 정렬과는 무관하게 번호 표시만 바뀝니다.</p>
							<p class="description">번호 표시가 없는 스킨은 적용되지 않습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="list_default_sorting">게시글 기본 정렬 순서 설정하기</label></th>
						<td>
							<select name="list_default_sorting" id="list_default_sorting">
								<option value="">최신순</option>
								<option value="best"<?php if($board->meta->list_default_sorting == 'best'):?> selected<?php endif?>>추천순</option>
								<option value="viewed"<?php if($board->meta->list_default_sorting == 'viewed'):?> selected<?php endif?>>조회순</option>
								<option value="updated"<?php if($board->meta->list_default_sorting == 'updated'):?> selected<?php endif?>>업데이트순</option>
							</select>
							<p class="description">게시글 기본 정렬 순서를 설정합니다.</p>
							<p class="description">게시판 첫 화면 에서만 적용됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="list_sorting_range_select">기간 내 게시글만 표시하기(NEW)</label></th>
						<td>
							<select name="list_sorting_range_select" id="list_sorting_range_select">
								<option value="">-- 선택하세요 --</option>
								<option value="7" <?php selected($board->meta->list_sorting_range_select, '7'); ?>>최근 일주일</option>
								<option value="30" <?php selected($board->meta->list_sorting_range_select, '30'); ?>>최근 한달</option>
								<option value="365" <?php selected($board->meta->list_sorting_range_select, '365'); ?>>최근 1년</option>
								<option value="custom" <?php selected($board->meta->list_sorting_range_select, 'custom'); ?>>직접 설정</option>
							</select>

							<div id="custom-date-range" style="margin-top: 10px; <?php echo ($board->meta->list_sorting_range_select === 'custom') ? '' : 'display:none'; ?>">
								<label>
									<input type="date" name="list_sorting_start_date" id="list_sorting_start_date" value="<?php echo esc_attr($board->meta->list_sorting_start_date) ?>">
								</label>
								~
								<label>
									<input type="date" name="list_sorting_end_date" id="list_sorting_end_date" value="<?php echo esc_attr($board->meta->list_sorting_end_date) ?>">
								</label>
							</div>
							<p class="description">게시판 목록에 표시할 게시글의 기간을 선택할 수 있습니다.</p>
							<p class="description">예: '최근 7일'을 선택하면 최근 일주일 이내에 작성된 게시글만 목록에 표시됩니다.</p>
							<p class="description">기간을 설정하지 않으면 전체 게시글이 모두 표시됩니다.</p>
							<p class="description">※ 인기글 및 공지사항은 기간 설정과 관계없이 항상 상단에 표시됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="show_author_activity_menu">작성자 활동 보기(NEW)</label></th>
						<td>
							<select name="show_author_activity_menu" id="show_author_activity_menu">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->show_author_activity_menu):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description"> 활성화하면 게시글 목록에서 작성자 이름을 클릭 시 해당 사용자의 글을 볼 수 있는 메뉴가 표시됩니다.</p>
							<p class="description"> 작성자 이름을 클릭하면 작은 메뉴가 열립니다.</p>
							<p class="description"> 해당 메뉴를 통해 작성자의 다른 글이나 댓글을 빠르게 확인할 수 있습니다.</p>
							<p class="description"> 예: "작성 게시글 보기", 메뉴 표시</p>
							<p class="description"> <strong>탈퇴했거나 존재하지 않는 사용자</strong>는 메뉴를 클릭해도 동작하지 않습니다.</p>
							<p class="description">일부 스킨에서는 적용되지 않습니다.</p>
							<p class="description">활성화하신 후 적용이 되지 않는 스킨은 비활성화해주시기 바랍니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="use_notice_expiration">공지 사항 기간 만료 기능(NEW)</label></th>
						<td>
							<select name="use_notice_expiration" id="use_notice_expiration">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->use_notice_expiration):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">활성화하면 글 작성 시 공지사항 체크 시 만료 날짜 입력란이 표시됩니다.</p>
							<p class="description">입력한 만료 날짜가 지나면 자동으로 일반 글로 변경됩니다.</p>
							<p class="description">입력한 날짜 형식이 올바르지 않으면 만료 처리가 정상 작동하지 않을 수 있습니다.</p>
							<p class="description">만료 시간이 지나더라도 서버 작업 또는 페이지 새로고침 이후에 반영될 수 있습니다.</p>
							<p class="description">일부 스킨에서는 적용되지 않습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"></th>
						<td>
							<ul class="cosmosfarm-news-list">
								<?php
								$upgrader = KBUpgrader::getInstance();
								foreach($upgrader->getLatestNews() as $news_item):?>
								<li>
									<a href="<?php echo esc_url($news_item->url)?>" target="<?php echo esc_attr($news_item->target)?>" style="text-decoration:none"><?php echo esc_html($news_item->title)?></a>
								</li>
								<?php endforeach?>
							</ul>
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
							<a href="https://blog.cosmosfarm.com/?p=1141" onclick="window.open(this.href);return false;">워드프레스 사용자 역할과 권한 알아보기</a>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="permission_admin">최고관리자그룹</label></th>
						<td>
							<input type="hidden" name="permission_admin_roles" value="">
							<?php $admin_roles = $board->getAdminRoles();?>
							<?php foreach(get_editable_roles() as $key=>$value):?>
								<label><input type="checkbox" name="permission_admin_roles[]" value="<?php echo $key?>"<?php if($key=='administrator'):?> onclick="return false"<?php endif?><?php if($key=='administrator' || in_array($key, $admin_roles)):?> checked<?php endif?>><?php echo _x($value['name'], 'User role')?></label>
							<?php endforeach?>
							<p class="description"><code>글쓴이</code>는 실제 글쓴이를 지칭하는게 아니라 워드프레스 역할(Role) 명칭입니다.</p>
							<p class="description">역할(Role)은 레벨 혹은 등급이라고 말할 수 있습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="admin_user">선택된 관리자</label></th>
						<td>
							<input type="text" name="admin_user" id="admin_user" class="regular-text" value="<?php echo $board->admin_user?>">
							<p class="description">사용자명(아이디)을 입력하세요. 여러명을 입력하실 경우 콤마(,)로 구분됩니다.</p>
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
					<tr valign="top">
						<th scope="row"><label for="permission_comment_read">댓글보기권한</label></th>
						<td>
							<select name="permission_comment_read" id="permission_comment_read">
								<option value=""<?php if(!$meta->permission_comment_read):?> selected<?php endif?>>
									<?php echo kboard_permission('all')?>
								</option>
								<option value="author"<?php if($meta->permission_comment_read == 'author'):?> selected<?php endif?>>
									<?php echo kboard_permission('author')?>
								</option>
								<option value="comment_owner"<?php if($meta->permission_comment_read == 'comment_owner'):?> selected<?php endif?>>
									<?php echo kboard_permission('comment_owner')?>
								</option>
							</select>
							<p class="description">제한없음 일 때 비로그인 사용자는 <input type="number" style="width:60px" name="permission_comment_read_minute" value="<?php echo intval($board->meta->permission_comment_read_minute)?>" min="0" max="9999999">분 이후에 댓글을 볼 수 있도록 합니다.</p>
							<p class="description">시간이 0일 경우와 로그인 사용자는 댓글을 바로 볼 수 있습니다.</p>
							<p class="description">본인의 댓글만 보기로 세팅하시려면 <label for="permission_comment_write" style="font-weight:bold">댓글쓰기권한</label>을 로그인 사용자 이상으로 변경해주세요.</p>
							<p class="description">본인의 댓글만 보기는 다른 사람이 작성한 댓글을 볼 수 없으며 해당 게시글 작성자는 모든 댓글을 볼 수 있습니다.</p>
						</td>
					</tr>
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
							<p class="description">결제기능이 있는 스킨에 적용됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="permission_attachment_download">첨부파일 다운로드 권한</label></th>
						<td>
							<select name="permission_attachment_download" id="permission_attachment_download" onchange="kboard_permission_roles_view('.kboard-permission-attachment-download-roles-view', this.value)">
								<option value=""<?php if(!$meta->permission_attachment_download):?> selected<?php endif?>>
									<?php echo kboard_permission('all')?>
								</option>
								<option value="1"<?php if($meta->permission_attachment_download == '1'):?> selected<?php endif?>>
									<?php echo kboard_permission('author')?>
								</option>
								<option value="roles"<?php if($meta->permission_attachment_download == 'roles'):?> selected<?php endif?>>
									<?php echo kboard_permission('roles')?>
								</option>
							</select>
							<div class="kboard-permission-attachment-download-roles-view<?php if($meta->permission_attachment_download != 'roles'):?> kboard-hide<?php endif?>">
								<input type="hidden" name="permission_attachment_download_roles" value="">
								<?php $vote_roles = $board->getAttachmentDownloadRoles();?>
								<?php foreach(get_editable_roles() as $key=>$value):?>
									<label><input type="checkbox" name="permission_attachment_download_roles[]" value="<?php echo $key?>"<?php if($key=='administrator'):?> onclick="return false"<?php endif?><?php if($key=='administrator' || in_array($key, $vote_roles)):?> checked<?php endif?>><?php echo _x($value['name'], 'User role')?></label>
								<?php endforeach?>
							</div>
							<p class="description">게시글에 등록된 첨부파일 다운로드를 제한할 수 있습니다.</p>
							<p class="description">먼저 읽기권한이 있는 사용자만 다운로드가 가능합니다.</p>
							<p class="description">글 작성자 본인은 항상 다운로드할 수 있습니다.</p>
						</td>
					</tr>
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
							<div class="kboard-permission-vote-hide"><label><input type="checkbox" name="permission_vote_hide" value="1"<?php if($meta->permission_vote_hide):?> checked<?php endif?>>좋아요/싫어요 버튼 숨기기</label></div>
							<div class="kboard-permission-vote-roles-view<?php if($meta->permission_vote != 'roles'):?> kboard-hide<?php endif?>">
								<input type="hidden" name="permission_vote_roles" value="">
								<?php $vote_roles = $board->getVoteRoles();?>
								<?php foreach(get_editable_roles() as $key=>$value):?>
									<label><input type="checkbox" name="permission_vote_roles[]" value="<?php echo $key?>"<?php if($key=='administrator'):?> onclick="return false"<?php endif?><?php if($key=='administrator' || in_array($key, $vote_roles)):?> checked<?php endif?>><?php echo _x($value['name'], 'User role')?></label>
								<?php endforeach?>
							</div>
							<p class="description">게시판에서 좋아요, 싫어요 기능을 제한할 수 있습니다.</p>
							<p class="description">좋아요/싫어요 버튼 숨기기를 체크하면 게시판에서 좋아요, 싫어요 버튼을 안보이게 할 수 있습니다 .</p>
							<p class="description">스킨에 따라서 버튼이 숨겨지거나 그렇지 않을 수 있습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="permission_list">리스트 보기</label></th>
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
							<p class="description">본인의 글만 보기로 설정하면 관리자와의 1:1 게시판으로 운영이 가능합니다.</p>
							<p class="description">공지사항은 항상 표시됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="permit">게시글 관리자 승인</label></th>
						<td>
							<select name="permit" id="permit">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->permit):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">읽기/쓰기 권한과는 관계없이 관리자가 승인한 게시글만 정상적으로 보입니다. <a href="<?php echo admin_url('admin.php?page=kboard_content_list')?>" onclick="window.open(this.href);return false;">전체 게시글 관리</a></p>
							<p class="description">승인되지 않은 글은 제목과 내용이 숨김 처리되어 확인이 불가능하며 리스트에는 추가되어 작성자가 편집할 수 있습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="comment_permit">댓글 관리자 승인</label></th>
						<td>
							<select name="comment_permit" id="comment_permit">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->comment_permit):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">읽기/쓰기 권한과는 관계없이 관리자가 승인한 댓글만 정상적으로 보입니다. <a href="<?php echo admin_url('admin.php?page=kboard_comments_list')?>" target="__blank">전체 게시글 관리</a></p>
							<p class="description">승인되지 않은 댓글은 내용이 숨김 처리되어 확인이 불가능하며 리스트에는 추가되어 작성자가 편집할 수 있습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="secret_checked_default">비밀글 기본 체크</label></th>
						<td>
							<select name="secret_checked_default" id="secret_checked_default">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->secret_checked_default):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">새로운 글 작성 시 비밀글 체크박스를 기본적으로 체크해 보여줍니다.</p>
							<p class="description">작성자가 비밀글 체크박스를 해제할 수 있으며 강제 적용을 원하시면 <label for="secret_checked_forced" style="font-weight:bold">비밀글 강제 설정</label>을 사용해주세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="secret_checked_forced">비밀글 강제 설정</label></th>
						<td>
							<select name="secret_checked_forced" id="secret_checked_forced">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->secret_checked_forced):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">새로운 글 작성 시 강제로 비밀글 체크를 설정합니다.</p>
							<p class="description">관리자의 경우 적용되지 않습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="use_prevent_modify_delete">게시글 본인 수정 제한</label></th>
						<td>
							<select name="use_prevent_modify_delete" id="use_prevent_modify_delete">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->use_prevent_modify_delete):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">작성자 본인의 수정/삭제를 막을 때 사용해주세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="use_prevent_comment_modify_delete">댓글 본인 수정 제한</label></th>
						<td>
							<select name="use_prevent_comment_modify_delete" id="use_prevent_comment_modify_delete">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->use_prevent_comment_modify_delete):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">작성자 본인의 수정/삭제를 막을 때 사용해주세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="board_username_display_save">게시글 작성자명 저장 방법</label></th>
						<td>
							<select name="board_username_display_save" id="board_username_display_save">
								<option value="">공개적으로 보일 이름</option>
								<option value="name"<?php if($meta->board_username_display_save === 'name'):?> selected<?php endif?>>이름</option>
								<option value="email"<?php if($meta->board_username_display_save === 'email'):?> selected<?php endif?>>이메일</option>
							</select>
							<p class="description">게시글 작성자명 저장 방법을 설정합니다.</p>
							<p class="description">기존에 작성된 글은 적용되지 않습니다.</p>
							<p class="description">로그인한 사용자가 글 작성할 때만 적용됩니다.</p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><label for="comments_username_display_save">댓글 작성자명 저장 방법</label></th>
						<td>
							<select name="comments_username_display_save" id="comments_username_display_save">
								<option value="">공개적으로 보일 이름</option>
								<option value="name"<?php if($meta->comments_username_display_save === 'name'):?> selected<?php endif?>>이름</option>
								<option value="email"<?php if($meta->comments_username_display_save === 'email'):?> selected<?php endif?>>이메일</option>
							</select>
							<p class="description">댓글 작성자명 저장 방법을 설정합니다.</p>
							<p class="description">기존에 작성된 글은 적용되지 않습니다.</p>
							<p class="description">로그인한 사용자가 글 작성할 때만 적용됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="board_username_masking">작성자 이름 숨기기</label></th>
						<td>
							<select name="board_username_masking" id="board_username_masking">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->board_username_masking == '1'):?> selected<?php endif?>>활성화 (모두 적용)</option>
								<option value="2"<?php if($meta->board_username_masking == '2'):?> selected<?php endif?>>활성화 (관리자 제외)</option>
							</select>
							<p class="description">게시글 리스트,본문에서 작성자 이름을 숨길 수 있습니다.</p>
							<p class="description"><code>관리자 제외</code> 옵션을 선택하면 관리자는 이름을 그대로 볼 수 있습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="comments_username_masking">댓글 작성자 이름 숨기기</label></th>
						<td>
							<select name="comments_username_masking" id="comments_username_masking">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->comments_username_masking == '1'):?> selected<?php endif?>>활성화 (모두 적용)</option>
								<option value="2"<?php if($meta->comments_username_masking == '2'):?> selected<?php endif?>>활성화 (각자 적용)</option>
								<option value="3"<?php if($meta->comments_username_masking == '3'):?> selected<?php endif?>>활성화 (모두 적용 - 관리자 제외)</option>
								<option value="4"<?php if($meta->comments_username_masking == '4'):?> selected<?php endif?>>활성화 (각자 적용 - 관리자 제외)</option>
							</select>
							<p class="description">본문에서 댓글 작성자 이름을 숨길 수 있습니다.</p>
							<p class="description"><code>관리자 제외</code> 옵션을 선택하면 관리자는 이름을 그대로 볼 수 있습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="comments_anonymous">댓글 작성자 익명 설정</label></th>
						<td>
							<select name="comments_anonymous" id="comments_anonymous">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->comments_anonymous == '1'):?> selected<?php endif?>>활성화 (모두 적용)</option>
								<option value="2"<?php if($meta->comments_anonymous == '2'):?> selected<?php endif?>>활성화 (각자 적용)</option>
							</select>
							<p class="description">본문에서 댓글 작성자를 익명으로 만들 수 있습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="max_document_limit">최대 게시글 작성 제한</label></th>
						<td>
							<input type="number" name="max_document_limit" id="max_document_limit" value="<?php echo esc_attr($meta->max_document_limit)?>" placeholder="숫자만 입력 가능">개
							<p class="description">0 또는 공란 시 동작하지 않습니다.</p>
							<p class="description">게시판에서 동일 사용자가 작성 가능한 게시글 수를 제한합니다.</p>
							<p class="description">로그인 사용자에게만 적용되며 <label for="permission_write" style="font-weight:bold">쓰기권한</label>을 로그인 사용자 이상으로 해주세요.</p>
							<p class="description">비회원의 경우 적용되지 않기 때문의 주의가 필요합니다.</p>
							<p class="description">관리자의 경우 적용되지 않습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="new_document_delay">최소 시간 간격</label></th>
						<td>
							<input type="number" name="new_document_delay" id="new_document_delay" value="<?php echo esc_attr($meta->new_document_delay)?>" placeholder="숫자만 입력 가능">분
							<p class="description">0 또는 공란 시 동작하지 않습니다.</p>
							<p class="description">게시판에서 동일 사용자가 연속적으로 게시글을 작성하는 것을 방지하기 위해서 입력된 시간 이내로 새로운 게시글 작성을 제한합니다.</p>
							<p class="description">1분 단위로 숫자를 입력할 수 있으며 큰 값을 설정하면 그만큼 동일 사용자가 오랫동안 글을 남길 수 없게 됩니다.</p>
							<p class="description">로그인 사용자에게 적용하려면 <label for="permission_write" style="font-weight:bold">쓰기권한</label>을 로그인 사용자 이상으로 해주세요.</p>
							<p class="description">비회원에게 적용하려면 입력필드 설정에서 IP 주소 필드를 추가해주세요.</p>
							<p class="description">비회원의 경우 IP 주소 필드가 없다면 적용되지 않습니다.</p>
							<p class="description">관리자의 경우 적용되지 않습니다.</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php if($board->id):?>
		<div class="tab-kboard-setting">
			<?php $default_fields = $board->fields()->getDefaultFields()?>
			<div class="kboard-fields-wrap">
				<div class="kboard-fields-message">
					일부 스킨에서는 입력필드 설정이 적용되지 않습니다.
				</div>
				<div class="kboard-fields-left">
					<h3 class="kboard-fields-h3">사용 가능한 필드</h3>
					<ul class="kboard-fields">
						<li class="kboard-fields-default left">
							<button type="button" class="kboard-fields-header">
								KBoard 기본 필드
								<span class="fields-up">▲</span>
								<span class="fields-down">▼</span>
							</button>
							<ul class="kboard-fields-list kboard-fields-content">
								<?php foreach($default_fields as $key=>$item):?>
								<li class="default <?php echo $key?>">
									<input type="hidden" class="field_data class" value="<?php echo $item['class']?>">
									<input type="hidden" class="field_data close_button" value="<?php echo isset($item['close_button'])?$item['close_button']:''?>">
									<div class="kboard-extends-fields">
										<div class="kboard-fields-title toggle kboard-field-handle">
											<button type="button">
												<?php echo esc_html($item['field_label'])?>
												<span class="fields-up">▲</span>
												<span class="fields-down">▼</span>
											</button>
										</div>
										<div class="kboard-fields-toggle">
											<button type="button" class="fields-remove" title="<?php echo __('Remove', 'kboard')?>">X</button>
										</div>
									</div>
									<div class="kboard-fields-content">
										<input type="hidden" class="field_data field_type" value="<?php echo esc_attr($item['field_type'])?>">
										<input type="hidden" class="field_data field_label" value="<?php echo esc_attr($item['field_label'])?>">
										<?php if(isset($item['option_field'])):?>
											<input type="hidden" class="field_data option_field" value="<?php echo esc_attr($item['option_field'])?>">
										<?php endif?>
										<div class="attr-row">
											<label class="attr-name" for="<?php echo $key?>_field_label">필드 레이블</label>
											<div class="attr-value">
												<input type="text" id="<?php echo $key?>_field_label" class="field_data field_name" placeholder="<?php echo esc_attr($item['field_label'])?>">
											</div>
										</div>
										<?php if(isset($item['roles'])):?>
										<div class="attr-row">
											<label class="attr-name" for="<?php echo $key?>_roles">표시할 권한</label>
											<div class="attr-value">
												<select id="<?php echo $key?>_roles" class="field_data roles" onchange="kboard_fields_permission_roles_view(this)">
													<option value="all" selected>제한없음</option>
													<option value="author">로그인 사용자</option>
													<option value="roles">직접선택</option>
												</select>
												<div class="kboard-permission-read-roles-view kboard-hide">
													<?php foreach(get_editable_roles() as $roles_key=>$roles_value):?>
														<label><input type="checkbox" class="field_data roles_checkbox" value="<?php echo $roles_key?>"<?php if($roles_key=='administrator'):?> onclick="return false" checked<?php endif?>> <?php echo _x($roles_value['name'], 'User role')?></label>
													<?php endforeach?>
												</div>
											</div>
										</div>
										<?php endif?>
										<?php if(isset($item['show_document']) || isset($item['show_document_mode'])):?>
											<div class="attr-row">
												<label class="attr-name" for="<?php echo $key?>_show_document_mode">게시글 본문 표시하기</label>
												<div class="attr-value">
													<select id="<?php echo $key?>_show_document_mode" class="field_data show_document_mode" onchange="kboard_fields_permission_roles_view(this)">
														<option value="" <?php if(empty($item['show_document_mode']) || $item['show_document_mode'] == ''):?> selected<?php endif?>>안함</option>
														<option value="1" <?php if((isset($item['show_document']) && $item['show_document']) || $item['show_document_mode'] == '1'):?> selected<?php endif?>>전체 표시</option>
														<option value="roles" <?php if(isset($item['show_document_mode']) && $item['show_document_mode'] == 'roles'):?> selected<?php endif?>>직접 설정(역할)</option>
													</select>

													<div class="kboard-permission-read-roles-view<?php if(!isset($item['show_document_mode']) || $item['show_document_mode'] != 'roles'):?> kboard-hide<?php endif?>">
														<?php foreach(get_editable_roles() as $roles_key => $roles_value):?>
														<label>
															<input type="checkbox" class="field_data show_document_roles_checkbox" value="<?php echo $roles_key?>" <?php if($roles_key == 'administrator'):?>onclick="return false" checked<?php endif?>>
															<?php echo _x($roles_value['name'], 'User role')?>
														</label>
														<?php endforeach;?>
													</div>
												</div>
											</div>
										<?php endif?>
										<?php if(isset($item['secret_permission'])):?>
										<div class="attr-row">
											<label class="attr-name" for="<?php echo $key?>_secret">비밀글</label>
											<div class="attr-value">
												<select id="<?php echo $key?>_secret" class="field_data secret-roles" onchange="kboard_fields_permission_roles_view(this)">
													<option value="all">제한없음</option>
													<option value="author"<?php if($item['secret_permission'] == 'author'):?> selected<?php endif?>>로그인 사용자</option>
													<option value="roles"<?php if($item['secret_permission'] == 'roles'):?> selected<?php endif?>>직접선택</option>
												</select>
												<div class="kboard-permission-read-roles-view<?php if($item['secret_permission'] != 'roles'):?> kboard-hide<?php endif?>">
													<?php foreach(get_editable_roles() as $roles_key=>$roles_value):?>
														<label><input type="checkbox" class="field_data secret_checkbox" value="<?php echo $roles_key?>"<?php if($roles_key=='administrator'):?> onclick="return false" checked<?php endif?>> <?php echo _x($value['name'], 'User role')?></label>
													<?php endforeach?>
												</div>
											</div>
										</div>
										<?php endif?>
										<?php if(isset($item['notice_permission'])):?>
										<div class="attr-row">
											<label class="attr-name" for="<?php echo $key?>-notice">공지사항</label>
											<div class="attr-value">
												<select id="<?php echo $key?>-notice" class="field_data notice-roles" onchange="kboard_fields_permission_roles_view(this)">
													<option value="all">제한없음</option>
													<option value="author"<?php if($item['notice_permission'] == 'author'):?> selected<?php endif?>>로그인 사용자</option>
													<option value="roles"<?php if($item['notice_permission'] == 'roles'):?> selected<?php endif?>>직접선택</option>
												</select>
												<div class="kboard-permission-read-roles-view<?php if($item['notice_permission'] != 'roles'):?> kboard-hide<?php endif?>">
													<?php foreach(get_editable_roles() as $roles_key=>$roles_value):?>
														<label><input type="checkbox" class="field_data notice_checkbox" value="<?php echo $roles_key?>"<?php if($roles_key=='administrator'):?> onclick="return false"<?php endif?><?php if($roles_key=='administrator' || in_array($roles_key, $item['notice'])):?> checked<?php endif?>> <?php echo _x($roles_value['name'], 'User role')?></label>
													<?php endforeach?>
												</div>
											</div>
										</div>
										<?php endif?>
										<?php if(isset($item['default_value'])):?>
											<div class="attr-row">
												<label class="attr-name" for="<?php echo $key?>_default_value">기본값</label>
												<div class="attr-value">
												<?php if($item['field_type'] == 'search'):?>
													<select id="<?php echo $key?>_default_value" class="field_data default_value">
														<option value="1">제목과 내용 검색허용</option>
														<option value="2">제목만 검색허용 (비밀글)</option>
														<option value="3">통합검색 제외</option>
													</select>
												<?php elseif($item['field_type'] == 'category1'):?>
													<?php if($board->initCategory1()):?>
														<select id="<?php echo $key?>_default_value" class="field_data default_value">
															<option value=""><?php echo __('Category', 'kboard')?> <?php echo __('Select', 'kboard')?></option>
															<?php while($board->hasNextCategory()):?>
															<option value="<?php echo $board->currentCategory()?>"<?php if($item['default_value'] == $board->currentCategory()):?> selected<?php endif?>><?php echo $board->currentCategory()?></option>
															<?php endwhile?>
														</select>
													<?php endif?>
												<?php elseif($item['field_type'] == 'category2'):?>
													<?php if($board->initCategory2()):?>
														<select id="<?php echo $key?>_default_value" class="field_data default_value">
															<option value=""><?php echo __('Category', 'kboard')?> <?php echo __('Select', 'kboard')?></option>
															<?php while($board->hasNextCategory()):?>
															<option value="<?php echo $board->currentCategory()?>"<?php if($item['default_value'] == $board->currentCategory()):?> selected<?php endif?>><?php echo $board->currentCategory()?></option>
															<?php endwhile?>
														</select>
													<?php endif?>
												<?php else:?>
													<input type="text" class="field_data default_value">
												<?php endif?>
												</div>
											</div>
										<?php endif?>
										<?php if(isset($item['placeholder'])):?>
										<div class="attr-row">
											<label class="attr-name" for="<?php echo $key?>_placeholder">Placeholder</label>
											<div class="attr-value"><input type="text" id="<?php echo $key?>_placeholder" class="field_data placeholder" value="<?php if(isset($item['placeholder']) && $item['placeholder']):echo $item['placeholder']; endif?>"></div>
										</div>
										<?php endif?>
										<?php if(isset($item['description'])):?>
										<div class="attr-row">
											<label class="attr-name" for="<?php echo $key?>_description">설명</label>
											<div class="attr-value">
												<input type="text" id="<?php echo $key?>_description" class="field_data field_description" value="<?php echo $item['description']?>">
											</div>
										</div>
										<?php endif?>
										<?php if(isset($item['required']) || isset($item['show_document']) || isset($item['hidden'])):?>
											<div class="attr-row">
											<?php if(isset($item['required'])):?>
												<label>
													<input type="hidden" class="field_data required" value="">
													<input type="checkbox" class="field_data required" value="1"<?php if($item['required']):?> checked<?php endif?>>필수
												</label>
											<?php endif?>
											<?php if(isset($item['hidden'])):?>
												<label>
													<input type="hidden" class="field_data hidden" value="">
													<input type="checkbox" class="field_data hidden" value="1"<?php if($item['hidden']):?> checked<?php endif?>>숨김
												</label>
											<?php endif?>
											</div>
										<?php endif?>
									</div>
								</li>
								<?php endforeach?>
							</ul>
						</li>
						<li class="kboard-fields-extension left">
							<?php $extends_field = $board->fields()->getExtensionFields()?>
							<button type="button" class="kboard-fields-header">
								확장 필드
								<span class="fields-up">▲</span>
								<span class="fields-down">▼</span>
							</button>
							
							<ul class="kboard-fields-list kboard-fields-content">
							<?php if($extends_field):?>
								<?php foreach($extends_field as $key=>$item):?>
								<li class="extends <?php echo $key?>">
									<input type="hidden" value="<?php echo $item['class']?>" class="field_data class">
									<input type="hidden" class="field_data close_button" value="<?php echo isset($item['close_button'])?$item['close_button']:''?>">
									<div class="kboard-extends-fields">
										<div class="kboard-fields-title toggle kboard-field-handle">
											<button type="button">
												<?php echo esc_html($item['field_label'])?>
												<span class="fields-up">▲</span>
												<span class="fields-down">▼</span>
											</button>
										</div>
										<div class="kboard-fields-toggle">
											<button type="button" class="fields-remove" title="<?php echo __('Remove', 'kboard')?>">X</button>
										</div>
									</div>
									<div class="kboard-fields-content">
										<input type="hidden" class="field_data field_type" value="<?php echo esc_attr($item['field_type'])?>">
										<input type="hidden" class="field_data field_label" value="<?php echo esc_attr($item['field_label'])?>">
										<?php if($board->fields()->isMultiLineFields($item['field_type'])):?>
											<div class="attr-row">
												<label class="attr-name">필드 레이블</label>
												<div class="attr-value"><input type="text" class="field_data field_name" placeholder="<?php echo esc_attr($item['field_label'])?>"></div>
											</div>
											<?php if(isset($item['meta_key'])):?>
											<div class="attr-row">
												<label class="attr-name">메타키</label>
												<div class="attr-value"><input type="text" class="field_data meta_key" placeholder="meta_key"></div>
												<div class="description">※ 입력하지 않으면 자동으로 설정되며 저장 이후에는 값을 변경할 수 없습니다.</div>
											</div>
											<?php endif?>
											<div class="attr-row">
												<label class="attr-name"><?php echo $item['field_label']?></label>
												<div class="attr-value">
													<?php if($item['field_type'] == 'html'):?>
														<textarea class="field_data html" rows="5"></textarea>
													<?php elseif($item['field_type'] == 'shortcode'):?>
														<textarea class="field_data shortcode" rows="5"></textarea>
													<?php endif?>
												</div>
											</div>
										<?php else:?>
											<div class="attr-row">
												<label class="attr-name">필드 레이블</label>
												<div class="attr-value"><input type="text" class="field_data field_name" placeholder="<?php echo esc_attr($item['field_label'])?>"></div>
											</div>
											<?php if(isset($item['meta_key'])):?>
											<div class="attr-row">
												<label class="attr-name">메타키</label>
												<div class="attr-value"><input type="text" class="field_data meta_key" placeholder="meta_key"></div>
												<div class="description">※ 입력하지 않으면 자동으로 설정되며 저장 이후에는 값을 변경할 수 없습니다.</div>
											</div>
											<?php endif?>
											<?php if(isset($item['row'])):?>
												<?php $uniq_id = uniqid()?>
												<div class="kboard-radio-reset">
													<div class="attr-row option-wrap">
														<div class="attr-name option">
															<label for="<?php echo $uniq_id?>">라벨</label>
														</div>
														<div class="attr-value">
															<input type="text" id="<?php echo $uniq_id?>" class="field_data option_label">
															<button type="button" class="<?php echo $item['field_type']?>" onclick="add_option(this)">+</button>
															<button type="button" class="<?php echo $item['field_type']?>" onclick="remove_option(this)">-</button>
															<label>
																<?php if($item['field_type'] == 'checkbox'):?>
																<input type="checkbox" name="<?php echo $item['field_type']?>" class="field_data default_value" value="1">
																<?php else:?>
																<input type="radio" name="<?php echo $item['field_type']?>" class="field_data default_value" value="1">
																<?php endif?>
																기본값
															</label>
															<?php if($item['field_type'] == 'radio' || $item['field_type'] == 'select'):?>
																<span style="vertical-align:middle;cursor:pointer;" onclick="kboard_radio_reset(this)">· <?php echo __('Reset', 'kboard')?></span>
															<?php endif?>
														</div>
													</div>
												</div>
											<?php endif?>
											<?php if(isset($item['roles'])):?>
											<div class="attr-row">
												<label class="attr-name">표시할 권한</label>
												<div class="attr-value">
													<select class="field_data roles" onchange="kboard_fields_permission_roles_view(this)">
														<option value="all" selected>제한없음</option>
														<option value="author">로그인 사용자</option>
														<option value="roles">직접선택</option>
													</select>
													<div class="kboard-permission-read-roles-view kboard-hide">
														<?php foreach(get_editable_roles() as $roles_key=>$roles_value):?>
															<label><input type="checkbox" class="field_data roles_checkbox" value="<?php echo $roles_key?>"<?php if($roles_key=='administrator'):?> onclick="return false" checked<?php endif?>> <?php echo _x($roles_value['name'], 'User role')?></label>
														<?php endforeach?>
													</div>
												</div>
											</div>
											<?php endif?>
											<?php if(isset($item['show_document']) || isset($item['show_document_mode'])):?>
											<div class="attr-row">
												<label class="attr-name">게시글 본문 표시하기</label>
												<div class="attr-value">
													<select class="field_data show_document_mode" onchange="kboard_fields_permission_roles_view(this)">
														<option value="" <?php if(empty($item['show_document_mode']) || $item['show_document_mode'] == ''):?> selected<?php endif?>>안함</option>
														<option value="1" <?php if(isset($item['show_document_mode']) && $item['show_document_mode'] == '1'):?> selected<?php endif?>>전체 표시</option>
														<option value="roles" <?php if(isset($item['show_document_mode']) && $item['show_document_mode'] == 'roles'):?> selected<?php endif?>>직접 설정(역할)</option>
													</select>
													<div class="kboard-permission-read-roles-view<?php if(!isset($item['show_document_mode']) || $item['show_document_mode'] != 'roles'):?> kboard-hide<?php endif?>">
														<?php foreach(get_editable_roles() as $roles_key => $roles_value):?>
															<label>
																<input type="checkbox" class="field_data show_document_roles_checkbox" value="<?php echo $roles_key?>"<?php if($roles_key == 'administrator'):?> onclick="return false"<?php endif?><?php if($roles_key == 'administrator' || (isset($item['show_document_roles']) && in_array($roles_key, $item['show_document_roles']))):?> checked<?php endif?>> <?php echo _x($roles_value['name'], 'User role')?>
															</label>
														<?php endforeach?>
													</div>
												</div>
											</div>
											<?php endif?>
											<?php if(isset($item['default_value']) && !isset($item['row'])):?>
											<div class="attr-row">
												<label class="attr-name">기본값</label>
												<div class="attr-value"><input type="text" class="field_data default_value"></div>
											</div>
											<?php endif?>
											<?php if(isset($item['placeholder'])):?>
											<div class="attr-row">
												<label class="attr-name">Placeholder</label>
												<div class="attr-value"><input type="text" class="field_data placeholder"></div>
											</div>
											<?php endif?>
											<?php if(isset($item['description'])):?>
											<div class="attr-row">
												<label class="attr-name">설명</label>
												<div class="attr-value">
													<input type="text" class="field_data field_description" value="<?php echo $item['description']?>">
												</div>
											</div>
											<?php endif?>
											<?php if(isset($item['custom_class'])):?>
											<div class="attr-row">
												<label class="attr-name">CSS 클래스</label>
												<div class="attr-value"><input type="text" class="field_data custom_class"></div>
											</div>
											<?php endif?>
											<div class="attr-row">
												<?php if(isset($item['required'])):?>
												<input type="hidden" class="field_data required" value="">
												<label><input type="checkbox" class="field_data required" value="1">필수</label>
												<?php endif?>
												<?php if(isset($item['hidden'])):?>
												<input type="hidden" class="field_data hidden" value="">
												<label><input type="checkbox" class="field_data hidden" value="1">숨김<?php if($item['field_type'] == 'text'):?>(hidden)<?php endif?></label>
												<?php endif?>
											</div>
										<?php endif?>
									</div>
								</li>
								<?php endforeach?>
								<?php endif?>
							</ul>
						</li>
					</ul>
				</div>
				<div class="kboard-fields-right">
					<div class="kboard-fields kboard-sortable-fields">
						<h3 class="kboard-fields-h3">입력 필드 구조</h3>
						<div class="description">왼쪽 열에서 필드를 드래그 앤 드롭으로 추가하세요.</div>
						<ul class="kboard-skin-fields kboard-fields-sortable connected-sortable">
							<?php $fields = $board->fields()->getSkinFields()?>
							<?php foreach($fields as $key=>$item):?>
							<?php
							$meta_key = isset($item['meta_key']) && $item['meta_key'] ? $item['meta_key'] : $key;
							$field_label = $board->fields()->getFieldLabel($item);
							?>
							<li class="<?php echo $board->fields()->isDefaultFields($item['field_type'])?> <?php echo esc_attr($meta_key)?> <?php echo esc_attr($item['field_type'])?>">
								<input type="hidden" class="parent_id" value="<?php echo esc_attr($meta_key)?>">
								<input type="hidden" name="fields[<?php echo esc_attr($meta_key)?>][class]" class="field_data class" value="<?php echo $item['class']?>"> 
								<input type="hidden" name="fields[<?php echo esc_attr($meta_key)?>][close_button]" class="field_data close_button" value="<?php echo isset($item['close_button'])?$item['close_button']:''?>">
								<div class="kboard-saved-fields-header">
									<div class="kboard-fields-title toggle kboard-field-handle<?php if(!(isset($item['close_button']) && $item['close_button'] == 'yes')):?> only-toggle<?php endif?>">
										<button type="button">
											<?php echo esc_html($field_label)?>
											<?php if(isset($item['field_name']) && $item['field_name']):?>
											: <?php echo esc_html($item['field_name'])?>
											<?php endif?>
											<span class="fields-up">▲</span>
											<span class="fields-down">▼</span>
										</button>
									</div>
									<?php if(isset($item['close_button']) && $item['close_button'] == 'yes'):?>
									<div class="kboard-fields-toggle">
										<button type="button" class="fields-remove" title="<?php echo __('Remove', 'kboard')?>">X</button>
									</div>
									<?php endif?>
								</div>
								<div class="kboard-fields-content">
									<input type="hidden" name="fields[<?php echo esc_attr($meta_key)?>][field_type]" class="field_data field_type" value="<?php echo esc_attr($item['field_type'])?>">
									<input type="hidden" name="fields[<?php echo esc_attr($meta_key)?>][field_label]" class="field_data field_label" value="<?php echo esc_attr($field_label)?>">
									<?php if(isset($item['option_field'])):?>
										<input type="hidden" name="fields[<?php echo esc_attr($meta_key)?>][option_field]" class="field_data option_field" value="1">
									<?php endif?>
									<?php if(isset($item['hidden'])):?>
										<input type="text" name="fields[<?php echo esc_attr($meta_key)?>][hidden]" class="field_data hidden" value="1">
									<?php endif?>
									
									<?php if($item['field_type'] == 'title'):?>
									<div class="attr-row">
										<div class="description">※ 제목은 항상 필수로 입력해야 합니다.</div>
										<input type="hidden" name="fields[title][permission]" value="all">
									</div>
									<?php elseif(in_array($item['field_type'], array('category1', 'category2', 'tree_category'))):?>
									<div class="attr-row">
										<div class="description">※ 글 작성 화면에서 표시되지 않는다면 기본설정 페이지에서 카테고리 사용과 카테고리 선택 설정을 확인해보세요.</div>
									</div>
									<?php elseif($item['field_type'] == 'author'):?>
									<div class="attr-row">
										<div class="description">※ 비회원은 비밀번호를 항상 필수로 입력해야 합니다.</div>
									</div>
									<?php elseif($item['field_type'] == 'attach'):?>
									<div class="attr-row">
										<div class="description">※ 글 작성 화면에서 표시되지 않는다면 기본설정 페이지에서 최대 첨부파일 개수를 확인해보세요.</div>
									</div>
									<?php endif?>
									
									<?php if(isset($item['field_name'])):?>
									<div class="attr-row">
										<label class="attr-name" for="<?php echo esc_attr($meta_key)?>-field-label">필드 레이블</label>
										<div class="attr-value">
											<input type="text" id="<?php echo esc_attr($meta_key)?>-field-label" name="fields[<?php echo esc_attr($meta_key)?>][field_name]" class="field_data field_name" value="<?php echo esc_attr($item['field_name'])?>" placeholder="<?php echo esc_attr($field_label)?>">
										</div>
									</div>
									<?php endif?>
									
									<div class="attr-row">
										<label class="attr-name" for="<?php echo esc_attr($meta_key)?>">메타키</label>
										<div class="attr-value">
											<input type="text" name="fields[<?php echo esc_attr($meta_key)?>][meta_key]" id="<?php echo esc_attr($meta_key)?>" class="field_data meta_key" value="<?php echo $meta_key?>"<?php if($meta_key):?> readonly<?php endif?> placeholder="meta_key">
										</div>
										<div class="description">※ 입력하지 않으면 자동으로 설정되며 저장 이후에는 값을 변경할 수 없습니다.</div>
									</div>
									
									<?php if(isset($item['row'])):?>
										<?php if($board->fields()->valueExists($item['row'])):?>
											<div class="kboard-radio-reset">
											<?php $already_echo = false;?>
											<?php foreach($item['row'] as $option_key=>$option_value):?>
												<?php if(isset($option_value['label']) && $option_value['label']):?>
													<div class="attr-row option-wrap">
														<div class="attr-name option">
															<label for="<?php echo esc_attr($option_key)?>_label">라벨</label>
														</div>
														<div class="attr-value">
															<input type="text" id="<?php echo esc_attr($option_key)?>_label" name="fields[<?php echo esc_attr($meta_key)?>][row][<?php echo esc_attr($option_key)?>][label]" id="<?php echo esc_attr($meta_key)?>" class="field_data option_label" value="<?php echo esc_attr($option_value['label'])?>">
															<button type="button" class="<?php echo esc_attr($item['field_type'])?>" onclick="add_option(this)">+</button>
															<button type="button" class="<?php echo esc_attr($item['field_type'])?>" onclick="remove_option(this)">-</button>
															<label>
																<?php if($item['field_type'] == 'checkbox'):?>
																<input type="checkbox" name="fields[<?php echo esc_attr($meta_key)?>][row][<?php echo esc_attr($option_key)?>][default_value]" class="field_data default_value"<?php if(isset($option_value['default_value']) && $option_value['default_value'] == '1'):?> checked<?php endif?> value="1">
																<?php else:?>
																<input type="radio" name="fields[<?php echo esc_attr($meta_key)?>][default_value]" class="field_data default_value"<?php if(isset($item['default_value']) && $item['default_value']==$option_key):?> checked<?php endif?> value="<?php echo esc_attr($option_key)?>">
																<?php endif?>
																기본값
															</label>
															<?php if($item['field_type'] == 'radio' || $item['field_type'] == 'select'):?>
																<?php if(!$already_echo):?>
																<span style="vertical-align:middle;cursor:pointer;" onclick="kboard_radio_reset(this)">· <?php echo __('Reset', 'kboard')?></span>
																<?php $already_echo=true; endif?>
															<?php endif?>
														</div>
													</div>
												<?php endif?>
											<?php endforeach?>
											</div>
										<?php else:?>
											<?php $uniq_id = uniqid()?>
											<div class="attr-row option-wrap">
												<div class="attr-name option">
													<label for="<?php echo esc_attr($meta_key)?>_label">라벨</label>
												</div>
												<div class="attr-value">
													<input type="text" id="<?php echo esc_attr($meta_key)?>_label" name="fields[<?php echo esc_attr($meta_key)?>][row][<?php echo $uniq_id?>][label]" class="field_data option_label" value="">
													<button type="button" class="<?php echo esc_attr($item['field_type'])?>" onclick="add_option(this)">+</button>
													<button type="button" class="<?php echo esc_attr($item['field_type'])?>" onclick="remove_option(this)">-</button>
													<label>
													<?php if($item['field_type'] == 'checkbox'):?>
													<input type="checkbox" name="fields[<?php echo esc_attr($meta_key)?>][row][<?php echo $uniq_id?>][default_value]" class="field_data default_value" value="">
													<?php else:?>
													<input type="radio" name="fields[<?php echo esc_attr($meta_key)?>][default_value]" class="field_data default_value" value="">
													<?php endif?>
													기본값
													</label>
												</div>
											</div>
										<?php endif?>
									<?php endif?>
									<?php if(isset($item['permission']) && $item['field_type'] != 'title'):?>
									<div class="attr-row">
										<label class="attr-name" for="<?php echo esc_attr($meta_key)?>_permission">표시할 권한</label>
										<div class="attr-value">
											<?php if($item['field_type'] == 'author'):?>
											<select id="<?php echo esc_attr($meta_key)?>_permission" name="fields[<?php echo esc_attr($meta_key)?>][permission]" class="field_data roles">
												<option value="">비회원일때만 표시</option>
												<option value="always_visible"<?php if($item['permission'] == 'always_visible'):?> selected<?php endif?>>항상 표시</option>
												<option value="always_hide"<?php if($item['permission'] == 'always_hide'):?> selected<?php endif?>>항상 숨김</option>
											</select>
											<?php else:?>
											<select id="<?php echo esc_attr($meta_key)?>_permission" name="fields[<?php echo esc_attr($meta_key)?>][permission]" class="field_data roles" onchange="kboard_fields_permission_roles_view(this)">
												<option value="all"<?php if($item['permission'] == 'all'):?> selected<?php endif?>>제한없음</option>
												<option value="author"<?php if($item['permission'] == 'author'):?> selected<?php endif?>>로그인 사용자</option>
												<option value="roles"<?php if($item['permission'] == 'roles'):?> selected<?php endif?>>직접선택</option>
											</select>
											<div class="kboard-permission-read-roles-view<?php if($item['permission'] != 'roles'):?> kboard-hide<?php endif?>">
												<?php foreach(get_editable_roles() as $roles_key=>$roles_value):?>
													<label><input type="checkbox" name="fields[<?php echo esc_attr($meta_key)?>][roles][]" class="field_data" value="<?php echo $roles_key?>"<?php if($roles_key=='administrator'):?> onclick="return false"<?php endif?><?php if($roles_key=='administrator' || in_array($roles_key, $item['roles'])):?> checked<?php endif?>> <?php echo _x($roles_value['name'], 'User role')?></label>
												<?php endforeach?>
											</div>
											<?php endif?>
										</div>
									</div>
									<?php endif?>
									<?php if(!empty($item['show_document_mode']) || !empty($item['show_document']) || isset($item['show_document_mode']) || isset($item['show_document'])):?>
										<div class="attr-row">
											<label class="attr-name">게시글 본문 표시하기</label>
											<div class="attr-value">
												<select name="fields[<?php echo esc_attr($meta_key)?>][show_document_mode]" class="field_data show_document_mode" onchange="kboard_fields_permission_roles_view(this)">
													<option value="" <?php if(empty($item['show_document_mode']) || $item['show_document_mode'] == ''):?> selected<?php endif?>>안함</option>
													<option value="1" <?php if(isset($item['show_document_mode']) && $item['show_document_mode'] == '1'):?> selected<?php endif?>>전체 표시</option>
													<option value="roles" <?php if(isset($item['show_document_mode']) && $item['show_document_mode'] == 'roles'):?> selected<?php endif?>>직접 설정(역할)</option>
												</select>
												<div class="kboard-permission-read-roles-view<?php if(!isset($item['show_document_mode']) || $item['show_document_mode'] != 'roles'):?> kboard-hide<?php endif?>">
													<?php foreach(get_editable_roles() as $roles_key => $roles_value):?>
														<label>
															<input type="checkbox" name="fields[<?php echo esc_attr($meta_key)?>][show_document_roles][]" class="field_data show_document_roles_checkbox" value="<?php echo $roles_key?>"<?php if($roles_key == 'administrator'):?> onclick="return false"<?php endif?><?php if($roles_key == 'administrator' || (isset($item['show_document_roles']) && in_array($roles_key, $item['show_document_roles']))):?> checked<?php endif?>> <?php echo _x($roles_value['name'], 'User role')?>
														</label>
													<?php endforeach?>
												</div>
											</div>
										</div>
									<?php endif?>
									<?php if(isset($item['secret_permission'])):?>
									<div class="attr-row">
										<label class="attr-name" for="<?php echo esc_attr($meta_key)?>_secret">비밀글</label>
										<div class="attr-value">
											<select id="<?php echo esc_attr($meta_key)?>_secret" name="fields[option][secret_permission]" class="field_data roles" onchange="kboard_fields_permission_roles_view(this)">
												<option value="all"<?php if($item['secret_permission'] == 'all'):?> selected<?php endif?>>제한없음</option>
												<option value="author"<?php if($item['secret_permission'] == 'author'):?> selected<?php endif?>>로그인 사용자</option>
												<option value="roles"<?php if($item['secret_permission'] == 'roles'):?> selected<?php endif?>>직접선택</option>
											</select>
											<div class="kboard-permission-read-roles-view<?php if($item['secret_permission'] != 'roles'):?> kboard-hide<?php endif?>">
												<?php foreach(get_editable_roles() as $roles_key=>$roles_value):?>
													<label><input type="checkbox" name="fields[option][secret][]" class="field_data" value="<?php echo $roles_key?>"<?php if($roles_key=='administrator'):?> onclick="return false"<?php endif?><?php if($roles_key=='administrator' || in_array($roles_key, $item['secret'])):?> checked<?php endif?>> <?php echo _x($roles_value['name'], 'User role')?></label>
												<?php endforeach?>
											</div>
										</div>
									</div>
									<?php endif?>
									<?php if(isset($item['notice_permission'])):?>
									<div class="attr-row">
										<label class="attr-name" for="<?php echo esc_attr($meta_key)?>_notice">공지사항</label>
										<div class="attr-value">
											<select id="<?php echo esc_attr($meta_key)?>_notice" name="fields[option][notice_permission]" class="field_data roles" onchange="kboard_fields_permission_roles_view(this)">
												<option value="all"<?php if($item['notice_permission'] == 'all'):?> selected<?php endif?>>제한없음</option>
												<option value="author"<?php if($item['notice_permission'] == 'author'):?> selected<?php endif?>>로그인 사용자</option>
												<option value="roles"<?php if($item['notice_permission'] == 'roles'):?> selected<?php endif?>>직접선택</option>
											</select>
											<div class="kboard-permission-read-roles-view<?php if($item['notice_permission'] != 'roles'):?> kboard-hide<?php endif?>">
												<?php foreach(get_editable_roles() as $roles_key=>$roles_value):?>
													<label><input type="checkbox" name="fields[option][notice][]" class="field_data" value="<?php echo $roles_key?>"<?php if($roles_key=='administrator'):?> onclick="return false"<?php endif?><?php if($roles_key=='administrator' || in_array($roles_key, $item['notice'])):?> checked<?php endif?>> <?php echo _x($roles_value['name'], 'User role')?></label>
												<?php endforeach?>
											</div>
										</div>
									</div>
									<?php endif?>
									<?php if(isset($item['default_value']) && $item['field_type'] != 'checkbox' && $item['field_type'] != 'radio' && $item['field_type'] != 'select' && $item['field_type'] != 'ip'):?>
										<div class="attr-row">
											<label class="attr-name" for="<?php echo esc_attr($meta_key)?>_default_value">기본값</label>
											<div class="attr-value">
											<?php if($item['field_type'] == 'search'):?>
												<select id="<?php echo esc_attr($meta_key)?>_default_value" name="fields[search][default_value]" class="field_data default_value">
													<option value="1"<?php if($item['default_value'] == '1'):?> selected<?php endif?>>제목과 내용 검색허용</option>
													<option value="2"<?php if($item['default_value'] == '2'):?> selected<?php endif?>>제목만 검색허용 (비밀글)</option>
													<option value="3"<?php if($item['default_value'] == '3'):?> selected<?php endif?>>통합검색 제외</option>
												</select>
											<?php elseif($item['field_type'] == 'category1'):?>
												<?php if($board->initCategory1()):?>
													<select id="<?php echo esc_attr($meta_key)?>_default_value" name="fields[category1][default_value]" class="field_data default_value">
														<option value=""><?php echo __('Category', 'kboard')?> <?php echo __('Select', 'kboard')?></option>
														<?php while($board->hasNextCategory()):?>
														<option value="<?php echo $board->currentCategory()?>"<?php if($item['default_value'] == $board->currentCategory()):?> selected<?php endif?>><?php echo $board->currentCategory()?></option>
														<?php endwhile?>
													</select>
												<?php endif?>
											<?php elseif($item['field_type'] == 'category2'):?>
												<?php if($board->initCategory2()):?>
													<select id="<?php echo esc_attr($meta_key)?>_default_value" name="fields[category2][default_value]" class="field_data default_value">
														<option value=""><?php echo __('Category', 'kboard')?> <?php echo __('Select', 'kboard')?></option>
														<?php while($board->hasNextCategory()):?>
														<option value="<?php echo $board->currentCategory()?>"<?php if($item['default_value'] == $board->currentCategory()):?> selected<?php endif?>><?php echo $board->currentCategory()?></option>
														<?php endwhile?>
													</select>
												<?php endif?>
											<?php else:?>
												<input type="text" id="<?php echo esc_attr($meta_key)?>_default_value" name="fields[<?php echo esc_attr($meta_key)?>][default_value]" class="field_data default_value" value="<?php echo $item['default_value']?>">
											<?php endif?>
											</div>
										</div>
									<?php endif?>
									<?php if($board->fields()->isMultiLineFields($item['field_type'])):?>
										<div class="attr-row">
										<?php if($item['field_type'] == 'html'):?>
											<label class="attr-name" for="<?php echo esc_attr($meta_key)?>_html"><?php echo $item['field_label']?></label>
											<div class="attr-value">
												<textarea id="<?php echo esc_attr($meta_key)?>_html" name="fields[<?php echo esc_attr($meta_key)?>][html]" class="field_data html" rows="5"><?php echo esc_textarea($item['html'])?></textarea>
											</div>
										<?php elseif($item['field_type'] == 'shortcode'):?>
											<label class="attr-name" for="<?php echo esc_attr($meta_key)?>_shortcode"><?php echo $item['field_label']?></label>
											<div class="attr-value">
												<textarea id="<?php echo esc_attr($meta_key)?>_shortcode" name="fields[<?php echo esc_attr($meta_key)?>][shortcode]" class="field_data shortcode" rows="5"><?php echo $item['shortcode']?></textarea>
											</div>
										<?php endif?>
										</div>
									<?php endif?>
									<?php if(isset($item['placeholder'])):?>
									<div class="attr-row">
										<label class="attr-name" for="<?php echo esc_attr($meta_key)?>_placeholder">Placeholder</label>
										<div class="attr-value"><input type="text" id="<?php echo esc_attr($meta_key)?>_placeholder" name="fields[<?php echo esc_attr($meta_key)?>][placeholder]" class="field_data placeholder" value="<?php echo esc_attr($item['placeholder'])?>"></div>
									</div>
									<?php endif?>
									<?php if(isset($item['description'])):?>
									<div class="attr-row">
										<label class="attr-name" for="<?php echo esc_attr($meta_key)?>_description">설명</label>
										<div class="attr-value">
											<input type="text" id="<?php echo esc_attr($meta_key)?>_description" name="fields[<?php echo esc_attr($meta_key)?>][description]" class="field_data field_description" value="<?php echo esc_attr($item['description'])?>">
										</div>
									</div>
									<?php endif?>
									<?php if(isset($item['custom_class'])):?>
									<div class="attr-row">
										<label class="attr-name" for="<?php echo esc_attr($meta_key)?>_custom_class">CSS 클래스</label>
										<div class="attr-value"><input type="text" id="<?php echo esc_attr($meta_key)?>_custom_class" name="fields[<?php echo esc_attr($meta_key)?>][custom_class]" class="field_data custom_class" value="<?php echo esc_attr($item['custom_class'])?>"></div>
									</div>
									<?php endif?>
									<?php if(isset($item['show_document']) && !$board->fields()->isMultiLineFields($item['field_type'])):?>
									<div class="attr-row">
										<label class="attr-name">스킨 출력 예제</label>
										<div class="attr-value">
											<div class="example">
											<?php
											if($board->fields()->isDefaultFields($item['field_type']) == 'extends' || (isset($item['option_field']) && $item['option_field'])){
												if($item['field_type'] == 'file'){
													$print_code = '<?php echo $content->attach->{\'' . $meta_key . '\'}[1]?>';
												}
												else if($item['field_type'] == 'checkbox'){
													$print_code = '<?php echo implode(\', \', $content->option->{\'' . $meta_key . '\'})?>';
												}
												else{
													$print_code = '<?php echo $content->option->{\'' . $meta_key . '\'}?>';
												}
											}
											echo esc_html($print_code);
											?>
											</div>
										</div>
									</div>
									<?php endif?>
									<?php if(isset($item['required']) || isset($item['show_document']) || isset($item['hidden'])):?>
									<div class="attr-row">
										<?php if(isset($item['required'])):?>
											<label>
												<input type="hidden" name="fields[<?php echo esc_attr($meta_key)?>][required]" class="field_data required" value="">
												<input type="checkbox" name="fields[<?php echo esc_attr($meta_key)?>][required]" class="field_data required" value="1"<?php if($item['required']):?> checked<?php endif?>>필수
											</label>
										<?php endif?>
										<?php if(isset($item['hidden'])):?>
											<label>
												<input type="hidden" name="fields[<?php echo esc_attr($meta_key)?>][hidden]" class="field_data hidden" value="">
												<input type="checkbox" name="fields[<?php echo esc_attr($meta_key)?>][hidden]" class="field_data hidden" value="1"<?php if($item['hidden']):?> checked<?php endif?>>숨김<?php if($item['field_type'] == 'text'):?>(hidden)<?php endif?>
											</label>
										<?php endif?>
									</div>
									<?php endif?>
								</div>
							</li>
							<?php endforeach?>
						</ul>
						<div class="description">에러가 나거나 설정이 잘못됐다면 <button type="button" class="button button-small" onclick="kboard_skin_fields_reset()">초기화</button> 해주세요.</div>
					</div>
				</div>
			</div>
		</div>
		<div class="tab-kboard-setting">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="use_category">카테고리 사용</label></th>
						<td>
							<select name="use_category" id="use_category">
								<option value="">비활성화</option>
								<option value="yes"<?php if($board->use_category == 'yes'):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">카테고리를 사용해서 게시글을 분리할 수 있습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="use_tree_category">카테고리 선택</label></th>
						<td>
							<select name="use_tree_category" id="use_tree_category">
								<option value="">기본 카테고리 사용</option>
								<option value="yes"<?php if($board->meta->use_tree_category == 'yes'):?> selected<?php endif?>>계층형 카테고리 사용</option>
							</select>
							<a class="button button-small" href="#tab-kboard-setting-4" onclick="kboard_setting_tab_change(4);">계층형 카테고리 관리</a>
							<p class="description">기본 카테고리를 사용하시려면 아래의 <label for="category1_list" style="font-weight:bold">카테고리1</label>과 <label for="category2_list" style="font-weight:bold">카테고리2</label> 설정을 세팅해주세요.</p>
							<p class="description">계층형 카테고리를 선택하면 기본 카테고리는 사용이 중지됩니다.</p>
							<p class="description">계층형 카테고리가 적용되지 않는 일부 스킨에는 기본 카테고리를 사용해주세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="category1_list">카테고리1</label></th>
						<td>
							<input type="text" name="category1_list" id="category1_list" value="<?php echo $board->category1_list?>" class="regular-text" placeholder="예제 : 자유게시판,공지사항">
							<p class="description">특수문자는 사용할 수 없습니다. 여러 카테고리를 입력하실 경우 콤마(,)로 구분됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="category2_list">카테고리2</label></th>
						<td>
							<input type="text" name="category2_list" id="category2_list" value="<?php echo $board->category2_list?>" class="regular-text" placeholder="예제 : 자유게시판,공지사항">
							<p class="description">특수문자는 사용할 수 없습니다. 여러 카테고리를 입력하실 경우 콤마(,)로 구분됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="category3_list">카테고리3</label></th>
						<td>
							<input type="text" name="category3_list" id="category3_list" value="<?php echo $board->category3_list?>" class="regular-text" placeholder="예제 : 자유게시판,공지사항">
							<p class="description">특수문자는 사용할 수 없습니다. 여러 카테고리를 입력하실 경우 콤마(,)로 구분됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="category4_list">카테고리4</label></th>
						<td>
							<input type="text" name="category4_list" id="category4_list" value="<?php echo $board->category4_list?>" class="regular-text" placeholder="예제 : 자유게시판,공지사항">
							<p class="description">특수문자는 사용할 수 없습니다. 여러 카테고리를 입력하실 경우 콤마(,)로 구분됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="category5_list">카테고리5</label></th>
						<td>
							<input type="text" name="category5_list" id="category5_list" value="<?php echo $board->category5_list?>" class="regular-text" placeholder="예제 : 자유게시판,공지사항">
							<p class="description">특수문자는 사용할 수 없습니다. 여러 카테고리를 입력하실 경우 콤마(,)로 구분됩니다.</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="tab-kboard-setting">
			<div class="kboard-tree-category-wrap">
				<div class="col-left kboard-category-setting-left">
					<div class="col-wrap">
						<div class="form-wrap">
							<div class="kboard-update-tree-category">
								<h2>카테고리 수정</h2>
								<div class="form-field form-required term-name-wrap">
									<label for="update-category-name">수정할 카테고리</label>
									<input type="text" id="update-category-name" class="update_category_name" name="update_category_name">
									<input type="hidden" id="current-category-name" class="update_category_name" name="current_category_name">
									<input type="hidden" id="category-id" name="category_id" value="">
									<input type="hidden" id="parent-id" name="parent_id" value="">
								</div>
							</div>
							
							<div class="kboard-update-tree-category btn">
								<button type="button" class="button" onclick="kboard_tree_category_update('kboard_tree_category_update')">이름 변경</button>
								<button type="button" class="button" onclick="kboard_tree_category_update('kboard_tree_category_remove')">삭제</button>
							</div>
							
							<div class="kboard-new-tree-category">
								<h2>새 카테고리 추가</h2>
								<div class="form-field form-required term-name-wrap">
									<label for="new-category-name">이름</label>
									<input type="text" id="new-category-name" name="new_category">
									<input type="hidden" id="new-parent-id">
								</div>
							</div>
							
							<div class="kboard-new-tree-category-btn">
								<button type="button" class="button-primary" onclick="kboard_tree_category_update('kboard_tree_category_create')">새 카테고리 추가</button>
							</div>
						</div>
					</div>
				</div>
				
				<div class="kboard-category-setting-right">
					<div class="kboard-category-setting-sortable">
					<h2>카테고리 구조</h2>
					<ul class="sortable">
						<?php echo $board->tree_category->buildAdminTreeCategorySortableRow($board->tree_category->buildAdminTreeCategory())?>
					</ul>
					</div>
				</div>
				<p class="description"><strong>계층형 카테고리란?</strong></p>
				<p class="description">카테고리를 <u>1단계 → 2단계 → 3단계</u>처럼 계층 구조로 구성할 수 있습니다. 예를 들어, <code>고객센터 &gt; 회원문의 &gt; 로그인 오류</code>처럼 세분화된 카테고리 구성이 가능합니다.</p>
				<p class="description"><strong>사용 안내:</strong></p>
				<p class="description">- 왼쪽 입력창에서 <strong>새 카테고리 추가</strong> 및 <strong>이름 변경</strong>이 가능합니다.</p>
				<p class="description">- 오른쪽의 카테고리 구조는 <strong>드래그 앤 드롭</strong>으로 정렬하거나 상하위 관계를 변경할 수 있습니다.</p>
				<p class="description">- <strong>기본 카테고리 입력란(category1~5)은 비활성화되며</strong>, 계층형 카테고리만 사용됩니다.</p>
				<p class="description"><strong>주의사항:</strong></p>
				<p class="description">계층형 카테고리는 일부 커스터마이징 스킨에서는 지원되지 않을 수 있습니다. 이 경우 <strong>기본 카테고리</strong>를 사용해주세요.</p>
				<p class="description"><strong>※</strong> 이 기능은 기본 카테고리 탭에서 <strong>“카테고리 선택”을 계층형 카테고리로 설정</strong>해야만 사용 가능합니다.</p>
			</div>
		</div>
		<div class="tab-kboard-setting">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"></th>
						<td>
							궁금한 것은 <a href="https://www.cosmosfarm.com/threads" onclick="window.open(this.href);return false;">KBoard 커뮤니티</a>에서 검색하고 질문해보세요.
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="shortcode_execute">게시글 숏코드(Shortcode) 실행</label></th>
						<td>
							<select name="shortcode_execute" id="shortcode_execute">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->shortcode_execute):?> selected<?php endif?>>활성화</option>
							</select>
							<div>
								<label><input type="checkbox" name="shortcode_execute_only_admin" value="1"<?php if($meta->shortcode_execute_only_admin):?> checked<?php endif?>> 게시판 관리자의 글에만 실행</label>
							</div>
							<p class="description">게시글 본문에 글쓴이가 입력한 워드프레스 숏코드를 실행합니다.</p>
							<p class="description"><a href="https://wordpress.org/documentation/article/audio-shortcode/" onclick="window.open(this.href);return false;">audio</a>, <a href="https://wordpress.org/documentation/article/video-shortcode/" onclick="window.open(this.href);return false;">video</a> 등의 숏코드를 사용할 수 있습니다.</p>
							<p class="description">관리자가 아닌 사용자가 워드프레스 내장 기능을 사용할 수 있어 보안에 주의해야 합니다. <a href="https://blog.naver.com/PostView.nhn?blogId=chan2rrj&logNo=50179426321" onclick="window.open(this.href);return false;">알아보기</a></p>
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
							<code><a href="<?php echo home_url("?kboard_id={$board->id}")?>" onclick="window.open(this.href);return false;"><?php echo home_url("?kboard_id={$board->id}")?></a></code>
							<?php endif?>
							<p class="description">고유주소는 독립적 레이아웃 편집, 아이프레임 삽입, 다른 사이트와 연결 등 고급 사용자를 위한 편의 기능입니다.</p>
							<p class="description">일반 사용자는 자동설치 또는 숏코드(Shortcode)를 사용해 게시판을 생성하세요.</p>
							<p class="description"><label for="editor_view_iframe" style="font-weight:bold">글쓰기 아이프레임으로 보기</label> 기능과 충돌할 수 있으니 해당 기능을 비활성화 해주세요.</p>
						</td>
                    </tr>
					<tr valign="top">
						<th scope="row"><label for="pass_autop">특정 테마 레이아웃 깨짐 방지</label></th>
						<td>
							<select name="pass_autop" id="pass_autop">
								<option value="disable"<?php if($meta->pass_autop == 'disable'):?> selected<?php endif?>>비활성화</option>
								<option value="enable"<?php if($meta->pass_autop == 'enable'):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">문제가 없다면 활성화 하지 마세요.</p>
							<p class="description">특정 테마에서 content에 자동으로 P태그가 추가되어 레이아웃이 깨지는 현상이 발생됩니다.</p>
							<p class="description">활성화시 content에 P태그가 추가되기 전에 게시판을 출력시킵니다. <a href="https://blog.naver.com/PostView.nhn?blogId=chan2rrj&logNo=50178536050" onclick="window.open(this.href);return false;">알아보기</a></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="view_iframe">아이프레임으로 보기</label></th>
						<td>
							<select name="view_iframe" id="view_iframe">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->view_iframe):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">문제가 없다면 활성화 하지 마세요.</p>
							<p class="description">원페이지 테마 또는 게시판이 심하게 깨질 때 아이프레임으로 보기를 사용해주세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="editor_view_iframe">글쓰기 아이프레임으로 보기</label></th>
						<td>
							<select name="editor_view_iframe" id="editor_view_iframe">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->editor_view_iframe):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">문제가 없다면 활성화 하지 마세요.</p>
							<p class="description">글쓰기 화면 또는 워드프레스 내장 에디터가 깨질 때 사용해주세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="conversion_tracking_code">전환추적 코드</label></th>
						<td>
							<textarea name="conversion_tracking_code" id="conversion_tracking_code" style="width:600px;max-width:100%;height:100px;"><?php echo $meta->conversion_tracking_code?></textarea>
							<p class="description">게시글 등록 전환추적을 위한 코드(HTML 태그 또는 자바스크립트 소스)를 입력해주세요.</p>
							<p class="description">이 코드가 존재하면 새로운 게시글이 저장된 직후 실행됩니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="default_build_mod">게시판 기본 화면</label></th>
						<td>
							<select name="default_build_mod" id="default_build_mod">
								<option value="">글목록 화면</option>
								<option value="editor"<?php if($meta->default_build_mod == 'editor'):?> selected<?php endif?>>글쓰기 화면</option>
							</select>
							<p class="description">게시판에서 첫 번째로 보일 화면을 정합니다.</p>
							<p class="description">별다른 이유가 없다면 글목록 화면으로 선택해주세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="after_executing_mod">글 쓴 후 이동 화면</label></th>
						<td>
							<select name="after_executing_mod" id="after_executing_mod">
								<option value="">작성된 글 화면</option>
								<option value="list"<?php if($meta->after_executing_mod == 'list'):?> selected<?php endif?>>글목록 화면</option>
								<option value="editor"<?php if($meta->after_executing_mod == 'editor'):?> selected<?php endif?>>글쓰기 화면</option>
							</select>
							<p class="description">글쓰기를 완료하고 보일 화면을 정합니다.</p>
							<p class="description">보통의 경우라면 작성된 글 화면으로 이동해주세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="woocommerce_product_tabs_add">우커머스 상품 탭에 표시</label></th>
						<td>
							<select name="woocommerce_product_tabs_add" id="woocommerce_product_tabs_add">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->woocommerce_product_tabs_add):?> selected<?php endif?>>활성화</option>
							</select>
							<div>
								<label>탭 표시 순서 <input type="number" name="woocommerce_product_tabs_priority" value="<?php echo intval($meta->woocommerce_product_tabs_priority)?>"></label>
							</div>
							<p class="description">우커머스 상품 탭에 게시판을 표시합니다.</p>
							<p class="description">탭 표시 순서 값을 변경해서 표시 위치를 조절할 수 있습니다. (0~1000 숫자 입력)</p>
							<p class="description">일부 스킨에서는 적용되지 않습니다.</p>
							<p class="description">직접 코드를 작성해서 우커머스와 연결하시려면 <a href="https://blog.naver.com/PostView.nhn?blogId=chan2rrj&logNo=220986923814" onclick="window.open(this.href);return false;">우커머스 상품에 KBoard 게시판 연결하기</a>를 참고해주세요.</p>
						</td>
						<tr valign="top">
							<th scope="row"><label for="prevent_copy">복사 방지 스크립트 실행</label></th>
							<td>
								<select name="prevent_copy" id="prevent_copy">
									<option value="">전체 설정에 따라 적용</option>
									<option value="1"<?php if($meta->prevent_copy == '1'):?> selected<?php endif?>>복사 방지</option>
									<option value="2"<?php if($meta->prevent_copy == '2'):?> selected<?php endif?>>드래그, 우클릭 방지</option>
									<option value="3"<?php if($meta->prevent_copy == '3'):?> selected<?php endif?>>드래그, 우클릭, 복사 방지</option>
								</select>
								<p class="description">Kboard가 있는 페이지에서 복사 방지 스크립트를 실행합니다.</p>
								<p class="description">Kboard 동작하는 페이지 전체에 적용됩니다.</p>
								<p class="description">관리자를 제외한 나머지 모두에게 적용됩니다.</p>
								<p class="description">일부 서버 환경에서는 동작하지 않을 수 있습니다.</p>
							</td>
						</tr>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="except_count_type">게시글 표시 수 제외 옵션</label></th>
						<td>
							<select name="except_count_type" id="except_count_type">
								<option value="">기본 설정</option>
								<option value="1"<?php if($meta->except_count_type == '1'):?> selected<?php endif?>>답글 제외</option>
								<option value="2"<?php if($meta->except_count_type == '2'):?> selected<?php endif?>>공지사항 제외</option>
								<option value="3"<?php if($meta->except_count_type == '3'):?> selected<?php endif?>>답변, 공지사항 제외</option>
								<option value="4"<?php if($meta->except_count_type == '4'):?> selected<?php endif?>>글 제목 키워드 설정 제외</option>
							</select>
							<div>
								<label>제외 할 키워드<input type="text" name="except_count_type_keyword" value="<?php echo $meta->except_count_type_keyword?>"></label>
							</div>
							<p class="description">게시글 수 제외 옵션입니다.</p>
							<p class="description">설정한 옵션이 게시판 리스트 전체글 수 에서 제외 됩니다.</p>
							<p class="description">글 제목 키워드 설정 제외 옵션 선택시 제외 할 키워드를 입력 안하시면 게시판에 등록된 전체 게시글 숫자가 표시됩니다.</p>
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
							<p>
								먼저 <a href="https://sidetalk.kr/" target="_blank">사이드톡 대시보드</a>에 사이트를 등록하고 API 키를 발급받아야 자동응답 기능을 사용할 수 있습니다.
							</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="sidetalk_ai_enable">AI 자동답변</label> <span style="font-size:12px;color:red;">(필수)</span></th>
						<td>
							<select name="sidetalk_ai_enable" id="sidetalk_ai_enable">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->sidetalk_ai_enable):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">사이드톡 API를 이용해 게시글 또는 댓글에 AI 자동답변을 생성합니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="sidetalk_ai_reply_user_id">AI 자동답변 작성 계정 ID</label> <span style="font-size:12px;color:red;">(필수)</span></th>
						<td>
							<input type="number" name="sidetalk_ai_reply_user_id" id="sidetalk_ai_reply_user_id" class="regular-text" value="<?php echo esc_attr($meta->sidetalk_ai_reply_user_id) ?>" min="1" step="1">
							<p class="description">
								WordPress 사용자 ID는 AI 자동답변을 생성할 때 사용할 계정입니다.<br>
								<strong>입력하지 않으면 자동답변이 생성되지 않습니다.</strong><br>
								예: <code>1</code>은 기본 관리자 계정일 수 있습니다. 별도 AI 전용 계정을 생성해 ID를 입력하는 것을 권장합니다.
							</p>
							<p class="description"><a href="https://www.notion.so/cosmosfarm/Kboard-WordPress-ID-1f3c7b1cfe21803fa1f5cb1374dcfa8b" target="_blank"> WordPress 사용자 ID 확인 방법</a></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="sidetalk_api_key">사이드톡 API 키</label> <span style="font-size:12px;color:red;">(필수)</span></th>
						<td>
							<input type="text" name="sidetalk_api_key" id="sidetalk_api_key" class="regular-text" value="<?php echo esc_attr($meta->sidetalk_api_key) ?>">
							<p class="description">사이드톡 대시보드에서 발급받은 API 키를 입력하세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="sidetalk_ai_target">AI 자동답변을 적용할 대상</label> <span style="font-size:12px;color:gray;">(선택)</span></th>
						<td>
							<select name="sidetalk_ai_target" id="sidetalk_ai_target">
								<option value="post"<?php if($meta->sidetalk_ai_target == 'post'):?> selected<?php endif?>>게시글만</option>
								<option value="comment"<?php if($meta->sidetalk_ai_target == 'comment'):?> selected<?php endif?>>댓글만</option>
								<option value="all"<?php if($meta->sidetalk_ai_target == 'all'):?> selected<?php endif?>>게시글 + 댓글 모두</option>
							</select>
							<p class="description">어떤 유형의 글에 자동으로 AI 답변을 생성할지 선택하세요.</p>
						</td>
					</tr>
					<tr id="sidetalk_ai_post_reply_mode_row" valign="top" style="display:none;">
						<th scope="row"><label for="sidetalk_ai_post_reply_mode">게시글의 답변 달기 방식</label> <span style="font-size:12px;color:gray;">(선택)</span></th>
						<td>
							<select name="sidetalk_ai_post_reply_mode" id="sidetalk_ai_post_reply_mode">
								<option value="comment"<?php if ($meta->sidetalk_ai_post_reply_mode === 'comment'): ?> selected<?php endif; ?>>댓글로 달기</option>
								<option value="reply"<?php if ($meta->sidetalk_ai_post_reply_mode === 'reply'): ?> selected<?php endif; ?>>답글로 달기</option>
							</select>
							<p class="description">게시글에 대한 AI 자동답변을 어떤 형태로 작성할지 선택하세요.<br>
							<strong>댓글로 달기:</strong> 게시글 아래 일반 댓글로 AI 답변이 작성됩니다.<br>
							<strong>답글로 달기:</strong> 게시글에 대한 하위 글(답글)로 AI가 새 글을 작성합니다.</p>
						</td>
					</tr>
					<!-- <tr valign="top">
						<th scope="row"><label for="sidetalk_filter_keywords">필터 키워드</label></th>
						<td>
							<input type="text" name="sidetalk_filter_keywords" id="sidetalk_filter_keywords" class="regular-text" value="<?php echo esc_attr($meta->sidetalk_filter_keywords) ?>">
							<p class="description">쉼표(,)로 구분. 포함 시 AI 응답을 생성하지 않습니다. 예: [AI답변금지], [NOAI]</p>
						</td>
					</tr> -->
					<tr valign="top">
						<th scope="row"><label for="sidetalk_ai_reply_title">자동답변 제목</label> <span style="font-size:12px;color:gray;">(선택)</span></th>
						<td>
							<input type="text" name="sidetalk_ai_reply_title" id="sidetalk_ai_reply_title" class="regular-text" value="<?php echo esc_attr($meta->sidetalk_ai_reply_title) ?>">
							<p class="description">자동생성되는 답변의 제목입니다. 예: <code>AI 자동 답변</code></p>
							<p class="description">댓글에는 제목이 없어 이 기능이 적용되지 않을 수 있습니다.</p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><label for="sidetalk_ai_reply_author">자동답변 작성자 이름</label> <span style="font-size:12px;color:gray;">(선택)</span></th>
						<td>
							<input type="text" name="sidetalk_ai_reply_author" id="sidetalk_ai_reply_author" class="regular-text" value="<?php echo esc_attr($meta->sidetalk_ai_reply_author) ?>">
							<p class="description">AI 답변 작성자 이름으로 표시할 이름입니다. 예: <code>사이드톡 AI</code></p>
						</td>
					</tr>
					<!-- <tr valign="top">
						<th scope="row">AI 댓글 스타일</th>
						<td>
							<p class="description">자동 생성된 댓글에는 <code>ai-reply</code> 클래스가 추가됩니다.<br>CSS에서 이 클래스를 활용해 별도 스타일을 지정할 수 있습니다.</p>
						</td>
					</tr> -->
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
							<p class="description">새로운 글을 쓰면 작성자의 포인트를 차감합니다.</p>
							<p class="description">작성자는 포인트가 있어야 글을 쓸 수 있습니다.</p>
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
							<p class="description">게시판에 글을 읽으면 사용자의 포인트를 차감합니다.</p>
							<p class="description">사용자는 포인트가 있어야 글을 읽을 수 있습니다.</p>
							<p class="description">처음 읽을 때만 포인트가 차감됩니다.</p>
						</td>
					</tr>
					<!-- 첨부파일 다운로드 감소 포인트 -->
					<tr valign="top">
						<th scope="row"><label for="attachment_download_down_point"><?php echo __('Attachment download decrease points', 'kboard')?></label></th>
						<td>
							<input type="number" name="attachment_download_down_point" id="attachment_download_down_point" value="<?php echo $meta->attachment_download_down_point?>" placeholder="<?php echo __('Please enter only numbers', 'kboard')?>">
							<p class="description">첨부파일 다운로드시 사용자의 포인트를 차감합니다.</p>
							<p class="description">사용자는 포인트가 있어야 첨부파일을 다운로드할 수 있습니다.</p>
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
							<p class="description">새로운 댓글을 쓰면 작성자의 포인트를 차감합니다.</p>
							<p class="description">작성자는 포인트가 있어야 댓글을 쓸 수 있습니다.</p>
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
							<select id="kboard_csv_download_option">
								<option value="">입력 필드 데이터 제외 (기본)</option>
								<option value="1">본문 표시 필드만 포함</option>
								<option value="2">모든 입력 필드 포함</option>
							</select>
							<br>
							<input type="button" class="button-primary" value="<?php echo __('Download', 'kboard')?>" onclick="const option = document.getElementById('kboard_csv_download_option').value; const url = '<?php echo wp_nonce_url(add_query_arg(array('action'=>'kboard_csv_download_execute', 'board_id'=>$board->id), admin_url('admin-post.php')), 'kboard-csv-download-execute', 'kboard-csv-download-execute-nonce')?>'; window.location.href = url + '&kboard_csv_download_option=' + encodeURIComponent(option);">
							<p class="description"><strong>옵션 설명:</strong></p>
							<ul style="margin: 4px 0 8px 20px; padding-left: 0; list-style: disc;">
								<li><strong>입력 필드 데이터 제외 (기본)</strong>: 제목, 작성자, 날짜 등 기본 게시글 정보만 포함됩니다.</li>
								<li><strong>본문 표시 필드만 포함</strong>: '게시글 본문에 표시' 체크된 입력 필드의 값만 게시글 내용에 병합됩니다.</li>
								<li><strong>모든 입력 필드 포함</strong>: 입력된 모든 필드의 값이 게시글 내용에 포함되어 CSV에 기록됩니다.</li>
							</ul>
							<p class="description">대략 <?php echo number_format($board->getTotal())?>개의 게시글 정보를 다운로드합니다. (휴지통에 있는 게시글이 포함됩니다.)</p>
							<p class="description">게시글 양이 많다면 웹호스팅의 트래픽 사용량이 높아지니 주의해주세요.</p>
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
							<p class="description">CSV 파일의 인코딩을 UTF-8로 변경해서 시도해보세요.<br>너무 많은 데이터를 한 번에 업로드하게 되면 에러가 발생될 수 있으니 가급적 나눠서 여러 번 업로드해주세요.<br>댓글과 첨부파일은 등록되지 않습니다.</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="tab-kboard-setting">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="popular_action">인기글 사용 설정</label></th>
						<td>
							<select name="popular_action" id="popular_action">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->popular_action):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">게시판 리스트에 인기글이 표시됩니다.</p>
							<p class="description">일부 스킨에는 적용이 안될 수 있습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="popular_type">표시 종류</label></th>
						<td>
							<select name="popular_type" id="popular_type">
								<option value="">— 선택하기 —</option>
								<option value="view"<?php if($meta->popular_type == 'view'):?> selected<?php endif?>>조회수</option>
								<option value="vote"<?php if($meta->popular_type == 'vote'):?> selected<?php endif?>>추천수</option>
							</select>
							<p class="description">선택한 종류를 기준으로 가장 높은 순서의 게시글을 표시합니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="popular_count">표시 개수</label></th>
						<td>
							<select name="popular_count" id="popular_count">
								<?php if(!$meta->popular_count) $meta->popular_count=5;?>
								<option value="10"<?php if($meta->popular_count == 10):?> selected<?php endif?>>10개</option>
								<option value="9"<?php if($meta->popular_count == 9):?> selected<?php endif?>>9개</option>
								<option value="8"<?php if($meta->popular_count == 8):?> selected<?php endif?>>8개</option>
								<option value="7"<?php if($meta->popular_count == 7):?> selected<?php endif?>>7개</option>
								<option value="6"<?php if($meta->popular_count == 6):?> selected<?php endif?>>6개</option>
								<option value="5"<?php if($meta->popular_count == 5):?> selected<?php endif?>>5개</option>
								<option value="4"<?php if($meta->popular_count == 4):?> selected<?php endif?>>4개</option>
								<option value="3"<?php if($meta->popular_count == 3):?> selected<?php endif?>>3개</option>
								<option value="2"<?php if($meta->popular_count == 2):?> selected<?php endif?>>2개</option>
								<option value="1"<?php if($meta->popular_count == 1):?> selected<?php endif?>>1개</option>
							</select>
							<p class="description">리스트에 보여지는 인기글 개수를 정합니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="popular_range">표시 기간</label></th>
						<td>
							<select name="popular_range" id="popular_range">
								<?php if(!$meta->popular_range) $meta->popular_range='all';?>
								<option value="all"<?php if($meta->popular_range == 'all'):?> selected<?php endif?>>전체 인기글</option>
								<option value="week"<?php if($meta->popular_range == 'week'):?> selected<?php endif?>>이주의 인기글</option>
								<option value="month"<?php if($meta->popular_range == 'month'):?> selected<?php endif?>>이달의 인기글</option>
							</select>
							<p class="description">해당 기간 내의 인기글을 표시할 수 있습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="popular_name">표시 이름</label></th>
						<td>
							<input type="text" name="popular_name" id="popular_name" class="regular-text" value="<?php echo esc_attr($meta->popular_name)?>" placeholder="인기글">
							<p class="description">리스트에 표시되는 인기글 이름을 변경합니다.</p>
							<p class="description">리스트에서 게시글 번호 대신 이름이 표시됩니다.</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="tab-kboard-setting">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="latest_alerts">최신글 이메일 알림</label></th>
						<td>
							<input type="text" name="latest_alerts" id="latest_alerts" value="<?php echo $meta->latest_alerts?>" class="regular-text" placeholder="예제 : <?php echo get_bloginfo('admin_email')?>">
							<p class="description">최신글이 등록되면 입력된 이메일로 알려드립니다.</p>
							<p class="description">여러명을 입력하실 경우 콤마(,)로 구분됩니다.</p>
							<p class="description">서버 환경에 따라서 이메일이 전송되지 못 할 수도 있습니다.</p>
							<p class="description">이메일 전송에 문제가 있다면 <a href="https://blog.cosmosfarm.com/?p=720" onclick="window.open(this.href);return false;">워드프레스 이메일 전송 문제 해결 방법</a>을 참고해주세요.</p>
							<p class="description"><a href="https://www.cosmosfarm.com/wpstore/product/cosmosfarm-telebot" onclick="window.open(this.href);return false;">코스모스팜 텔레봇</a> 플러그인을 사용하시면 최신글 알림을 <span style="font-weight:bold">텔레그램</span> 메신저로 받아보실 수 있습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="latest_alerts_attachments_size"></label></th>
						<td>
							<select name="latest_alerts_attachments_size" id="latest_alerts_attachments_size">
								<option value="">첨부파일 제외</option>
								<?php for($i=1; $i<=100; $i++):?>
									<option value="<?php echo $i?>"<?php if($i == $meta->latest_alerts_attachments_size):?> selected<?php endif?>><?php echo $i?> MB 이하 첨부파일 포함</option>
								<?php endfor?>
							</select>
							<p class="description"><label for="latest_alerts" style="font-weight:bold">최신글 이메일 알림</label>에 첨부파일을 포함해서 전송할 수 있습니다.</p>
							<p class="description">안전한 전송을 위해서 설정한 용량보다 작은 파일만 포함해서 이메일을 전송합니다.</p>
							<p class="description">이메일을 보내는 쪽 서버 또는 받는 쪽 서버에서 첨부파일 허용 용량에 제한이 있다면 에러가 날 수도 있습니다.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="comment_alerts">댓글 이메일 알림(NEW)</label></th>
						<td>
							<select name="comment_alerts" id="comment_alerts">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->comment_alerts):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">댓글 등록되면 게시글 본문 작성자 이메일로 알려드립니다.</p>
							<p class="description">댓글 작성자의 이메일과 대댓글 작성자의 이메일에는 적용되지 않습니다.</p>
							<p class="description">로그인하지 않았거나 로그인 상태이지만 사용자 정보에 이메일이 없는 경우에는 이메일이 전송되지 못할 수도 있습니다.</p>
							<p class="description">서버 환경에 따라서 이메일이 전송되지 못 할 수도 있습니다.</p>
							<p class="description">이메일 전송에 문제가 있다면 <a href="https://blog.cosmosfarm.com/?p=720" onclick="window.open(this.href);return false;">워드프레스 이메일 전송 문제 해결 방법</a>을 참고해주세요.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="exclude_admin_reply_alert">관리자 답글 알림 제외(NEW)</label></th>
						<td>
							<select name="exclude_admin_reply_alert" id="exclude_admin_reply_alert">
								<option value="">비활성화</option>
								<option value="1"<?php if($meta->exclude_admin_reply_alert):?> selected<?php endif?>>활성화</option>
							</select>
							<p class="description">
								이메일 알림 기능을 사용하는 경우, 관리자 계정이 답글을 작성했을 때도 알림이 전송됩니다.<br>
								이 옵션을 <strong>"활성화"</strong>하면 <strong>관리자가 답글을 작성할 때는 이메일 알림이 전송되지 않습니다.</strong><br>
								일반 사용자가 답글을 작성할 경우에는 기존대로 알림이 정상적으로 전송됩니다.
							</p>
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
							KBoard는 직접 확장 플러그인 개발이 가능하며 추가된 게시판 기능을 이곳에 표시 할 수 있습니다. <a href="https://www.cosmosfarm.com/products/kboard/hooks" onclick="window.open(this.href);return false;">더보기</a>
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
jQuery(document).ready(function(){
	jQuery('.kboard-copy-text').each(function(){
		jQuery(this).click(function(){
			kboard_copy_text(jQuery(this).val());
			alert('복사되었습니다.');
		});
	});
});

function kboard_copy_text(string){
	function handler(event){
		event.clipboardData.setData('text/plain', string);
		event.preventDefault();
		document.removeEventListener('copy', handler, true);
	}
	document.addEventListener('copy', handler, true);
	document.execCommand('copy');
}
</script>