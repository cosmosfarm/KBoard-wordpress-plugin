<div class="kboard-tree-category-search">
	<form id="kboard-tree-category-search-form-<?php echo $board->id?>" method="get" action="<?php echo $url->toString()?>">
		<?php echo $url->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->toInput()?>
		
		<div class="kboard-tree-category-wrap">
			<?php $tree_category_list = $board->tree_category->getCategoryItemList()?>
			<div class="kboard-search-option-wrap-<?php echo $board->tree_category->depth?> kboard-search-option-wrap type-tab">
				<input type="hidden" name="kboard_search_option[tree_category_<?php echo $board->tree_category->depth?>][key]" value="tree_category_<?php echo $board->tree_category->depth?>">
				<input type="hidden" name="kboard_search_option[tree_category_<?php echo $board->tree_category->depth?>][value]" value="<?php echo $board->tree_category->getCategoryNameWithDepth($board->tree_category->depth)?>">
				<ul class="kboard-tree-category">
					<li<?php if(!$board->tree_category->getCategoryNameWithDepth($board->tree_category->depth)):?> class="kboard-category-selected"<?php endif?>><a href="#" onclick="return kboard_tree_category_search('<?php echo $board->tree_category->depth?>', '')"><?php echo __('All', 'kboard')?></a></li>
					<?php foreach($tree_category_list as $item):?>
					<li<?php if($board->tree_category->getCategoryNameWithDepth($board->tree_category->depth) == $item['category_name']):?> class="kboard-category-selected"<?php endif?>><a href="#" onclick="return kboard_tree_category_search('<?php echo $board->tree_category->depth?>', '<?php echo $item['category_name']?>')"><?php echo $item['category_name']?></a></li>
					<?php endforeach?>
				</ul>
			</div>
			
			<?php foreach($board->tree_category->getSelectedList() as $key=>$category_name):?>
				<?php $tree_category_list = $board->tree_category->getCategoryItemList($category_name)?>
				<?php if($tree_category_list):?>
				<div class="kboard-search-option-wrap-<?php echo $board->tree_category->depth?> kboard-search-option-wrap type-tab">
					<input type="hidden" name="kboard_search_option[tree_category_<?php echo $board->tree_category->depth?>][key]" value="tree_category_<?php echo $board->tree_category->depth?>">
					<input type="hidden" name="kboard_search_option[tree_category_<?php echo $board->tree_category->depth?>][value]" value="<?php echo $board->tree_category->getCategoryNameWithDepth($board->tree_category->depth)?>">
					<ul class="kboard-tree-category">
						<li<?php if(!$board->tree_category->getCategoryNameWithDepth($board->tree_category->depth)):?> class="kboard-category-selected"<?php endif?>><a href="#" onclick="return kboard_tree_category_search('<?php echo $board->tree_category->depth?>', '')"><?php echo __('All', 'kboard')?></a></li>
						<?php foreach($tree_category_list as $item):?>
						<li<?php if($board->tree_category->getCategoryNameWithDepth($board->tree_category->depth) == $item['category_name']):?> class="kboard-category-selected"<?php endif?>><a href="#" onclick="return kboard_tree_category_search('<?php echo $board->tree_category->depth?>', '<?php echo $item['category_name']?>')"><?php echo $item['category_name']?></a></li>
						<?php endforeach?>
					</ul>
				</div>
				<?php endif?>
			<?php endforeach?>
		</div>
	</form>
</div>