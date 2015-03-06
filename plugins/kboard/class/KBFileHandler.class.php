<?php
/**
 * 파일 조작 클래스
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBFileHandler {
	
	private $path;
	private $file_extension;
	private $file_unique_name;
	private $extension;
	private $extension_type;
	private $limit_file_size;
	
	/**
	 * 파일 조작 클래스
	 * @param string $path
	 */
	function __construct($path=''){
		if($path) $this->setPath($path);
	}
	
	/**
	 * 디렉토리를 지정한다.
	 * @param string $path
	 */
	function setPath($path){
		$path = $this->addFirstSlash($path);
		if($path == '/' || !$path)  die('KBFileHandler->setPath() :: 디렉토리 이름이 없습니다.');

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
	function checkPath($path, $mk = true){
		$path = KBOARD_WORDPRESS_ROOT . $this->addFirstSlash($path);
		if(!file_exists($path)){
			return $mk?$this->mkPath($path):false;
		}
		return true;
	}
	
	/**
	 * 경로내 없는 모든 디렉토리를 생성한다.
	 * @param string $path
	 * @param int $permission
	 */
	function mkPath($path, $permission=0777){
		$path = str_replace(KBOARD_WORDPRESS_ROOT . '/', '', $path);
		$growing_path = KBOARD_WORDPRESS_ROOT;
		$path = explode("/", $path);
		for($i=0, $cnt=count($path); $i < $cnt; $i++){
			$growing_path = $growing_path . "/" . $path[$i];
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
	 * 파일명중 확장자를 분리해준다.
	 * @param string $file_Name
	 */
	private function explodeExtension($file_name){
		$this->file_extension = strtolower(end(explode('.', $file_name)));
	}
	
	/**
	 * 파일 확장자를 확인한다.
	 * @param string $file_Name
	 */
	private function checkExtension($file_name){
		if(!isset($this->file_extension) || !$this->file_extension){
			$this->explodeExtension($file_name);
		}
		for($i=0; $i<count($this->extension); $i++){
			if(!strcmp($this->extension[$i], $this->file_extension)){
				return true;
				break;
			}
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
			exit;
		}
		return true;
	}
	
	/**
	 * 파일명을 유일하게 만든다.
	 */
	private function makeUniqueName(){
		srand((double)microtime()*1000000);
		$random = rand(1000000,2000000);
		$date = current_time('Ymdhis');
		$this->file_unique_name = "{$date}{$random}.{$this->file_extension}";
	}
	
	/**
	 * 파일 중복 체크한다.
	 */
	private function checkOverlap(){
		if(isset($this->file_unique_name) && file_exists("{$this->path}/{$this->file_unique_name}")){
			return true;
		}
		if(!isset($this->file_unique_name) || !$this->file_unique_name){
			$this->makeUniqueName();
		}
		return false;
	}
	
	/**
	 * 오류를 반환한다.
	 * @param string $Error
	 */
	private function checkError($error){
		// 추후 오류에 대한 메시지를 반환하도록 ....;
		return 1;
	}
	
	/**
	 * 작업을 롤백한다.
	 */
	private function rollback(){
		foreach($_FILES as $key=>$file){
			if(isset($file['tmp_name']) && is_array($file['tmp_name'])){
				foreach($file['tmp_name'] as $key=>$sub_file){
					if(isset($sub_file['tmp_name']) && $sub_file['tmp_name']){
						unlink($sub_file['tmp_name']);
					}
				}
			}
			else if(isset($file['tmp_name']) && $file['tmp_name']){
				unlink($file['tmp_name']);
			}
		}
	}
	
	/**
	 * 파일을 업로드 한다.
	 * @param string $name
	 * @param array $extension
	 * @param int $extension_type
	 * @param int $limit_file_size
	 */
	function upload($name, $extension=array(), $extension_type=1, $limit_file_size=10485760){
		// http://www.hajae.net/mong2mam/bbs/view.php?id=php&page=7&sn1=&divpage=1&sn=off&ss=on&sc=on&select_arrange=hit&desc=desc&no=37&PHPSESSID=8e9c477ce82f0eeb87788e5c18d3d3ee
		/*
		 $extension : 업로드시 허용 또는 허용하지 않는 확장자명
		 $extension_type :
		 	0 $extension 으로 넘어오는 확장자 배열을 포함하면 업로드 불가
		 	1 $extension 으로 넘어오는 확장자 배열을 포함하면 업로드 허용
		 $limit_file_size : 업로드 할수 있는 파일용량 제한 - (1메가 = 1048576)
		*/
		
		if(!$this->path) die('KBFileHandler->upload() :: 디렉토리 경로가 없습니다.');
		
		if(!isset($_FILES[$name])){
			return array(
					'stored_name' => '',
					'original_name' => '',
					'temp_name' => '',
					'error' => '',
					'type' => '',
					'size' => '',
					'path' => ''
			);
		}
		
		if(count($extension)<=0 || !is_array($extension)){
			$extension = array('jpg', 'jpeg', 'gif', 'png', 'bmp', 'zip', '7z', 'hwp', 'ppt', 'xls', 'doc', 'txt', 'pdf', 'xlsx', 'pptx', 'docx');
		}
		
		$this->extension = $extension;
		$this->extension_type = $extension_type;
		$this->limit_file_size = $limit_file_size;
		
		$file_input = $_FILES[$name];
		if(is_array($file_input['tmp_name'])) $files = $this->multipleUpload($file_input);
		else $files = $this->singleUpload($file_input);
		
		unset($this->file_unique_name);
		unset($this->file_extension);
		
		return $files; // 모든 파일을 업로드 한후 파일 정보를 넘겨준다.
	}
	
	/**
	 * 단독 파일 업로드
	 * @param File $file
	 */
	private function singleUpload($file){
		if($file['size'] >= 0 && $file['name']){
			
			// HTTP POST를 통하여 업로드 된 파일인지 체크
			if(!is_uploaded_file($file['tmp_name'])){
				echo "<script>alert('파일이 올바르게 업로드 되지 않았습니다.');history.go(-1);</script>";
				$this->rollback();
				exit;
			}
			
			// 파일 확장자 체크
			$temps = $this->checkExtension($file['name']);
			if( ($temps && !$this->extension_type) || (!$temps && $this->extension_type) ){
				echo "<script>alert('업로드 가능한 파일 확장자가 아닙니다.');history.go(-1);</script>";
				$this->rollback();
				exit;
			}
			
			// 파일 사이즈 체크
			if(!$this->checkFileSize($file['size'])){
				echo "<script>alert('업로드 파일 용량이 너무 큽니다.');history.go(-1);</script>";
				$this->rollback();
				exit;
			}
			
			// 오류 체크
			if(!$this->checkError($file['error'])){
				echo "<script>alert('업로드 중 오류가 발생했습니다.');history.go(-1);</script>";
				$this->rollback();
				exit;
			}
			
			// 파일 중복 체크
			if($this->checkOverlap()){
				$this->makeUniqueName();
			}
			
			if(!@move_uploaded_file($file['tmp_name'], KBOARD_WORDPRESS_ROOT . $this->path. '/' .$this->file_unique_name)){
				echo "<script>alert('".$file['tmp_name'] . " 파일 업로드 중 오류가 발생 했습니다 : ". $this->path . '/' . $this->file_unique_name."');history.go(-1);</script>";
				$this->rollback();
				exit;
			}
			
			return array(
					'stored_name' => $this->file_unique_name,
					'original_name' => $file['name'],
					'temp_name' => $file['tmp_name'],
					'error' => $file['error'],
					'type' => $file['type'],
					'size' => $file['size'],
					'path' => $this->path
			);
		}
		return array(
				'stored_name' => '',
				'original_name' => '',
				'temp_name' => '',
				'error' => '',
				'type' => '',
				'size' => '',
				'path' => ''
		);
	}
	
	/**
	 * 다중 파일 업로드
	 * @param Files $file
	 */
	private function multipleUpload($file){
		for($i=0, $cnt=count($file['name']); $i<$cnt; $i++){
			// 파일사이즈가 0인건 업로드 하지 않는다.
			if($file['size'][$i] >= 0 && $file['name'][$i]){
				
				// HTTP POST를 통하여 업로드 된 파일인지 체크
				if(!is_uploaded_file($file['tmp_name'][$i])){
					echo "<script>alert('파일이 올바르게 업로드 되지 않았습니다.');history.go(-1);</script>";
					$this->rollback();
					exit;
				}
				
				// 파일 확장자 체크
				unset($this->file_extension);
				$temps = $this->checkExtension($file['name'][$i]);
				if( (!$temps && !$this->extension_type) || ($temps && $this->extension_type) ){
					echo "<script>alert('업로드 가능한 파일 확장자가 아닙니다.');history.go(-1);</script>";
					$this->rollback();
					exit;
				}
				
				// 파일 사이즈 체크
				if(!$this->checkFileSize($file['size'][$i])){
					echo "<script>alert('업로드 파일 용량이 너무 큽니다.');history.go(-1);</script>";
					$this->rollback();
					exit;
				}
				
				// 오류 체크
				if(!$this->checkError($file['error'][$i])){
					echo "<script>alert('업로드 중 오류가 발생했습니다.');history.go(-1);</script>";
					$this->rollback();
					exit;
				}
				
				// 파일 중복 체크
				if($this->checkOverlap()){
					$this->makeUniqueName();
				}
				
				if(!@move_uploaded_file($file['tmp_name'][$i], KBOARD_WORDPRESS_ROOT . $this->path. "/" .$this->file_unique_name)){
					echo "<script>alert('".$file['tmp_name'][$i] . " 파일 업로드 중 오류가 발생 했습니다 : ". $this->path . "/" . $this->file_unique_name."');history.go(-1);</script>";
					$this->rollback();
					exit;
				}
				
				$files[$i] = array(
						'stored_name' => $this->file_unique_name,
						'original_name' => $file['name'][$i],
						'temp_name' => $file['tmp_name'][$i],
						'error' => $file['error'][$i],
						'type' => $file['type'][$i],
						'size' => $file['size'][$i],
						'path' => $this->path
				);
			}
			else{
				$files[$i] = array(
						'stored_name' => '',
						'original_name' => '',
						'temp_name' => '',
						'error' => '',
						'type' => '',
						'size' => '',
						'path' => ''
				);
			}
		}
		return isset($files)?$files:array();
	}
	
	/**
	 * 파일을 다운로드 한다.
	 * @param string $file
	 */
	function download($file){
		// http://www.finalwebsites.com/forums/topic/php-file-download
		
		if(!$this->path) die('KBFileHandler->download() :: 디렉토리 경로가 없습니다.');
		$fullPath = KBOARD_WORDPRESS_ROOT . $this->path . '/' . $file;
		
		if($fd = fopen($fullPath, "r")){
			$fsize = filesize($fullPath);
			$path_parts = pathinfo($fullPath);
			$ext = strtolower($path_parts['extension']);
			
			switch($ext){
				case "pdf":
					header("Content-type: application/pdf"); // add here more headers for diff. extensions
					header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a download
					break;
				default:
					header("Content-type: application/octet-stream");
					header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
			}
			
			header("Content-length: $fsize");
			header("Cache-control: private"); // use this to open files directly
			while(!feof($fd)){
				$buffer = fread($fd, 2048);
				echo $buffer;
			}
		}
		fclose($fd);
	}
	
	/**
	 * 파일을 삭제한다.
	 * @param string $file
	 */
	function remove($file){
		if(!$this->path) die('KBFileHandler->delete() :: 디렉토리 경로가 없습니다.');
		return @unlink(KBOARD_WORDPRESS_ROOT . $this->path . $this->addFirstSlash($file));
	}
	
	/**
	 * 파일 또는 디렉토리 사이즈를 반환한다.
	 * @param string $path
	 * @param boolean $format
	 */
	function getSize($path, $format=true){
		$path = KBOARD_WORDPRESS_ROOT . $this->addFirstSlash($path);
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
		$c=0;
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
		$dirlist = array();
		if($dh = @opendir($path)){
			while(($file = readdir($dh)) !== false){
				if($file == "." || $file == "..") continue;
				$dirlist[] = $file;
			}
			closedir($dh);
		}
		return $dirlist;
	}
	
	/**
	 * 파일 및 디렉토리를 삭제한다.
	 * @param string $path
	 */
	public function delete($path){
		if(is_file($path)) unlink($path);
		elseif(is_dir($path)){
			if(substr($path, -1) != '/') $path .= '/';
			$dirlist = $this->getDirlist($path);
			foreach($dirlist as $file){
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
		if(is_file($path)){
			if(time() - filemtime($path) > $time){
				unlink($path);
			}
		}
		elseif(is_dir($path)){
			if(substr($path, -1) != '/') $path .= '/';
			$dirlist = $this->getDirlist($path);
			foreach($dirlist as $file){
				$this->deleteWithOvertime($path . $file, $time);
			}
			$stat = stat($path);
			if(time() - $stat['mtime'] > $time){
				rmdir($path);
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
			$dirlist = $this->getDirlist($from);
			$copy_result = true;
			foreach($dirlist as $file){
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
			return @file_put_contents($filename, $data);
		}
		else{
			if($fp = @fopen($filename, 'w')){
				fwrite($fp, $data);
				return fclose($fp);
			}
			return $fp;
		}
	}
}
?>