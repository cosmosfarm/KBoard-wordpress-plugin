<?php
/**
 * KBoard 데이터 백업 및 복구
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBBackup {
	
	public function __construct(){
		set_time_limit(0);
	}
	
	/**
	 * KBoard 테이블 목록을 반환한다.
	 * @return array
	 */
	public function getTables(){
		$tables = array();
		$resource = kboard_query('SHOW TABLES');
		while($row = mysql_fetch_row($resource)){
			if(stristr($row[0], KBOARD_DB_PREFIX.'kboard_')) $tables[] = $row[0];
		}
		return $tables;
	}
	
	/**
	 * 테이블 sql 테이터를 생성한다.
	 * @param string $table
	 * @return string
	 */
	public function getSql($table){
		$resource = kboard_query("SELECT * FROM `$table`");
		
		$sql = "TRUNCATE TABLE `$table`;\n";
		while($row = mysql_fetch_row($resource)){
			$columns = count($row);
			$value = array();
			$sql .= "INSERT INTO `$table` VALUE (";
			for($i=0; $i<$columns; $i++){
				if($row[$i]) $value[] = "'$row[$i]'";
				else $value[] = "''";
			}
			$value = implode(',', $value);
		
			$sql .= "$value);\n";
		}
		return $sql;
	}
	
	/**
	 * 테이블 xml 테이터를 생성한다.
	 * @param string $table
	 * @return string
	 */
	public function getXml($table){
		$resource = kboard_query("SELECT * FROM `$table`");
		
		$xml .= "<$table>\n";
		while($row = mysql_fetch_assoc($resource)){
			$xml .= "\t<data>\n";
		
			$value = array();
			foreach($row AS $key => $value){
				$xml .= "\t\t<$key>";
				$xml .= "<![CDATA[".stripslashes($value)."]]>";
				$xml .= "</$key>\n";
			}
		
			$xml .= "\t</data>\n";
		}
		$xml .= "</$table>\n";
		return $xml;
	}
	
	/**
	 * 데이터 파일을 다운로드 받는다.
	 * @param string $data
	 * @param string $file
	 * @param string $filename
	 */
	public function download($data, $file='xml', $filename=''){
		if(!$filename) $filename = 'KBoard-Backup-'.date("Ymd").'.'.$file;
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"".$filename."\"");
		header("Pragma: no-cache");
		Header("Expires: 0");
		if($file == 'xml'){
			echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
			echo "<kboard>\n";
			echo $data;
			echo "</kboard>";
		}
		else{
			echo $data;
		}
		exit;
	}
	
	/**
	 * XML 복원파일을 입력받아 기존 데이터를 비우고 DB에 입력한다.
	 * @param string $file
	 */
	public function importXml($file){
		include 'XML2Array.class.php';
		$xml = file_get_contents($file);
		$array = XML2Array::createArray($xml);
		
		foreach($array['kboard'] AS $table => $rows){
			
			// 테이블에 입력될 데이터가 한 개인지 여러개 인지 확인한다.
			if(is_array($rows['data'])){
				$keys = array_keys($rows['data']);
				if(reset($keys) == '0') $data = $rows['data'];
				else $data = $rows;
			}
			else{
				$data = $rows;
			}
			
			if($data){
				kboard_query("TRUNCATE TABLE `$table`");
				if(stristr($table, 'kboard_board_content')) kboard_query("DELETE FROM `".KBOARD_DB_PREFIX."posts` WHERE post_type='kboard'");
				
				foreach($data AS $key => $row){
					$keys = array_keys($row);
					$row_count = count($row);
					
					$columns = array();
					for($i=0; $i<$row_count; $i++){
						$columns[] = "`$keys[$i]`";
					}
					$columns = implode(',', $columns);
					
					$value = array();
					for($i=0; $i<$row_count; $i++){
						$value[] = "'".addslashes($row[$keys[$i]]['@cdata'])."'";
					}
					$value = implode(',', $value);
					
					kboard_query("INSERT INTO `$table` ($columns) VALUE ($value)");
					
					/*
					 * search 값이 있을경우 post 테이블에 내용을 입력한다.
					 */
					if($row['search']['@cdata']==1 || $row['search']['@cdata']==2){
						$insert_id = mysql_insert_id();
						if(!$insert_id) list($insert_id) = mysql_fetch_row(kboard_query("SELECT LAST_INSERT_ID()"));
						
						if($insert_id){
							$kboard_post = array(
								'post_author'   => $row['member_uid']['@cdata'],
								'post_title'    => addslashes($row['title']['@cdata']),
								'post_content'  => addslashes(($row['secret']['@cdata']=='true' || $row['search']['@cdata']==2)?'':$row['content']['@cdata']),
								'post_status'   => 'publish',
								'comment_status'=> 'closed',
								'ping_status'   => 'closed',
								'post_name'     => $insert_id,
								'post_parent'   => $row['board_id']['@cdata'],
								'post_type'     => 'kboard'
							);
							wp_insert_post($kboard_post, true);
						}
					}
				} // end foreach
			}
		}
	}
}
?>