<?php
/*
	$config = parse_ini_file('../../config.ini'); 
			
	echo $config['host'];
	echo $config['username'];
	echo $config['password'];
	echo $config['dbname'];
*/
	include 'Db.php';;
	
    $db = new Db();
    
	$result = $db -> query("SELECT * FROM talla");
					
	$row_cnt = $result->num_rows;
	if($row_cnt > 0)
	{
		for ($i = 1; $i <= $row_cnt; $i++) {
			$row = $result->fetch_array(MYSQLI_NUM);
			
			echo $row[1];
		}
	}
?>