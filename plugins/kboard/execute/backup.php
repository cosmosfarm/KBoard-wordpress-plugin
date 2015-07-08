<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';

header('Content-Type: text/html; charset=UTF-8');
$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
if($referer){
	$url = parse_url($referer);
	$referer_host = $url['host'];
}
else{
	wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard'));
}
if(!in_array($referer_host, array($host))) wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard'));
if(!current_user_can('activate_plugins')) wp_die('KBoard : '.__('No backup privilege.', 'kboard'));

include KBOARD_DIR_PATH.'/class/KBBackup.class.php';
$backup = new KBBackup();

$tables = $backup->getTables();
$data = '';
foreach($tables as $key => $value){
	$data .= $backup->getXml($value);
}

$backup->download($data, 'xml');
?>