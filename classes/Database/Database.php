<?php

	class Database {

		private $app;

		private $username;
		private $password;
		private $databaseName;
		private $host;
		private $port;

		private $connection;

		public function __construct(App $app) {
			$this->app = $app;
		}

		public function setCredentials($username, $password) {
			$this->username = $username;
			$this->password = $password;
		}

		public function setHost($host, $port) {
			$this->host = $host;
			$this->port = $port;
		}

		public function setDatabaseName($dbName) {
			$this->databaseName = $dbName;
		}

		/**
		 * @return PDOExt
		 */
		public function getConnection() {
			if ($this->connection === null) {
				$this->connect();
			}

			return $this->connection;
		}

		private function connect() {
			try {
				$this->connection = new PDOExt(sprintf("mysql:host=%s;port=%d;dbname=%s", $this->host,
					$this->port, $this->databaseName), $this->username, $this->password);

				if (!Debug::started()) {
					// suppress errors
					$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
				}
			} catch (PDOException $e) {
				throw new Exception("Database error.");
			}
		}

		public function close() {
			$this->connection = null;
		}

	}

	class PDOExt extends PDO {

		public function prepare($statement, $driver_options = array()) {
			Debug::addLine(new SQLDebugLine($statement));

			return parent::prepare($statement, $driver_options);
		}
	}
