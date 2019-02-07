<?php
/**
 * 파일 관리 클래스
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBFileHandler {
	
	private $name;
	private $path;
	private $file_extension;
	private $extension;
	private $limit_file_size;
	private $uploaded_file;
	private $abspath;
	
	/**
	 * 파일 조작 클래스
	 * @param string $path
	 */
	function __construct($path=''){
		$this->abspath = untrailingslashit(ABSPATH);
		if($path) $this->setPath($path);
	}
	
	/**
	 * 디렉토리를 지정한다.
	 * @param string $path
	 */
	function setPath($path){
		$path = $this->addFirstSlash($path);
		if($path == '/' || !$path) die('KBFileHandler->setPath() :: 디렉토리 이름이 없습니다.');
		if(!$this->checkPath($path)){
			$this->path = '';
			return false;
		}
		else{
			$this->path = $path;
			return true;
		}
	}
	
	/**
	 * 디렉토리 경로를 반환한다.
	 */
	function getPath(){
		return $this->path;
	}
	
	/**
	 * 파일이 업로드될 디렉토리를 확인한다.
	 * @param string $path
	 * @param boolean $mk
	 */
	function checkPath($path, $mk=true){
		$path = $this->abspath . $this->addFirstSlash($path);
		if($mk) wp_mkdir_p($path);
		if(!file_exists($path)){
			return false;
		}
		else if(!is_writable($path)){
			@chmod($path, 0777);
			return true;
		}
		return true;
	}
	
	/**
	 * 경로내 없는 모든 디렉토리를 생성한다.
	 * @param string $path
	 * @param int $permission
	 */
	function mkPath($path, $permission=0777){
		$path = str_replace($this->abspath . '/', '', $path);
		$growing_path = $this->abspath;
		$path = explode('/', $path);
		for($i=0, $cnt=count($path); $i < $cnt; $i++){
			$growing_path = "{$growing_path}/{$path[$i]}";
			if(!file_exists($growing_path)){
				$mkdir = @mkdir($growing_path, $permission);
				if(!$mkdir) return false;
				@chmod($growing_path, $permission);
			}
		}
		return true;
	}
	
	/**
	 * 경로 맨 앞에 슬래쉬를 추가한다.
	 * @param string $path
	 */
	private function addFirstSlash($path){
		if(substr($path, 0, 1) == '/') return $path;
		else return '/' . $path;
	}
	
	/**
	 * 파일명중 확장자 반환한다.
	 * @param string $file_Name
	 */
	private function getExtension($file_name){
		$file_extension = explode('.', $file_name);
		$file_extension = end($file_extension);
		return strtolower($file_extension);
	}
	
	/**
	 * 파일 확장자를 확인한다.
	 * @param string $file_Name
	 */
	private function checkExtension($file_name){
		$file_extension = $this->getExtension($file_name);
		if(in_array($file_extension, $this->extensions)){
			return true;
		}
		return false;
	}
	
	/**
	 * 파일의 용량을 확인한다.
	 * @param int $file_Size
	 */
	private function checkFileSize($file_Size){
		if($this->limit_file_size < $file_Size){
			return false;
		}
		return true;
	}
	
	/**
	 * 고유한 파일 이름을 반환한다.
	 * @return string
	 */
	private function getUniqueName($file_name){
		srand((double)microtime()*1000000);
		$random = rand(1000000,9999999);
		$uniqid = uniqid();
		$file_extension = $this->getExtension($file_name);
		return "{$uniqid}{$random}.{$file_extension}";
	}
	
	/**
	 * 오류를 반환한다.
	 * @param string $Error
	 */
	private function checkError($error){
		return $error;
	}
	
	/**
	 * 작업을 롤백한다.
	 */
	private function rollback(){
		foreach($_FILES as $key=>$file){
			if(isset($file['tmp_name']) && is_array($file['tmp_name'])){
				foreach($file['tmp_name'] as $tmp_name){
					@unlink($tmp_name);
				}
			}
			else if(isset($file['tmp_name']) && $file['tmp_name']){
				@unlink($file['tmp_name']);
			}
		}
		if(isset($this->uploaded_file) && $this->uploaded_file){
			foreach($this->uploaded_file as $uploaded_file){
				@unlink($uploaded_file);
			}
		}
	}
	
	/**
	 * 파일을 업로드 한다.
	 * @param string $name
	 * @param array $extension
	 * @param int $limit_file_size
	 */
	function upload($name, $extension=array(), $limit_file_size=0){
		/*
		 * $extension : 업로드 가능한 확장자 배열
		 * $limit_file_size : 업로드 가능한 파일용량 제한 (1메가 = 1048576)
		 */
		
		if(!$this->path) die('KBFileHandler->upload() :: 디렉토리 경로가 없거나 하위 디렉토리에 쓰기 권한이 없습니다.');
		
		// 이름 등록
		$this->name = $name;
		
		if(!isset($_FILES[$this->name])){
			return array(
				'stored_name' => '',
				'original_name' => '',
				'temp_name' => '',
				'error' => '',
				'type' => '',
				'size' => '',
				'path' => '',
				'metadata' => ''
			);
		}
		
		if(count($extension)<=0 || !is_array($extension)){
			$extension = kboard_allow_file_extensions(true);
		}
		
		$this->extensions = apply_filters('kboard_upload_extension', $extension);
		
		if($limit_file_size){
			$this->limit_file_size = $limit_file_size;
		}
		else{
			$this->limit_file_size = kboard_limit_file_size();
		}
		
		$file_input = $_FILES[$this->name];
		if(is_array($file_input['tmp_name'])){
			$files = $this->multipleUpload($file_input);
		}
		else{
			$files = $this->singleUpload($file_input);
		}
		
		unset($this->uploaded_file);
		
		return $files;
	}
	
	/**
	 * 단독 파일 업로드
	 * @param array $file
	 */
	private function singleUpload($file){
		if($file['size']){
			
			// HTTP POST를 통하여 업로드 된 파일인지 체크
			if(!is_uploaded_file($file['tmp_name'])){
				$this->rollback();
				$message = sprintf(__('%s 파일이 올바르게 업로드되지 않았습니다.', 'kboard'), $file['name']);
				echo "<script>alert('{$message}');history.go(-1);</script>";
				exit;
			}
			
			// 파일 확장자 체크
			if(!$this->checkExtension($file['name'])){
				$this->rollback();
				$message = sprintf(__('%s 파일은 업로드 가능한 파일 형식이 아닙니다.', 'kboard'), $file['name']);
				echo "<script>alert('{$message} ');history.go(-1);</script>";
				exit;
			}
			
			// 파일 사이즈 체크
			if(!$this->checkFileSize($file['size'])){
				$this->rollback();
				$message = sprintf(__('%s 파일의 용량이 너무 큽니다.', 'kboard'), $file['name']);
				echo "<script>alert('{$message}');history.go(-1);</script>";
				exit;
			}
			
			// 오류 체크
			if($this->checkError($file['error'])){
				$this->rollback();
				$message = sprintf(__('%s 파일 업로드 중 오류가 발생했습니다.', 'kboard'), $file['name']);
				echo "<script>alert('{$message}');history.go(-1);</script>";
				exit;
			}
			
			$file_unique_name = $this->getUniqueName($file['name']);
			
			if(!@move_uploaded_file($file['tmp_name'], "{$this->abspath}{$this->path}/{$file_unique_name}")){
				$this->uploaded_file[] = "{$this->abspath}{$this->path}/{$file_unique_name}";
				$this->rollback();
				$message = sprintf(__('%s 파일 업로드 중 오류가 발생했습니다.', 'kboard'), $file['name']);
				echo "<script>alert('{$message}');history.go(-1);</script>";
				exit;
			}
			
			// 사진 메타데이터 추출
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			$metadata = wp_read_image_metadata("{$this->abspath}{$this->path}/{$file_unique_name}");
			if(!$metadata){
				$metadata = array();
			}
			
			$this->imageOrientation("{$this->abspath}{$this->path}/{$file_unique_name}");
			
			return apply_filters('kboard_uploaded_file', array(
				'stored_name' => $file_unique_name,
				'original_name' => sanitize_file_name($file['name']),
				'temp_name' => $file['tmp_name'],
				'error' => $file['error'],
				'type' => $file['type'],
				'size' => $file['size'],
				'path' => $this->path,
				'metadata' => $metadata
			), $this->name);
		}
		else{
			return array(
				'stored_name' => '',
				'original_name' => '',
				'temp_name' => '',
				'error' => '',
				'type' => '',
				'size' => '',
				'path' => '',
				'metadata' => array()
			);
		}
	}
	
	/**
	 * 다중 파일 업로드
	 * @param array $file
	 */
	private function multipleUpload($file){
		
		// HTTP POST를 통하여 업로드 된 파일인지 체크
		foreach($file['tmp_name'] as $key=>$tmp_name){
			if($file['size'][$key] && !is_uploaded_file($tmp_name)){
				$this->rollback();
				$message = sprintf(__('%s 파일이 올바르게 업로드되지 않았습니다.', 'kboard'), $file['name'][$key]);
				echo "<script>alert('{$message}');history.go(-1);</script>";
				exit;
			}
		}
		
		// 파일 확장자 체크
		foreach($file['name'] as $key=>$name){
			if($file['size'][$key] && !$this->checkExtension($name)){
				$this->rollback();
				$message = sprintf(__('%s 파일은 업로드 가능한 파일 형식이 아닙니다.', 'kboard'), $file['name'][$key]);
				echo "<script>alert('{$message}');history.go(-1);</script>";
				exit;
			}
		}
		
		// 파일 사이즈 체크
		foreach($file['size'] as $key=>$size){
			if($file['size'][$key] && !$this->checkFileSize($size)){
				$this->rollback();
				$message = sprintf(__('%s 파일의 용량이 너무 큽니다.', 'kboard'), $file['name'][$key]);
				echo "<script>alert('{$message}');history.go(-1);</script>";
				exit;
			}
		}
		
		// 오류 체크
		foreach($file['error'] as $key=>$error){
			if($file['size'][$key] && $this->checkError($error)){
				$this->rollback();
				$message = sprintf(__('%s 파일 업로드 중 오류가 발생했습니다.', 'kboard'), $file['name'][$key]);
				echo "<script>alert('{$message}');history.go(-1);</script>";
				exit;
			}
		}
		
		foreach($file['name'] as $key=>$value){
			if($file['size'][$key]){
				$file_unique_name = $this->getUniqueName($file['name'][$key]);
					
				if(!@move_uploaded_file($file['tmp_name'][$key], "{$this->abspath}{$this->path}/{$file_unique_name}")){
					$this->uploaded_file[] = "{$this->abspath}{$this->path}/{$file_unique_name}";
					$this->rollback();
					$message = sprintf(__('%s 파일 업로드 중 오류가 발생했습니다.', 'kboard'), $file['name'][$key]);
					echo "<script>alert('{$message}');history.go(-1);</script>";
					exit;
				}
				
				// 사진 메타데이터 추출
				require_once(ABSPATH . 'wp-admin/includes/image.php');
				$metadata = wp_read_image_metadata("{$this->abspath}{$this->path}/{$file_unique_name}");
				if(!$metadata){
					$metadata = array();
				}
				
				$this->imageOrientation("{$this->abspath}{$this->path}/{$file_unique_name}");
					
				$files[] = array(
					'stored_name' => $file_unique_name,
					'original_name' => sanitize_file_name($file['name'][$key]),
					'temp_name' => $file['tmp_name'][$key],
					'error' => $file['error'][$key],
					'type' => $file['type'][$key],
					'size' => $file['size'][$key],
					'path' => $this->path,
					'metadata' => $metadata
				);
			}
		}
		
		if(isset($files) && $files){
			foreach($files as $item){
				$new_files[] = apply_filters('kboard_uploaded_file', $item, $this->name);
			}
			return $new_files;
		}
		else{
			return array();
		}
	}
	
	/**
	 * 파일을 삭제한다.
	 * @param string $file
	 */
	function remove($file){
		if(!$this->path) die('KBFileHandler->delete() :: 디렉토리 경로가 없습니다.');
		return @unlink($this->abspath . $this->path . $this->addFirstSlash($file));
	}
	
	/**
	 * 파일 또는 디렉토리 사이즈를 반환한다.
	 * @param string $path
	 * @param boolean $format
	 */
	function getSize($path, $format=true){
		$path = $this->abspath . $this->addFirstSlash($path);
		if(is_file($path)) $size = filesize($path);
		else $size = $this->dirsize($path);
		
		if($format) return $this->formatBytes($size);
		else return $size;
	}
	
	/**
	 * 바이트 크기를 입력 받아 적당한 단위로 변환한다.
	 * @author Martin Sweeny
	 * @param int $b
	 * @param int $p
	 */
	function formatBytes($b, $p=null){
		// http://php.net/manual/en/function.filesize.php
		$units = array("B","K","M","G","T","P","E","Z","Y");
		$c = 0;
		if(!$p && $p !== 0){
			foreach($units as $k => $u){
				if(($b / pow(1024,$k)) >= 1){
					$r['bytes'] = $b / pow(1024,$k);
					$r['units'] = $u;
					$c++;
				}
			}
			return number_format($r['bytes']) . $r['units'];
		}
		else{
			return number_format($b / pow(1024,$p)) . $units[$p];
		}
	}
	
	/**
	 * 리눅스 du 명령어로 디렉토리 사이즈를 반환한다.
	 * @param string $path
	 */
	function getDirsize($path){
		// http://php.net/manual/en/function.filesize.php
		$result=explode("\t", exec("du -hs " . $path), 2);
		return ($result[1]==$path ? $result[0] : 'error');
	}
	
	/**
	 * 디렉토리 사이즈를 반환한다.
	 * @param string $path
	 */
	function dirsize($path){
		// http://php.net/manual/en/function.filesize.php
		$size = 0;
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file){
			$size += $file->getSize();
		}
		return $size;
	}
	
	/**
	 * 경로의 내용을 반환한다.
	 * @param string $path
	 * @return array
	 */
	function getDirlist($path){
		return array_diff(scandir($path), array('.', '..'));
	}
	
	/**
	 * 파일 및 디렉토리를 삭제한다.
	 * @param string $path
	 */
	public function delete($path){
		if(is_file($path)) unlink($path);
		elseif(is_dir($path)){
			if(substr($path, -1) != '/') $path .= '/';
			$files = $this->getDirlist($path);
			foreach($files as $file){
				$this->delete($path . $file);
			}
			rmdir($path);
		}
	}
	
	/**
	 * 오래된 파일 및 디렉토리를 삭제한다.
	 * @param string $path
	 * @param int $time
	 */
	public function deleteWithOvertime($path, $time){
		if(is_dir($path)){
			if(substr($path, -1) != '/') $path .= '/';
			$files = $this->getDirlist($path);
			foreach($files as $file){
				$this->deleteWithOvertime($path . $file, $time);
			}
			$stat = stat($path);
			if(time() - $stat['mtime'] > $time){
				rmdir($path);
			}
		}
		else if(is_file($path)){
			if(time() - filemtime($path) > $time){
				unlink($path);
			}
		}
	}
	
	/**
	 * 파일 및 디렉토리를 복사한다.
	 * @param string $from
	 * @param string $to
	 */
	public function copy($from, $to){
		if(is_file($from)){
			return @copy($from, $to);
		}
		elseif(is_dir($from)){
			if(substr($to, -1) != '/') $to .= '/';
			if(substr($from, -1) != '/') $from .= '/';
			$this->mkPath($to);
			$copy_result = true;
			$files = $this->getDirlist($from);
			foreach($files as $file){
				$copy_result = $this->copy($from . $file, $to . $file);
				if(!$copy_result) break;
			}
			return $copy_result;
		}
	}
	
	/**
	 * 파일을 작성한다.
	 * @param string $filename
	 * @param mixed $data
	 */
	public function putContents($filename, $data){
		if(function_exists('file_put_contents')){
			return file_put_contents($filename, $data);
		}
		else{
			if($fp = @fopen($filename, 'w')){
				fwrite($fp, $data);
				return fclose($fp);
			}
			return $fp;
		}
	}
	
	/**
	 * 이미지 방향을 확인해 로테이션한다.
	 * @param string $image
	 */
	public function imageOrientation($image){
		if(kboard_mime_type($image) == 'image/jpeg'){
			$image_editor = wp_get_image_editor($image);
			if(!is_wp_error($image_editor) && function_exists('exif_read_data')){
				$exif = @exif_read_data($image);
				if(isset($exif['Orientation']) && $exif['Orientation']){
					switch($exif['Orientation']){
						case 3: $image_editor->rotate(180); break;
						case 6: $image_editor->rotate(-90); break;
						case 8: $image_editor->rotate(90); break;
					}
					$image_editor->save($image);
				}
			}
		}
	}
}
?>