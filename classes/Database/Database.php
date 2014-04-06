<?php
	class Database {
		
		private $app;
		
		private $username;
		private $password;
		private $database;
		private $host;
		private $port;
		
		private $connection;
		
		public function __construct(App $app){
			$this->app = $app;
		}
		
		public function setCredentials($username, $password){
			$this->username = $username;
			$this->password = $password;
		}
		
		public function setHost($host, $port){
			$this->host = $host;
			$this->port = $port;
		}
		
		public function setDatabaseName($dbName){
			$this->databaseName = $dbName;
		}
		
		public function getConnection(){
			if ($this->connection === null){
				$this->connect();
			}
			return $this->connection;
		}
		
		private function connect(){
			try {
				$this->connection = new PDO(sprintf("mysql:host=%s;port=%d;dbname=%s", $this->host, 
					$this->port, $this->databaseName), $this->username, $this->password);
					
					// suppress errors
					$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
			} catch (PDOException $e){
				throw new Exception("Database error.");
			}
		}
		
		public function close(){
			$this->connection = null;
		}
		
	}