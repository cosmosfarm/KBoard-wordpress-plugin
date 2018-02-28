<!-- 계층형(Tab) 카테고리 시작 -->
<div class="kboard-tree-category-search">
	<form id="kboard-tree-category-search-form-<?php echo $board->id?>" method="get" action="<?php echo $url->toString()?>">
		<?php echo $url->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->toInput()?>
		<div class="kboard-tree-category-wrap">
			<?php $board->tree_category->buildTreeCategoryRoot()?>
		</div>
	</form>
</div>
<!-- 계층형(Tab) 카테고리 끝 -->