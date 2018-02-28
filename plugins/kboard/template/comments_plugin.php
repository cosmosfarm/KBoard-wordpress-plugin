<?php if(!defined('ABSPATH')) exit;?>
<div style="padding: 20px 0;">
	<!-- 코스모스팜 소셜댓글 시작 -->
	<div id="cosmosfarm-comments" data-plugin-id="<?php echo $meta->comments_plugin_id?>" data-href="<?php echo $url->getCommentsPluginURLWithUID($_GET['uid'])?>" data-width="100%" data-row="<?php echo $meta->comments_plugin_row?>" data-access-token="<?php echo $template->get_comments_access_token()?>"><a href="http://www.cosmosfarm.com/plugin/comments">코스모스팜 소셜댓글</a></div>
	<!-- 코스모스팜 소셜댓글 종료 -->
</div>