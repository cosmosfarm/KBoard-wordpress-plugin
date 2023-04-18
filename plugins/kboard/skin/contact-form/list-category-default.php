<div class="kboard-category category-mobile">
	<form id="kboard-category-form-<?php echo $board->id?>" method="get" action="<?php echo esc_url($url->toString())?>">
		<?php echo $url->set('pageid', '1')->set('category1', '')->set('category2', '')->set('category3', '')->set('category4', '')->set('category5', '')->set('target', '')->set('keyword', '')->set('mod', 'list')->toInput()?>
		
		<?php if($board->initCategory1()):?>
			<select name="category1" onchange="jQuery('#kboard-category-form-<?php echo $board->id?>').submit();">
				<option value=""><?php echo __('All', 'kboard')?></option>
				<?php while($board->hasNextCategory()):?>
				<option value="<?php echo esc_attr($board->currentCategory())?>"<?php if(kboard_category1() == $board->currentCategory()):?> selected<?php endif?>><?php echo esc_html($board->currentCategory())?></option>
				<?php endwhile?>
			</select>
		<?php endif?>
		
		<?php if($board->initCategory2()):?>
			<select name="category2" onchange="jQuery('#kboard-category-form-<?php echo $board->id?>').submit();">
				<option value=""><?php echo __('All', 'kboard')?></option>
				<?php while($board->hasNextCategory()):?>
				<option value="<?php echo esc_attr($board->currentCategory())?>"<?php if(kboard_category2() == $board->currentCategory()):?> selected<?php endif?>><?php echo esc_html($board->currentCategory())?></option>
				<?php endwhile?>
			</select>
		<?php endif?>
		
		<?php if($board->initCategory3()):?>
			<select name="category3" onchange="jQuery('#kboard-category-form-<?php echo $board->id?>').submit();">
				<option value=""><?php echo __('All', 'kboard')?></option>
				<?php while($board->hasNextCategory()):?>
				<option value="<?php echo esc_attr($board->currentCategory())?>"<?php if(kboard_category3() == $board->currentCategory()):?> selected<?php endif?>><?php echo esc_html($board->currentCategory())?></option>
				<?php endwhile?>
			</select>
		<?php endif?>
		
		<?php if($board->initCategory4()):?>
			<select name="category4" onchange="jQuery('#kboard-category-form-<?php echo $board->id?>').submit();">
				<option value=""><?php echo __('All', 'kboard')?></option>
				<?php while($board->hasNextCategory()):?>
				<option value="<?php echo esc_attr($board->currentCategory())?>"<?php if(kboard_category4() == $board->currentCategory()):?> selected<?php endif?>><?php echo esc_html($board->currentCategory())?></option>
				<?php endwhile?>
			</select>
		<?php endif?>
		
		<?php if($board->initCategory5()):?>
			<select name="category5" onchange="jQuery('#kboard-category-form-<?php echo $board->id?>').submit();">
				<option value=""><?php echo __('All', 'kboard')?></option>
				<?php while($board->hasNextCategory()):?>
				<option value="<?php echo esc_attr($board->currentCategory())?>"<?php if(kboard_category5() == $board->currentCategory()):?> selected<?php endif?>><?php echo esc_html($board->currentCategory())?></option>
				<?php endwhile?>
			</select>
		<?php endif?>
	</form>
</div>

<div class="kboard-category category-pc">
	<?php if($board->initCategory1()):?>
		<ul class="kboard-category-list">
			<li<?php if(!kboard_category1()):?> class="kboard-category-selected"<?php endif?>><a href="<?php echo esc_url($url->set('category1', '')->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->tostring())?>"><?php echo __('All', 'kboard')?></a></li>
			<?php while($board->hasNextCategory()):?>
			<li<?php if(kboard_category1() == $board->currentCategory()):?> class="kboard-category-selected"<?php endif?>>
				<a href="<?php echo esc_url($url->set('category1', $board->currentCategory())->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->toString())?>"><?php echo esc_html($board->currentCategory())?></a>
			</li>
			<?php endwhile?>
		</ul>
	<?php endif?>
	
	<?php if($board->initCategory2()):?>
		<ul class="kboard-category-list">
			<li<?php if(!kboard_category2()):?> class="kboard-category-selected"<?php endif?>><a href="<?php echo esc_url($url->set('category2', '')->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->tostring())?>"><?php echo __('All', 'kboard')?></a></li>
			<?php while($board->hasNextCategory()):?>
			<li<?php if(kboard_category2() == $board->currentCategory()):?> class="kboard-category-selected"<?php endif?>>
				<a href="<?php echo esc_url($url->set('category2', $board->currentCategory())->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->toString())?>"><?php echo esc_html($board->currentCategory())?></a>
			</li>
			<?php endwhile?>
		</ul>
	<?php endif?>
	
	<?php if($board->initCategory3()):?>
		<ul class="kboard-category-list">
			<li<?php if(!kboard_category3()):?> class="kboard-category-selected"<?php endif?>><a href="<?php echo esc_url($url->set('category3', '')->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->tostring())?>"><?php echo __('All', 'kboard')?></a></li>
			<?php while($board->hasNextCategory()):?>
			<li<?php if(kboard_category3() == $board->currentCategory()):?> class="kboard-category-selected"<?php endif?>>
				<a href="<?php echo esc_url($url->set('category3', $board->currentCategory())->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->toString())?>"><?php echo esc_html($board->currentCategory())?></a>
			</li>
			<?php endwhile?>
		</ul>
	<?php endif?>
	
	<?php if($board->initCategory4()):?>
		<ul class="kboard-category-list">
			<li<?php if(!kboard_category4()):?> class="kboard-category-selected"<?php endif?>><a href="<?php echo esc_url($url->set('category4', '')->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->tostring())?>"><?php echo __('All', 'kboard')?></a></li>
			<?php while($board->hasNextCategory()):?>
			<li<?php if(kboard_category4() == $board->currentCategory()):?> class="kboard-category-selected"<?php endif?>>
				<a href="<?php echo esc_url($url->set('category4', $board->currentCategory())->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->toString())?>"><?php echo esc_html($board->currentCategory())?></a>
			</li>
			<?php endwhile?>
		</ul>
	<?php endif?>
	
	<?php if($board->initCategory5()):?>
		<ul class="kboard-category-list">
			<li<?php if(!kboard_category5()):?> class="kboard-category-selected"<?php endif?>><a href="<?php echo esc_url($url->set('category5', '')->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->tostring())?>"><?php echo __('All', 'kboard')?></a></li>
			<?php while($board->hasNextCategory()):?>
			<li<?php if(kboard_category5() == $board->currentCategory()):?> class="kboard-category-selected"<?php endif?>>
				<a href="<?php echo esc_url($url->set('category5', $board->currentCategory())->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->toString())?>"><?php echo esc_html($board->currentCategory())?></a>
			</li>
			<?php endwhile?>
		</ul>
	<?php endif?>
</div>