<?php
include_once('Config.Inc.php');
class DB{ 
	private $dbh = null;
	public $dsn = null;
	function __construct(){
		$dsn = 'mysql:dbname='.DATABASE_NAME.';host='.DATABASE_HOST;
		$user = root;
		$password = DATABASE_PASSWORD;
		
		try {
		    $this->dbh = new PDO($dsn, $user, $password);
		} catch (PDOException $e) {
		    echo 'Connection failed: ' . $e->getMessage();
		}
	

	}
	
	function Insert($array_values, $table_name){
		$Sql = "INSERT INTO $table_name SET ";
				foreach($array_values as $index=>$value){
					$value = mysql_escape_string($value);
					$Sql .= " `$index`='$value',";
				}
		$Sql = substr($Sql, 0, strlen($Sql)-1);
		
		
		$sth = $this->dbh->prepare($Sql);
		$sth->execute();
		return $this->dbh->lastInsertId();
		
	}
	
	function Show($table_name, $where_clause = array(), $oder_clause=""){
		$Sql = "SELECT * FROM $table_name WHERE 1";
		foreach($where_clause as $index=>$value){
			$value = mysql_escape_string($value);
			$Sql .= " AND `$index`='$value' ";
		}
		//echo $Sql ;
		$Sql .= $oder_clause;
		
		$sth = $this->dbh->prepare($Sql);
		$sth->execute();
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
                return $result ;
		
	}
	
	function Update($array_values, $table_name, $where_clause = array()){
		$Sql = "UPDATE $table_name SET ";
		foreach($array_values as $index=>$value){
			$value = mysql_escape_string($value);
			$Sql .= " `$index`='$value',";
		}
		$Sql = substr($Sql, 0, strlen($Sql)-1);
		$Sql .= " WHERE 1 "; 
		foreach($where_clause as $index=>$value){
			$value = mysql_escape_string($value);
			$Sql .= " AND `$index`='$value' ";
		}
		//echo $Sql."<hr>";

		$sth = $this->dbh->prepare($Sql);
		 $sth->execute();
	}
	
	function Remove($table_name, $where_clause = array()){
		$Sql = "DELETE FROM $table_name WHERE 1 ";
		foreach($where_clause as $index=>$value){
			$value = mysql_escape_string($value);
			$Sql .= " AND `$index`='$value' ";
		}
		//echo $Sql."<hr>";
		$sth = $this->dbh->prepare($Sql);
		 $sth->execute();
	
	}
        //--- for media management..
        function execute_query($sql){ 
		 
		$sth = $this->dbh->prepare($sql);
		 $sth->execute();
		 return $sth;
	} 
}

?>

