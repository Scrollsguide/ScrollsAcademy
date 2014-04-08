<?php
	abstract class Repository {
		
		private $database;
		
		public function __construct($database){
			$this->database = $database;
		}

		/**
		 * @return Database
		 */
		public function getDatabase(){
			return $this->database;
		}

		/**
		 * @return PDO
		 */
		public function getConnection(){
			return $this->getDatabase()->getConnection();
		}
		
		public abstract function getEntityName();

		public abstract function getTableName();
		
		// select all objects from db
		public function findAll(){
			$query = "SELECT * FROM " . $this->getTableName();
			
			$sth = $this->getConnection()->prepare($query);			
			$sth->execute();
			
			$resultSet = $sth->fetchAll(PDO::FETCH_CLASS, $this->getEntityName());
			
			return $resultSet;
		}
		
		public function findAllBy($column, $value, $limit = array()){
			$query = "SELECT *
						FROM " . $this->getTableName() . "
						WHERE " . $column . " = :value";
			
			if (isset($limit['count'])){
				$query .= " LIMIT ";
				if (isset($limit['offset'])){
					$query .= $limit['offset'] . ",";
				}
				$query .= $limit['count'];
			}
			
			$sth = $this->getConnection()->prepare($query);
			
			if (is_int($value)){
				$sth->bindParam(":value", $value, PDO::PARAM_INT);
			} else {
				$sth->bindParam(":value", $value, PDO::PARAM_STR);
			}
			
			$sth->execute();
			
			$resultSet = $sth->fetchAll(PDO::FETCH_CLASS, $this->getEntityName());
			
			return $resultSet;
		}
		
		public function findOneBy($column, $value){
			$singleItemList = $this->findAllBy($column, $value, array("count" => 1));
			// make sure just one item is returned
			return isset($singleItemList[0]) ? $singleItemList[0] : false;
		}
		
		/***************************************
		 * List of functions which are aliases *
		 * of findBy                           *
		 ***************************************/
		
		public function findOneById($id){
			// make extra sure it's int again
			return $this->findOneBy("id", (int)$id);
		}
		
	}