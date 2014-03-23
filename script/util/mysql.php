<?php
	// include_once("no_errors.php");

	function json_encode_strip($data) {
		return str_replace('\\\\','',json_encode($data));
	}

	function get_public_vars($obj) {
        return get_object_vars($obj);
	}

	class DataObject {
		private $table = "";
		private $dao;
		private $update;
		private $primary_key;
		private $primary_id;

		/**
		 * Create a new object for future insertion. Each argument is a value for a column in the database.
		 * @param DAO $dao a reference to a instance of DAO
		 * @param string $table the name of the table of this object
		 * @param array $assoc the associative array describing the properties of this object
		 * @return DataObject A new DataObject instance with the variables specified in $assoc which can
		 *	be committed to the table $table.
		 */
		static function create($dao, $table, $assoc) {
			$obj = new DataObject();
			$obj->table = $table;
			$obj->dao = $dao;//Reference to the dao stored
			$obj->update = false;//This will be inserted on commit

			foreach ($assoc as $key => $arg) {
				$obj->{$key} = $dao->escape($arg);
			}

			return $obj;
		}

		/**
		 * Select a single object from the database. Every argument will match a value in a column in the database.
		 * @param DAO $dao a reference to a instance of DAO
		 * @param string $table the name of the table of this object
		 * @param array $keys the associative array naming the properties of this object for selection
		 * @param array $where the associative array describing the properties of this object (used in the WHERE clause)
		 * @return DataObject A new DataObject instance with the variables specified in $assoc which can
		 *	be committed to the table $table.
		 */
		static function select_one($dao, $table, $keys, $where) {
			$obj = new DataObject();
			$obj->table = $table;
			$obj->dao = $dao;//Reference to the dao stored
			$obj->update = true;//This will be updated on commit

			$query_where = $obj->key_values($where);
			$query_part = implode(",",$keys);
			$query = "SELECT ".$query_part." FROM ".$table." WHERE ".implode(" AND ",$query_where).";";

			$dao->myquery($query);

			$query_obj = $dao->fetch_one_part($keys);

			if ($query_obj) {
				foreach ($keys as $key) {
					$obj->{$key} = $query_obj[$key];
				}
				$obj->determine_primary();
			} else {
				$obj = NULL;
			}
			return $obj;
		}

		/**
		 * Select all objects from the database where the WHERE clause is entirely true.
		 * Every argument will match a value in a column in the database.
		 * @param DAO $dao a reference to a instance of DAO
		 * @param string $table the name of the table of the objects
		 * @param array $keys the associative array naming the properties of these objects for selection
		 * @param array $where the associative array describing the properties of these objects (used in the WHERE clause)
		 * @return array An array of DataObject instances with the variables specified in $assoc which can
		 *	be committed to the table $table.
		 */
		static function select_all($dao, $table, $keys, $where) {
			$obj = new DataObject();
			$obj->table = $table;
			$obj->dao = $dao;//Reference to the dao stored
			$obj->update = true;//This will be updated on commit

			$objects = array();

			$query_where = $obj->key_values($where);
			$query_part = implode(",",$keys);
			$query = "SELECT ".$query_part." FROM ".$table." WHERE ".implode(" AND ",$query_where)." ORDER BY ".$keys[0]." DESC;";

			$dao->myquery($query);

			$query_objects = $dao->fetch_all_part($keys);
			//determine primary key and value
			$dao->myquery("SHOW index FROM $obj->table where Key_name = 'PRIMARY';");
			// var_dump($dao->fetch_one_obj());
			$obj->primary_key = $dao->fetch_one_obj()->Column_name;

			foreach ($query_objects as $query_obj) {
				$new_obj = clone $obj;//Copy the default obj

				foreach ($keys as $key) {
					$new_obj->{$key} = $query_obj[$key];
				}

				$new_obj->primary_id = $new_obj->{$new_obj->primary_key};

				$objects[] = $new_obj;
			}
			return $objects;
		}

		/**
		 * Same as select_all but encodes the result into json format.
		 */
		static function select_all_json($dao, $table, $keys, $where) {
			return json_encode_strip(DataObject::select_all($dao, $table, $keys, $where));
		}

		function get_vars() {
	        return get_public_vars($this);
		}

		private function __construct() {

		}

		function get_primary_id() {
			return $this->primary_id;
		}

		function format_json() {
			return json_encode_strip($this->get_vars());
		}

		function key_values($assoc) {
			$query_where = array();
			foreach ($assoc as $key => $value) {
				$query_where[] = $key."=\"".$this->dao->escape($value)."\"";//Escape and surround with quotes
			}
			return $query_where;
		}

		function determine_primary() {
			//determine primary key and value
			$this->dao->myquery("SHOW index FROM $this->table where Key_name = 'PRIMARY';");
			// var_dump($this->dao->fetch_one_obj());
			$this->primary_key = $this->dao->fetch_one_obj()->Column_name;
			if (isset($this->{$this->primary_key})) {
				$this->primary_id = $this->{$this->primary_key};
			}
		} 
		
		//Commit this object to the database
		function commit() {
			$vars = DataObject::get_vars($this);

			unset($vars[$this->primary_key]);//Do not update the primary key!

			$object_vars = $this->key_values($vars);

			if ($this->update) {
				$result = $this->dao->myquery("UPDATE $this->table SET ".implode(",",$object_vars)." WHERE ".$this->primary_key."=".$this->primary_id.";");
			} else {
				$result = $this->dao->myquery("INSERT INTO $this->table SET ".implode(",",$object_vars).";");
				DataObject::determine_primary($this);
				$this->{$this->primary_key} = $this->dao->insert_id();
				$this->primary_id = $this->{$this->primary_key};
			}

			$this->update = true;
			return $result;
		}

		//Delete this object from the database. Any subsequent commits will attempt to re-insert it.
		function delete() {
			$result = $this->dao->myquery("DELETE FROM $this->table WHERE ".$this->primary_key."=".$this->primary_id.";");
			$this->update = false;
			return $result;
		}
	}

	//Database access object
	class DAO {
		private $result = "hello";
		private $debug = false;
		
		function __construct($debug = false) {
			$this->debug = $debug;
			$this->myi = new mysqli("localhost", "lukebarn_uniuser", "xkN-u8E-kGf-4RD", "lukebarn_unify");
		}
		
		function escape($s) {
			$r = "".rand();
			return str_replace($r, "<br>", mysqli_real_escape_string($this->myi, str_replace("<br>", $r, $s)));
		}
	
		function myquery($query) {
			if($this->debug) {
				echo "Query: $query<br>";
			}
			$this->result = false;
			$this->result = $this->myi->query($query);
			if($this->debug) {
				echo $this->myi->error."<br>";
			}
			return $this->result;
		}
		
		function success() {
			if ($this->result) {
				return true;
			} else {
				return false;
			}
		}
		
		function fetch_all() {
			if ($this->result) {
				$rows = array();
				while ($row = $this->result->fetch_assoc()) {
					$rows[] = $row;
				}
				return $rows;
			} else {
				return array();
			}
		}

		function fetch_all_part($part) {
			if ($this->result) {
				$rows = array();
				while ($row = $this->fetch_one_part($part)) {
					$rows[] = $row;
				}
				return $rows;
			} else {
				return array();
			}
		}
		
		function fetch_all_obj() {
			if ($this->result) {
				$rows = array();
				while ($row = $this->fetch_one_obj()) {
					$rows[] = $row;
				}
				return $rows;
			} else {
				return array();
			}
		}

		function fetch_all_obj_part($part) {
			if ($this->result) {
				$rows = array();
				while ($row = $this->fetch_one_obj_part($part)) {
					$rows[] = $row;
				}
				return $rows;
			} else {
				return array();
			}
		}

		function fetch_json() {
			if ($this->result) {
				return json_encode_strip($this->fetch_all());
			} else {
				return "{}";
			}
		}

		function fetch_json_part($part) {
			if ($this->result) {
				return json_encode_strip($this->fetch_all_part($part));
			} else {
				return "{}";
			}
		}
		
		function fetch_one() {
			if ($this->result) {
				return $this->result->fetch_assoc();
			} else {
				return;
			}
		}

		//Where part is an array of the keys for each part
		function fetch_one_part($part) {
			if ($this->result) {
				$obj = $this->result->fetch_assoc();
				if ($obj) {
					$new_obj = array();
					foreach ($part as $column) {
						if (array_key_exists($column, $obj)) {
							$new_obj[$column] = $obj[$column];
						} else {
							throw new Exception("Column '$column' does not exist in ");
							return;
						}
					}
					return $new_obj;
				} else {
					return;
				}
			} else {
				return;
			}
		}

		function fetch_one_obj() {
			if ($this->result) {
				return $this->result->fetch_object();
			} else {
				return;
			}
		}

		//Where part is an array of the keys for each part
		function fetch_one_obj_part($part) {
			$assoc = $this->fetch_one_part($part);
			if ($assoc) {
				return (object)$assoc;
			} else {
				return;
			}
		}
		
		function fetch_num_rows() {
			if ($this->result) {
				return $this->result->num_rows;
			} else {
				return 0;
			}
		}

		function insert_id() {
			return $this->myi->insert_id;
		}
	
		function __destruct() {
			mysqli_close($this->myi);
		}
	}
?>