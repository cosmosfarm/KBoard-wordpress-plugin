<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1>
		<?php echo __('KBoard : 백업 및 복구', 'kboard')?>
		<a href="https://www.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
		<a href="https://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
		<a href="https://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
		<a href="https://blog.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
	</h1>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"></th>
				<td>
					웹호스팅의 하드와 데이터베이스 전체 백업기능이 있다면 먼저 웹호스팅의 백업 기능을 사용해보세요.
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for=""><?php echo __('백업파일 다운로드', 'kboard')?></label></th>
				<td>
					<form action="<?php echo admin_url('admin-post.php')?>" method="post">
						<?php wp_nonce_field('kboard-backup-download', 'kboard-backup-download-nonce');?>
						<input type="hidden" name="action" value="kboard_backup_download">
						<input type="submit" class="button-primary" value="<?php echo __('백업파일 다운로드', 'kboard')?>">
						<p class="description"><?php echo __('백업파일을 다운로드 받습니다. 파일은 xml 파일이며 복구하기를 통해 백업된 상태로 되돌릴 수 있습니다.', 'kboard')?></p>
					</form>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for=""><?php echo __('백업파일 선택', 'kboard')?></label></th>
				<td>
					<form action="<?php echo admin_url('admin-post.php')?>" method="post" enctype="multipart/form-data">
						<?php wp_nonce_field('kboard-restore-execute', 'kboard-restore-execute-nonce');?>
						<input type="hidden" name="action" value="kboard_restore_execute">
						<input type="file" name="kboard_backup_xml_file" accept=".xml">
						<br>
						<input type="submit" class="button-primary" value="<?php echo __('복구하기', 'kboard')?>">
						<p class="description"><?php echo __('xml 파일을 선택하고 복구하기 버튼을 누르세요. 지금까지의 데이터는 삭제되고 복원파일 데이터를 입력합니다.', 'kboard')?></p>
					</form>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for=""><?php echo __('Attachments', 'kboard')?></label></th>
				<td>
					<p class="description"><?php echo __('<code>/wp-content/uploads/kboard_attached</code> 이 경로의 모든 파일을 FTP를 사용해서 다운로드받아 옮겨주세요.', 'kboard')?></p>
				</td>
			</tr>
		</tbody>
	</table>
</div>