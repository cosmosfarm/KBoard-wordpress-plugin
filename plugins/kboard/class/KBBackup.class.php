<?php
/**
 * KBoard 데이터 백업 및 복구
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBBackup {
	
	/**
	 * KBoard 테이블 목록을 반환한다.
	 * @return array
	 */
	public function getTables(){
		global $wpdb;
		$tables_result = $wpdb->get_results('SHOW TABLES', ARRAY_N);
		foreach($tables_result as $table){
			if(strpos($table[0], $wpdb->prefix.'kboard_') !== false) $tables[] = $table[0];
		}
		return isset($tables)?$tables:array();
	}
	
	/**
	 * 테이블 sql 테이터를 생성한다.
	 * @param string $table
	 * @return string
	 */
	public function getSql($table){
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM `$table`", ARRAY_N);
		$sql = "TRUNCATE TABLE `$table`;\n";
		foreach($result as $row){
			$columns = count($row);
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
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM `$table`", ARRAY_A);
		
		// 테이블 이름에서 PREFIX를 지운다.
		$table = str_replace($wpdb->prefix, '', $table);
		$xml = "<$table>\n";
		foreach($result as $row){
			$xml .= "\t<data>\n";
			
			foreach($row as $key => $value){
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
		header("Content-Type: ".kboard_mime_type($filename));
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
		global $wpdb;
		include 'XML2Array.class.php';
		$xml = file_get_contents($file);
		$xml = trim($xml);
		$array = XML2Array::createArray($xml);
		
		foreach($array['kboard'] as $table=>$rows){
			
			// 테이블에 입력될 데이터가 한 개인지 여러개 인지 확인한다.
			if(isset($rows['data']) && is_array($rows['data'])){
				$keys = array_keys($rows['data']);
				if(reset($keys) == '0') $data = $rows['data'];
				else $data = $rows;
			}
			else{
				$data = $rows;
			}
			
			if($data){
				// 테이블 이름에 PREFIX를 추가 한다.
				$table = $wpdb->prefix.$table;
				
				// 새로 생성될 테이블을 비운다.
				$wpdb->query("TRUNCATE TABLE `$table`");
				
				// 새로운 content를 입력하기 위해서 posts테이블에 입력된 content를 삭제한다.
				if(stristr($table, 'kboard_board_content')) $wpdb->query("DELETE FROM `{$wpdb->prefix}posts` WHERE `post_type`='kboard'");
				
				foreach($data as $key=>$row){
					$keys = array_keys($row);
					$row_count = count($row);
					
					$columns = array();
					for($i=0; $i<$row_count; $i++){
						$columns[] = "`$keys[$i]`";
					}
					$columns = implode(',', $columns);
					
					$value = array();
					for($i=0; $i<$row_count; $i++){
						$value[] = "'".esc_sql($row[$keys[$i]]['@cdata'])."'";
					}
					$value = implode(',', $value);
					
					$wpdb->query("INSERT INTO `$table` ($columns) VALUES ($value)");
					
					/*
					 * search 값이 있을경우 post 테이블에 내용을 입력한다.
					 */
					if(isset($row['search']) && ($row['search']['@cdata']==1 || $row['search']['@cdata']==2)){
						if($wpdb->insert_id){
							$kboard_post = array(
								'post_author'   => $row['member_uid']['@cdata'],
								'post_title'    => esc_sql($row['title']['@cdata']),
								'post_content'  => esc_sql(($row['secret']['@cdata']=='true' || $row['search']['@cdata']==2)?'':$row['content']['@cdata']),
								'post_status'   => 'publish',
								'comment_status'=> 'closed',
								'ping_status'   => 'closed',
								'post_name'     => $wpdb->insert_id,
								'post_parent'   => $row['board_id']['@cdata'],
								'post_type'     => 'kboard'
							);
							wp_insert_post($kboard_post, true);
						}
					}
				} // end foreach
			}
		} // end foreach
	}
}
?>