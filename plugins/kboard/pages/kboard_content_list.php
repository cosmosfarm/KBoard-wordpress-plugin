<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h2>
		전체 게시글
		<a href="http://www.cosmosfarm.com/products/kboard" class="add-new-h2" onclick="window.open(this.href); return false;">홈페이지</a>
		<a href="http://www.cosmosfarm.com/threads" class="add-new-h2" onclick="window.open(this.href); return false;">커뮤니티</a>
		<a href="http://www.cosmosfarm.com/support" class="add-new-h2" onclick="window.open(this.href); return false;">고객지원</a>
	</h2>
	<form method="get">
		<input type="hidden" name="page" value="kboard_content_list">
		<?php $table->search_box('검색', 'kboard_content_list_search')?>
	</form>
	<form method="post">
		<?php $table->display()?>
	</form>
</div>