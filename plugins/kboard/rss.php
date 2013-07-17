<?php
include reset(explode(DIRECTORY_SEPARATOR . 'wp-content', dirname(__FILE__) . DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR . 'wp-load.php';

$list = new ContentList();
$list->rpp = 100;
$list->initWithRSS();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0">
	<channel>
		<title>워드프레스 KBoard 피드</title>
		<link><?=plugins_url()?>/kboard/rss.php</link>
		<description>워드프레스 KBoard 피드</description>
		<?php while($content = $list->hasNext()): $kboard = new KBoard($content->board_id); ?>
		<item>
			<title><![CDATA[<?=$content->title?>]]></title>
			<link><![CDATA[<?=plugins_url()?>/kboard/board.php?board_id=<?=$kboard->uid?>&mod=document&uid=<?=$content->uid?>]]></link>
			<description><![CDATA[<?=$content->content?>]]></description>
			<author><?=$content->member_display?></author>
			<pubDate><?=gmdate(DATE_RSS, strtotime($content->date))?></pubDate>
			<category domain="<?=plugins_url()?>/kboard/board.php?board_id=<?=$kboard->uid?>"><?=$kboard->board_name?></category>
		</item>
		<?php endwhile;?>
	</channel>
</rss>