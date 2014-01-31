<?php

class Dbactions{
	
	/* Variables for DB Connect */
	private $db_host = "localhost";														// Hostname
	private $db_user = "root";   														// Username
	private $db_pass = "";   															// Password
	private $db_name = "test";	 														// Database
	
	
	/* Extra variables that are required by other functions */
	private $con = false; 																// Checks connection is active
	private $result = array(); 															// Returns the query result
    private $myQuery = "";																// Process SQL Query
    private $numResults = "";															// Returns the number of rows
	
	
	/* Function to make connection to database */
	public function __construct(){
		if(!$this->con){
			$myconn = mysql_connect($this->db_host,$this->db_user,$this->db_pass);  	// mysql_connect() with variables defined at the start of Database class
            if($myconn){
            	$seldb = mysql_select_db($this->db_name,$myconn); 						// Credentials have been pass through mysql_connect() now select the database
                if($seldb){
                	$this->con = true;
                    return true;  														// Connection has been made return TRUE
                }else{
                	array_push($this->result,mysql_error()); 
                    return false;  														// Problem selecting database return FALSE
                }  
            }else{
            	array_push($this->result,mysql_error());
                return false; 															// Problem connecting return FALSE
            }  
        }else{  
            return true; 																// Connection has already been made return TRUE 
        }  	
	}
	
	/* Function to disconnect from the database */
    public function disconnect(){
    	if($this->con){
    		if(mysql_close()){															// We have found a connection, try to close it
    			$this->con = false;														// We have successfully closed the connection, set the connection variable to false
				return true;
			}else{
				return false;															// We could not close the connection, return false
			}
		}
    }
	
	/* Function to process an sql query */
	public function sql($sql){
		$query = mysql_query($sql);
        $this->myQuery = $sql; 															
		if($query){																		// If the query returns >= 1 assign the number of rows to numResults
			$this->numResults = mysql_num_rows($query);	
			for($i = 0; $i < $this->numResults; $i++){									// Loop through the query results by the number of rows returned
				$r = mysql_fetch_array($query);
               	$key = array_keys($r);
               	for($x = 0; $x < count($key); $x++){																			
                   	if(!is_int($key[$x])){												// Sanitizes keys so only alphavalues are allowed
                   		if(mysql_num_rows($query) >= 1){
                   			$this->result[$i][$key[$x]] = $r[$key[$x]];
						}else{
							$this->result = null;
						}
					}
				}
			}
			return true; 																// Query was successful
		}else{
			array_push($this->result,mysql_error());
			return false; 																// No rows where returned
		}
	}
	
	/* Private function to check if table exists for use with queries */
	private function tableExists($table){
		$tablesInDb = mysql_query('SHOW TABLES FROM '.$this->db_name.' LIKE "'.$table.'"');
        if($tablesInDb){
        	if(mysql_num_rows($tablesInDb)==1){
                return true; // The table exists
            }else{
            	array_push($this->result,$table." does not exist in this database");
                return false; // The table does not exist
            }
        }
    }
	
	/* Function to insert into the database */
    public function insert($table,$params=array()){
    	if($this->tableExists($table)){													// If the table exists
    	 	$sql='INSERT INTO `'.$table.'` (`'.implode('`, `',array_keys($params)).'`) VALUES (\'' . implode('\', \'', $params) . '\')';
            $this->myQuery = $sql; 
            
            if($ins = @mysql_query($sql)){
            	array_push($this->result,mysql_insert_id());
                return true; 															// The data has been inserted
            }else{
            	array_push($this->result,mysql_error());
                return false; 															// The data has not been inserted
            }
        }else{
        	return false; 																// Table does not exist
        }
    }
	
	/* Function to SELECT from the database */
	public function select($table, $columns = '*', $join = null, $where = null, $order = null, $limit = null){
		$q = 'SELECT '.$columns.' FROM '.$table;
		if($join != null){
			$q .= ' JOIN '.$join;
		}
        if($where != null){
        	$q .= ' WHERE '.$where;
		}
        if($order != null){
            $q .= ' ORDER BY '.$order;
		}
        if($limit != null){
            $q .= ' LIMIT '.$limit;
        }
        $this->myQuery = $q; 
		
        if($this->tableExists($table)){													// If the table exists
        	$query = mysql_query($q);
			if($query){																	// If the query returns >= 1 assign the number of rows to numResults
				$this->numResults = mysql_num_rows($query);
				while( $results[] = mysql_fetch_object($query));
				array_pop ( $results );
				$this->result = $results;
				return true; 															// Query was successful
			}else{
				array_push($this->result,mysql_error());
				return false; 															// No rows where returned
			}
      	}else{
      		return false; 																// Table does not exist
    	}
    }
	
	/* Function to update row in database */
    public function update($table,$params=array(),$where){
    	if($this->tableExists($table)){													// If the table exists
    		$args=array();
			foreach($params as $field=>$value){
				$args[]=$field.'="'.$value.'"';											// Seperate each column out with it's corresponding value
			}
			
			$sql='UPDATE '.$table.' SET '.implode(',',$args).' WHERE '.$where;			// Create the query
			
            $this->myQuery = $sql; 
            if($query = mysql_query($sql)){
            	array_push($this->result,mysql_affected_rows());
            	return true; 															// Update has been successful
            }else{
            	array_push($this->result,mysql_error());
                return false; 															// Update has not been successful
            }
        }else{
            return false; 																// The table does not exist
        }
    }
	
	/* Function to delete table or row(s) from database */
    public function delete($table,$where = null){
    	if($this->tableExists($table)){													// If the table exists
    	 	
    	 	if($where == null){
                $delete = 'DELETE '.$table; 											// Create query to delete table
            }else{
                $delete = 'DELETE FROM '.$table.' WHERE '.$where; 						// Create query to delete rows
            }
            
            if($del = mysql_query($delete)){
            	array_push($this->result,mysql_affected_rows());
                $this->myQuery = $delete; 
                return true; 															// The query exectued correctly
            }else{
            	array_push($this->result,mysql_error());
               	return false; 															// The query did not execute correctly
            }
        }else{
            return false; 																// The table does not exist
        }
    }
	
	
	/* Function to import csv data into mysql databse */
	public function csv_import($file_name, $table_name, $columns=array(), $file_heading){
		
		$field_separate_char = ",";													// separation character 	
		$field_enclose_char  = "\"";												// enclose character
		$field_escape_char   = "\\";												// escape character
		
		if(!empty($columns)){
		
			if($this->tableExists($table_name)){											// If table exists
			
				$sql = "LOAD DATA INFILE '".@mysql_escape_string($file_name).
				 "' INTO TABLE `".$table_name.
				 "` FIELDS TERMINATED BY '".@mysql_escape_string($field_separate_char).
				 "' OPTIONALLY ENCLOSED BY '".@mysql_escape_string($field_enclose_char).
				 "' ESCAPED BY '".@mysql_escape_string($field_escape_char).
				 "' ".
				 ($file_heading ? " IGNORE 1 LINES " : "")
				 ."(`".implode("`,`", $columns)."`)";
				
				$res = @mysql_query($sql);
				return $res;
			
			} else {																		// The table does not exist																		
				return false;
			}
			
		} else {
			return false;
		}
	}
	
	/* Function to export csv from mysql database */
	public function csv_export($table, $columns = '*', $join = null, $where = null, $order = null){
		$q = 'SELECT '.$columns.' FROM '.$table;
		if($join != null){
			$q .= ' JOIN '.$join;
		}
        if($where != null){
        	$q .= ' WHERE '.$where;
		}
        if($order != null){
            $q .= ' ORDER BY '.$order;
		}
        $this->myQuery = $q; 
		
        if($this->tableExists($table)){													// If the table exists
        	
			function gen_csv($fields)
			{
				$separator = '';
				foreach ($fields as $field) {
						if (preg_match('/\\r|\\n|,|"/', $field)) {
								$field = '"' . str_replace('"', '""', $field) . '"';
						}
						echo $separator . $field;
						$separator = ',';
				}
				echo "\r\n";
			}
	
			$query = @mysql_query($q);
			if($query){																	// If the query returns >= 1 assign the number of rows to numResults
				
				//Following headers instruct the browser to treat the data as a csv file called export.csv
				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename=data.csv');

				$row = mysql_fetch_assoc($query);
				if ($row) {
					gen_csv(array_keys($row));
					while ($row) {
						gen_csv($row);
						$row = mysql_fetch_assoc($query);
					}
					exit;
				}
				
			} else {
				return false;
			}	
		} else {
			return false;
		}
	}
	
	
	/* Public function to return the data to the user */
    public function getResult(){
		$val = $this->result;
		$this->result = array();
        return $val;
    }

    /* Pass the SQL back for debugging */
    public function getSql(){
        $val = $this->myQuery;
        $this->myQuery = array();
        return $val;
    }

    /* Pass the number of rows back */
    public function numRows(){
        $val = $this->numResults;
        $this->numResults = array();
        return $val;
    }
	
}

$sadb = new Dbactions(); 
	
?>
