<?php
$path = explode(DIRECTORY_SEPARATOR . 'wp-content', dirname(__FILE__) . DIRECTORY_SEPARATOR);
include reset($path) . DIRECTORY_SEPARATOR . 'wp-load.php';

$url = new KBUrl();
$list = new KBContentList();
$list->rpp = 20;
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
			<link><![CDATA[<?=$url->getDocumentRedirect($content->uid)?>]]></link>
			<description><![CDATA[<?=$content->content?>]]></description>
			<author><![CDATA[<?=$content->member_display?>]]></author>
			<pubDate><?=gmdate(DATE_RSS, strtotime($content->date))?></pubDate>
			<category domain="<?=$url->getBoardRedirect($kboard->uid)?>"><?=$kboard->board_name?></category>
		</item>
		<?php endwhile;?>
	</channel>
</rss>