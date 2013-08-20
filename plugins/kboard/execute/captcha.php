<?php
$path = explode(DIRECTORY_SEPARATOR . 'wp-content', dirname(__FILE__) . DIRECTORY_SEPARATOR);
include reset($path) . DIRECTORY_SEPARATOR . 'wp-load.php';
include KBOARD_DIR_PATH . '/KBCaptcha.class.php';

$captcha = new KBCaptcha();
$captcha->createImage();
?>