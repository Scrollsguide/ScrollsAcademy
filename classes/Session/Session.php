<?php
	class Session {
	
		private $user;
		
		private $flashbag;
	
		private static $instance; 
	
		private function __construct(){
			session_start();
			$this->user = User::createFromSession($_SESSION);
			
			$this->flashbag = FlashBag::createFromSession($_SESSION);
		}
		
		public function getUser(){
			return $this->user;
		}
		
		public function getFlashBag(){
			return $this->flashbag;
		}
		
		public function close(){
			// save state
			$_SESSION['user'] = serialize($this->user);
			$_SESSION['flashbag'] = serialize($this->flashbag);
		}
		
		public static function getInstance(){
			if (!isset(static::$instance)){
				static::$instance = new static();
			}
			
			return static::$instance;
		}
		
	}