<?php
$path = explode(DIRECTORY_SEPARATOR . 'wp-content', dirname(__FILE__) . DIRECTORY_SEPARATOR);
include reset($path) . DIRECTORY_SEPARATOR . 'wp-load.php';

header("Content-Type: text/html; charset=UTF-8");
if(!stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) wp_die('KBoard : 이 페이지는 외부에서의 접근을 제한하고 있습니다.');
if(!current_user_can('activate_plugins')) wp_die('KBoard : 백업 권한이 없습니다.');

include KBOARD_DIR_PATH . '/KBBackup.class.php';
$backup = new KBBackup();

$tables = $backup->getTables();
foreach($tables AS $key => $value){
	$data .= $backup->getXml($value);
}

$backup->download($data, 'xml');
?>