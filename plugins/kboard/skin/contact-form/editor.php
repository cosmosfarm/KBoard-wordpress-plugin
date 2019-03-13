<div id="kboard-contact-form-editor">
	<form class="kboard-form" method="post" action="<?php echo $url->getContentEditorExecute()?>" enctype="multipart/form-data" onsubmit="return kboard_editor_execute(this);">
		<?php $skin->editorHeader($content, $board)?>
		
		<input type="hidden" name="secret" value="true">
		<input type="hidden" name="wordpress_search" value="3">
		
		<?php foreach($board->fields()->getSkinFields() as $key=>$field):?>
			<?php echo $board->fields()->getTemplate($field, $content, $boardBuilder)?>
		<?php endforeach?>
		
		<div class="kboard-control">
			<div class="left">
				<?php if($content->uid):?>
				<a href="<?php echo $url->getDocumentURLWithUID($content->uid)?>" class="kboard-contact-form-button-small"><?php echo __('Back', 'kboard')?></a>
				<a href="<?php echo $url->set('mod', 'list')->toString()?>" class="kboard-contact-form-button-small"><?php echo __('List', 'kboard')?></a>
				<?php elseif($board->isWriter()):?>
				<button type="submit" class="kboard-contact-form-button-large"><?php echo __('Send', 'kboard')?></button>
				<?php endif?>
			</div>
			<div class="right">
				<?php if($content->uid && $board->isWriter()):?>
				<button type="submit" class="kboard-contact-form-button-small"><?php echo __('Save', 'kboard')?></button>
				<?php endif?>
			</div>
		</div>
	</form>
	
	<?php if($board->contribution()):?>
	<div class="kboard-contact-form-poweredby">
		<a href="https://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href);return false;" title="<?php echo __('KBoard is the best community software available for WordPress', 'kboard')?>">Powered by KBoard</a>
	</div>
	<?php endif?>
</div>

<?php if(kboard_execute_uid()):?>
<script>alert('<?php echo __('Your message was sent successfully. Thanks.', 'kboard')?>');</script>
<?php endif?>

<?php wp_enqueue_script('kboard-contact-form-script', "{$skin_path}/script.js", array(), KBOARD_VERSION, true)?>