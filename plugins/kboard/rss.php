<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';

$url = new KBUrl();
$list = new KBContentList();
$list->rpp = 20;
$list->initWithRSS();

header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0">
	<channel>
		<title><?php echo __('WordPress KBoard feed', 'kboard')?></title>
		<link><?php echo plugins_url()?>/kboard/rss.php</link>
		<description><?php echo __('WordPress KBoard feed', 'kboard')?></description>
		<?php
		while($content = $list->hasNext()):
			$kboard = new KBoard($content->board_id);
		?>
		<item>
			<title><![CDATA[<?php echo $content->title?>]]></title>
			<link><![CDATA[<?php echo $url->getDocumentRedirect($content->uid)?>]]></link>
			<description><![CDATA[<?php echo $content->content?>]]></description>
			<author><![CDATA[<?php echo $content->member_display?>]]></author>
			<pubDate><?php echo gmdate(DATE_RSS, strtotime($content->date))?></pubDate>
			<category><?php echo $kboard->board_name?></category>
		</item>
		<?php endwhile;?>
	</channel>
</rss>