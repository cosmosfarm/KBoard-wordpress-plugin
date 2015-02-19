<?php if(!defined('ABSPATH')) exit;?>
<!-- 코스모스팜 댓글 플러그인 시작 -->
<script id="cosmosfarm-comments-script" type="text/javascript" src="https://plugin.cosmosfarm.com/comments.js"></script>
<div id="cosmosfarm-comments" data-plugin-id="<?php echo $meta->comments_plugin_id?>" data-href="<?php echo site_url($url->set('uid', intval($_GET['uid']))->set('mod', 'document')->toString())?>" data-width="100%" data-row="<?php echo $meta->comments_plugin_row?>"><a href="http://www.cosmosfarm.com/plugin/comments">코스모스팜 댓글 플러그인</a></div>
<!-- 코스모스팜 댓글 플러그인 종료 -->