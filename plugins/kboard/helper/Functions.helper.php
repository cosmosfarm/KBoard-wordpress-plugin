<?php
if(!defined('ABSPATH')) exit;
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
 * @param string $file
 * @return string
 */
function kboard_mime_type($file){
	/*
	 * http://php.net/manual/en/function.mime-content-type.php#87856
	 */
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
	$temp = basename($file);
	$temp = explode('.', $temp);
	$temp = array_pop($temp);
	$ext = strtolower($temp);
	
	if(array_key_exists($ext, $mime_types)){
		$mime_type = $mime_types[$ext];
	}
	else if(function_exists('mime_content_type') && file_exists($file)){
		$mime_type = mime_content_type($file);
	}
	else if(function_exists('finfo_open') && file_exists($file)){
		$finfo = finfo_open(FILEINFO_MIME);
		$mime_type = finfo_file($finfo, $file);
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
		return __('제한없음', 'kboard');
	}
	else if($permission == 'author'){
		return __('로그인 사용자', 'kboard');
	}
	else if($permission == 'editor'){
		return __('선택된 관리자', 'kboard');
	}
	else if($permission == 'administrator'){
		return __('최고관리자', 'kboard');
	}
	else if($permission == 'roles'){
		return __('직접선택', 'kboard');
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
	
	$new_image = $upload_dir['basedir'] . "{$resize_dir}/{$resize_name}";
	$new_image_src = content_url("uploads{$resize_dir}/{$resize_name}");
	
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

/**
 * 리사이즈 이미지를 지운다.
 * @param string $image_src
 */
function kbaord_delete_resize($image_src){
	if(file_exists($image_src)){
		$size = getimagesize($image_src);
		if($size){
			$fileinfo = pathinfo($image_src);
			$original_name = basename($image_src, '.'.$fileinfo['extension']).'-';
			$dir = dirname($image_src);
			if($dh = @opendir($dir)){
				while(($file = readdir($dh)) !== false){
					if($file == "." || $file == "..") continue;
					if(strpos($file, $original_name) !== false){
						@unlink($dir . '/' . $file);
					}
				}
			}
			closedir($dh);
		}
	}
}

/**
 * 새글 알림 시간을 반환한다.
 * @return int
 */
function kboard_new_document_notify_time(){
	return get_option('kboard_new_document_notify_time', '86400');
}

/**
 * 업로드 가능한 파일 크기를 반환한다.
 */
function kboard_limit_file_size(){
	return get_option('kboard_limit_file_size', kboard_upload_max_size());
}

/**
 * 서버에 설정된 최대 업로드 크기를 반환한다.
 * @link http://stackoverflow.com/questions/13076480/php-get-actual-maximum-upload-size
 * @return int
 */
function kboard_upload_max_size(){
	static $max_size = -1;
	if($max_size < 0){
		$max_size = kboard_parse_size(ini_get('post_max_size'));
		$upload_max = kboard_parse_size(ini_get('upload_max_filesize'));
		if($upload_max > 0 && $upload_max < $max_size){
			$max_size = $upload_max;
		}
	}
	return $max_size;
}

/**
 * 바이트로 크기를 변환한다.
 * @link http://stackoverflow.com/questions/13076480/php-get-actual-maximum-upload-size
 * @return int
 */
function kboard_parse_size($size){
	$unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
	$size = preg_replace('/[^0-9\.]/', '', $size);
	if($unit){
		return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
	}
	else{
		return round($size);
	}
}

/**
 * 허가된 첨부파일 확장자를 반환한다.
 * @param boolean $to_array
 */
function kboard_allow_file_extensions($to_array=false){
	$file_extensions = get_option('kboard_allow_file_extensions');
	$file_extensions = trim($file_extensions);
	
	if(!$file_extensions){
		$file_extensions = 'jpg, jpeg, gif, png, bmp, zip, 7z, hwp, ppt, xls, doc, txt, pdf, xlsx, pptx, docx, torrent, smi';
	}
	
	if($to_array){
		$file_extensions = explode(',', $file_extensions);
		return array_map('trim', $file_extensions);
	}
	return $file_extensions;
}
?>