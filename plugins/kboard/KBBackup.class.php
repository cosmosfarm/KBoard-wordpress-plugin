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
		$tables = array();
		$resource = mysql_query('SHOW TABLES');
		while($row = mysql_fetch_row($resource)){
			if(stristr($row[0], 'kboard')) $tables[] = $row[0];
		}
		return $tables;
	}
	
	/**
	 * 테이블 테이터를 생성한다.
	 * @param string $table
	 * @return string
	 */
	public function getData($table){
		$resource = mysql_query("SELECT * FROM `$table`");
		$num_fields = mysql_num_fields($resource);
		$create_table = mysql_fetch_row(mysql_query("SHOW CREATE TABLE `$table`"));
		
		$data .= "DROP TABLE `$table`;";
		$data .= "\n\n$create_table;\n\n";
		
		for($i = 0; $i<$num_fields; $i++){
			while($row = mysql_fetch_row($resource)){
				$data .= "INSERT INTO `$table` VALUES (";
				
				$value = array();
				for($j=0; $j<$num_fields; $j++){
					$row[$j] = addslashes($row[$j]);
					$row[$j] = str_replace("\n","\\n", $row[$j]);
					
					if($row[$j]) $value[] = "'$row[$j]'";
					else $value[] = "''";
				}
				$value = implode(',', $value);
				
				$data .= "$value);\n";
			}
		}
		
		$data .= "\n\n\n";
		
		return $data;
	}
}
?>