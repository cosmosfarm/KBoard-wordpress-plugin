<?php
/**
 * 파일 조작 클래스
 * @author www.cosmosfarm.com
 */
if(!class_exists('KBFileHandler')){
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
			if($path == '/' || !$path)  die('FileHandler->setPath() :: 디렉토리 이름이 없습니다.');
	
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
		private function explodeExtension($file_Name){
			$Tmp = explode('.', $file_Name);
			$this->file_extension = $Tmp[count($Tmp)-1];
		}
	
		/**
		 * 파일 확장자를 확인한다.
		 * @param string $file_Name
		 */
		private function checkExtension($file_Name){
			if(!count($this->file_extension)){
				$this->explodeExtension($file_Name);
			}
			$temp = strtolower($this->file_extension); // 확장자를 소문자로 변환해준다.
			
			for($i=0;$i <= count($this->extension);$i++){
				if(!strcmp($this->extension[$i],$temp)){
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
			$Rnd = rand(1000000,2000000);
			$temp = date("Ymdhis");
			$this->file_unique_name = $temp.$Rnd . '.' . $this->file_extension;
		}
	
		/**
		 * 파일 중복 체크한다.
		 */
		private function checkOverlap(){
			if(file_exists($this->path."/".$this->file_unique_name) || $this->file_unique_name){
				return false;
				exit;
			}
			if(!$this->file_unique_name){
				$this->makeUniqueName();
			}
			return true;
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
		 * @param int $i
		 */
		private function rollback($i){
			if($i > 1){
				for($i=$i-1;$i>=0;$i--){
					$this->remove($this->files['re_name']);
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
	
			if(!$this->path) die("FileHandler->upload() :: 디렉토리 경로가 없습니다.");
	
			$file_input = $_FILES[$name];
	
			if(count($extension)<=0 || !is_array($extension)){
				$extension = array('jpg', 'jpeg', 'gif', 'png', 'bmp', 'zip', 'hwp', 'ppt', 'xls', 'doc', 'txt', 'pdf');
			}
	
			$this->extension = $extension;
			$this->extension_type = $extension_type;
			$this->limit_file_size = $limit_file_size;
	
			if(count($file_input["name"]) > 1) $files = $this->multipleUpload($file_input);
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
			if($file["size"] >= 0 && $file["name"]){
					
				// HTTP POST를 통하여 업로드 된 파일인지 체크
				if(!is_uploaded_file($file["tmp_name"])){
					echo "올바르게 업로드 되지 않았습니다.";
					$this->rollback($i);
					exit;
				}
					
				// 파일 확장자 체크
				$temps = $this->checkExtension($file["name"]);
				if( ($temps && !$this->extension_type) || (!$temps && $this->extension_type) ){
					echo "확장자 거부";
					$this->rollback($i);
					exit;
				}
					
				// 파일 사이즈 체크
				if(!$this->checkFileSize($file["size"])){
					echo "용량 너무큼";
					$this->rollback($i);
					exit;
				}
					
				// 오류 체크
				if(!$this->checkError($file["error"])){
					echo "오류";
					$this->rollback($i);
					exit;
				}
					
				// 파일 중복 체크
				if(!$this->checkOverlap()){
					echo "중복됨";
					$this->makeUniqueName();
				}
					
				if(!@move_uploaded_file($file["tmp_name"], KBOARD_WORDPRESS_ROOT . $this->path. "/" .$this->file_unique_name)){
					echo $file["tmp_name"] . " 파일 업로드 도중 오류가 발생함 : ". $this->path . "/" . $this->file_unique_name;
					$this->rollback($i);
					exit;
				}
					
				$files = array(
						"stored_name" => $this->file_unique_name,
						"original_name" => $file["name"],
						"temp_name" => $file["tmp_name"],
						"error" => $file["error"],
						"type" => $file["type"],
						"size" => $file["size"],
						"path" => $this->path
				);
			}
			return $files;
		}
	
		/**
		 * 다중 파일 업로드
		 * @param Files $file
		 */
		private function multipleUpload($file){
			for($i=0, $cnt=count($file["name"]); $i<$cnt; $i++){
				// 파일사이즈가 0인건 업로드 하지 않는다.
				if($file["size"][$i] >= 0 && $file["name"][$i]){
	
					// HTTP POST를 통하여 업로드 된 파일인지 체크
					if(!is_uploaded_file($file["tmp_name"][$i])){
						echo "올바르게 업로드 되지 않았습니다.";
						$this->rollback($i);
						exit;
					}
	
					// 파일 확장자 체크
					unset($this->file_extension);
					$temps = $this->checkExtension($file["name"][$i]);
					if( (!$temps && !$this->extension_type) || ($temps && $this->extension_type) ){
						echo "확장자 거부";
						$this->rollback($i);
						exit;
					}
	
					// 파일 사이즈 체크
					if(!$this->checkFileSize($file["size"][$i])){
						echo "용량 너무큼";
						$this->rollback($i);
						exit;
					}
	
					// 오류 체크
					if(!$this->checkError($file["error"][$i])){
						echo "오류";
						$this->rollback($i);
						exit;
					}
	
					// 파일 중복 체크
					if(!$this->checkOverlap()){
						echo "중복됨";
						$this->makeUniqueName();
					}
	
					if(!@move_uploaded_file($file["tmp_name"][$i], KBOARD_WORDPRESS_ROOT . $this->path. "/" .$this->file_unique_name)){
						echo $file["tmp_name"][$i] . " 파일 업로드 도중 오류가 발생함 : ". $this->path . "/" . $this->file_unique_name;
						$this->rollback($i);
						exit;
					}
	
					$files[$i] = array(
							"stored_name" => $this->file_unique_name,
							"original_name" => $file["name"][$i],
							"temp_name" => $file["tmp_name"][$i],
							"error" => $file["error"][$i],
							"type" => $file["type"][$i],
							"size" => $file["size"][$i],
							"path" => $this->path
					);
				}
			}
	
			return $files;
		}
	
		/**
		 * 파일을 다운로드 한다.
		 * @param string $file
		 */
		function download($file){
			// http://www.finalwebsites.com/forums/topic/php-file-download
			
			if(!$this->path) die("FileHandler->download() :: 디렉토리 경로가 없습니다.");
			$fullPath = KBOARD_WORDPRESS_ROOT . $this->path . '/' . $file;
			
			if($fd = fopen($fullPath, "r")){
				$fsize = filesize($fullPath);
				$path_parts = pathinfo($fullPath);
				$ext = strtolower($path_parts["extension"]);
				
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
			if(!$this->path) die("FileHandler->delete() :: 디렉토리 경로가 없습니다.");
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
	}
}
?>