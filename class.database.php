<?php
/*
=====================================
 Database Class by Kyle Ouellette
 Used to simplify database access
 methods include:
=====================================
 updated 12/18/2010 	 
=====================================
 */
class Database{

	public $error;
	
	protected $conn,
				$query,
				$where,
				$affected,
				$limit,
				$order,
				$order_type;
	
	function __construct($h=null, $u=null, $p=null, $n=null){
		
		//if passed null arguements, will check for defined db_h, db_u, db_p, and db_n for connectivity
		if($h == null && defined('DB_H')){
			$h = DB_H;
		}
		if($u == null && defined('DB_U')){
			$u = DB_U;
		}
		if($p == null && defined('DB_P')){
			$p = DB_P;
		}
		if($n == null && defined('DB_N')){
			$n = DB_N;
		}
		
		$this->conn = new mysqli($h, $u, $p, $n) or die('Connection Error');
		return;
	}
	
	//method for entering a custom query
	public function custom($q){
		$this->query = $q;
		$res = $this->conn->query($this->query) or $this->error = $this->conn->error;
		$this->affected = $this->conn->affected_rows;
		if(preg_match('/^(SELECT)/i', $q)){
			$r = $this->_fetch($res);
			return $r;
		}else{
			return $res;
		}

	}
	
	//Method used to get data from database	
	function get($table, $what='*', $where=null){
		
		$this->query = "SELECT $what FROM $table";
		
		if($where != null){
			$this->where($where);
		}
		
		if(!empty($this->order)){$this->query .= ' order by '.$this->order;}

		if(!empty($this->order) && !empty($this->order_type)){$this->query .= ' '.$this->order_type;}

		if(!empty($this->limit)){$this->query .= ' limit '.$this->limit;}
		
		$res = $this->conn->query($this->query) or $this->error = $this->conn->error;
		$this->affected = $this->conn->affected_rows;
		if($res){
			$r = $this->_fetch($res);
			return $r;
		}else{
			return false;
		}
	}
	
	//method used to pull results into an array
	protected function _fetch($results){
	$limit = $this->limit;
	
		if($results){
			if(!preg_match('/limit 1(\'|\")?$/i', $this->query)){
				while($r = $results->fetch_object()){
					$ret[] = (Array)$r;
				}
			}else{
				while($r = $results->fetch_object()){
					$ret = (Array)$r;
				}			
			}
			return $ret;
		}else{
			return;
		}
	}
	
	//Method used to generate the appropriate string 
	//for adding 'where' to a query
	//could be mashed with build_query
	protected function where($arr){
		$this->query .= ' WHERE';
		$keys = array_keys($arr);
		$vals = array_values($arr);
		$total = count($arr);
		$addon = '';

		if($total > 1){
			while($total-1 != 0){
		
				$k = $keys[$total-1]; $v = $vals[$total-1];
		
				$addon .= " $k='$v' AND";
				$total--;
			}
	
			$k = $keys[$total-1]; $v = $vals[$total-1];
	
			$addon .= " $k='$v'";
		}else{
			$k = $keys[0]; $v = $vals[0];
			$addon .= " $k='$v'";
		}

		$this->query .= $addon;
		return;
	}

	//insert method used to insert data into the database
	function insert($table, $arr){
		$this->query = "INSERT INTO $table SET ";
		$this->build_query($arr);
		$res = $this->conn->query($this->query) or $this->error = $this->conn->error;
		$this->affected = $this->conn->affected_rows;
		return $res;
	}
	
	//builds the query for the insert method...
	//could be mashed with where method
	private function build_query($arr){
		$count = count($arr);
		$keys = array_keys($arr);
		$vals = array_values($arr);
		$addOn = '';
		$i = $count-1;
			
		while($i >= 0){
			$k = $keys[$i]; $v = $vals[$i];
			if($i != 0){
				$addOn .= "$k='$v', ";
			}else{
				$addOn .= "$k='$v'";
			}
			$i--;
		}
		$this->query .= $addOn;
		return;
		
	}
	
	//method for updating database
	public function update($table, $oldData, $newData){
		$this->query = "UPDATE $table SET ";
		$this->build_query($newData);
		$this->where($oldData);
		if(!empty($this->limit)){$this->query .= ' limit '.$this->limit;}
		$res = $this->conn->query($this->query) or $this->error = $this->conn->error;
		$this->affected = $this->conn->affected_rows;
		if($res){return true;}else{return false;}
	}
	
	//method for deleting from the database
	public function delete($table, $arr){
		$this->query = 'DELETE FROM '.$table;
		$this->where($arr);
		if(!empty($this->limit)){$this->query .= ' limit '.$this->limit;}
		$res = $this->conn->query($this->query) or $this->error = $this->conn->error;
		$this->affected = $this->conn->affected_rows;
		if($res){return true;}else{return false;}
	}
	
	public function truncate($tbl){
		$this->query = "TRUNCATE $tbl";
		$this->conn->query($this->query) or $this->error = $this->conn->error;
		return;
	}
	
	//function for displaying query :: Debugging purposes only!
	public function display_query(){
		echo '<p>'.$this->query.'</p>';
	}
	
	//method for getting rows affected
	public function rows(){
		return $this->affected;
	}
	
	/*---------------Methods for setting limit, unsetting limit, setting order, setting order by----------------*/
	public function limit($l){
		$this->limit = $l;
		return;
	}
	
	public function unlimit(){
		unset($this->limit);
		return;
	}
	
	public function order($order, $type = 'ASC'){
		$this->order = $order;
		$this->order_type = $type;
		return;
	}
	/*--------------------------------------END-----------------------------------------*/
		
	function __destruct(){
		$this->conn->close();
		return;
	}
	
}