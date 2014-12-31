<?php
/**
 * KBoard 워드프레스 게시판 사용자 함수
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */

/**
 * JSON 인코더
 * @param array $val
 * @return string
 */
function kboard_json_encode($val){
	if(function_exists('json_encode')){
		return json_encode($val);
	}
	
	/*
	 * http://kr1.php.net/json_encode#113219
	 */
	
	if(is_string($val)) return '"'.addslashes($val).'"';
	if(is_numeric($val)) return $val;
	if($val === null) return 'null';
	if($val === true) return 'true';
	if($val === false) return 'false';

	$assoc = false;
	$i = 0;
	foreach($val as $k=>$v){
		if($k !== $i++){
			$assoc = true;
			break;
		}
	}
	$res = array();
	foreach($val as $k=>$v){
		$v = kboard_json_encode($v);
		if($assoc){
			$k = '"'.addslashes($k).'"';
			$v = $k.':'.$v;
		}
		$res[] = $v;
	}
	$res = implode(',', $res);
	
	return ($assoc)? '{'.$res.'}' : '['.$res.']';
}

/**
 * 파일의 MIME Content-type을 반환한다.
 * @param string $filename
 * @return string
 */
function kboard_mime_type($filename){
	$filename = basename($filename);
	$mime_types = array(
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',
			
			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',
			
			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',
			'7z' => 'application/x-7z-compressed',
			
			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',
			
			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',
			
			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
			'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
			'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
			'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
			
			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
			
			// etc
			'hwp' => 'application/hangul',
	);
	
	$mime_type = '';
	$ext = strtolower(array_pop(explode('.', $filename)));
	if(array_key_exists($ext, $mime_types)){
		$mime_type = $mime_types[$ext];
	}
	elseif(function_exists('mime_content_type')){
		$mime_type = mime_content_type($filename);
	}
	else{
		$finfo = finfo_open(FILEINFO_MIME);
		$mime_type = finfo_file($finfo, $filename);
		finfo_close($finfo);
	}
	
	if($mime_type) return $mime_type;
	else return 'application/octet-stream';
}

/**
 * 권한을 한글로 출력한다.
 * @param string $permission
 * @return string
 */
function kboard_permission($permission){
	if($permission == 'all'){
		return '제한없음';
	}
	else if($permission == 'author'){
		return '로그인 사용자';
	}
	else if($permission == 'editor'){
		return '선택된 관리자';
	}
	else if($permission == 'administrator'){
		return '최고관리자';
	}
	else{
		return $permission;
	}
}

/**
 * Captcha 이미지를 생성하고 이미지 주소를 반환한다.
 * @return string
 */
function kboard_captcha(){
	include_once KBOARD_DIR_PATH . '/class/KBCaptcha.class.php';
	$captcha = new KBCaptcha();
	return $captcha->createImage();
}

/**
 * 이미지 사이즈를 조절한다.
 * @param string $image_src
 * @param int $width
 * @param int $height
 * @return string
 */
function kboard_resize($image_src, $width, $height){
	$upload_dir = wp_upload_dir();
	$dirname = dirname($image_src);
	$dirname = explode('/wp-content/uploads', $dirname);
	$resize_dir = end($dirname);
	
	$basename = basename($image_src);
	$fileinfo = pathinfo($basename);
	$resize_name = basename($image_src, '.'.$fileinfo['extension']) . "-{$width}x{$height}.{$fileinfo['extension']}";
	
	$new_image = strtolower($upload_dir['basedir'] . "{$resize_dir}/{$resize_name}");
	$new_image_src = strtolower(content_url("uploads{$resize_dir}/{$resize_name}"));
	
	if(file_exists($new_image)){
		return $new_image_src;
	}
	
	$image_editor = wp_get_image_editor($upload_dir['basedir'] . "{$resize_dir}/{$basename}");
	if(!is_wp_error($image_editor)){
		$image_editor->resize($width, $height, true);
		$image_editor->save($new_image);
		return $new_image_src;
	}
	else{
		return $image_src;
	}
}
?>