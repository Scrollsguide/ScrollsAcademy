<?php
	class Session {
	
		private $user;
	
		private static $instance; 
	
		private function __construct(){
			session_start();
			$this->user = User::fromSession($_SESSION);
		}
		
		public function getUser(){
			return $this->user;
		}
		
		public static function getInstance(){
			if (!isset(static::$instance)){
				static::$instance = new static();
			}
			
			return static::$instance;
		}
		
	}