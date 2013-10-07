<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';

header("Content-Type: text/html; charset=UTF-8");
if(!stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard'));
if(!current_user_can('activate_plugins')) wp_die('KBoard : '.__('No backup privilege.', 'kboard'));

include KBOARD_DIR_PATH.'/class/KBBackup.class.php';
$backup = new KBBackup();

$tables = $backup->getTables();
foreach($tables AS $key => $value){
	$data .= $backup->getXml($value);
}

$backup->download($data, 'xml');
?>