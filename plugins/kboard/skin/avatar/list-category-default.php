<div class="kboard-category category-mobile">
	<form id="kboard-category-form-<?php echo $board->id?>" method="get" action="<?php echo $url->toString()?>">
		<?php echo $url->set('pageid', '1')->set('category1', '')->set('category2', '')->set('target', '')->set('keyword', '')->set('mod', 'list')->toInput()?>
		
		<?php if($board->initCategory1()):?>
			<select name="category1" onchange="jQuery('#kboard-category-form-<?php echo $board->id?>').submit();">
				<option value=""><?php echo __('All', 'kboard')?></option>
				<?php while($board->hasNextCategory()):?>
				<option value="<?php echo $board->currentCategory()?>"<?php if(kboard_category1() == $board->currentCategory()):?> selected<?php endif?>><?php echo $board->currentCategory()?></option>
				<?php endwhile?>
			</select>
		<?php endif?>
		
		<?php if($board->initCategory2()):?>
			<select name="category2" onchange="jQuery('#kboard-category-form-<?php echo $board->id?>').submit();">
				<option value=""><?php echo __('All', 'kboard')?></option>
				<?php while($board->hasNextCategory()):?>
				<option value="<?php echo $board->currentCategory()?>"<?php if(kboard_category2() == $board->currentCategory()):?> selected<?php endif?>><?php echo $board->currentCategory()?></option>
				<?php endwhile?>
			</select>
		<?php endif?>
	</form>
</div>

<div class="kboard-category category-pc">
	<?php if($board->initCategory1()):?>
		<ul class="kboard-category-list">
			<li<?php if(!kboard_category1()):?> class="kboard-category-selected"<?php endif?>><a href="<?php echo $url->set('category1', '')->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->tostring()?>"><?php echo __('All', 'kboard')?></a></li>
			<?php while($board->hasNextCategory()):?>
			<li<?php if(kboard_category1() == $board->currentCategory()):?> class="kboard-category-selected"<?php endif?>>
				<a href="<?php echo $url->set('category1', $board->currentCategory())->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->toString()?>"><?php echo $board->currentCategory()?></a>
			</li>
			<?php endwhile?>
		</ul>
	<?php endif?>
	
	<?php if($board->initCategory2()):?>
		<ul class="kboard-category-list">
			<li<?php if(!kboard_category2()):?> class="kboard-category-selected"<?php endif?>><a href="<?php echo $url->set('category2', '')->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->tostring()?>"><?php echo __('All', 'kboard')?></a></li>
			<?php while($board->hasNextCategory()):?>
			<li<?php if(kboard_category2() == $board->currentCategory()):?> class="kboard-category-selected"<?php endif?>>
				<a href="<?php echo $url->set('category2', $board->currentCategory())->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->toString()?>"><?php echo $board->currentCategory()?></a>
			</li>
			<?php endwhile?>
		</ul>
	<?php endif?>
</div>