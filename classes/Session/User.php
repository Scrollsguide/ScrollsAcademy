<?php
	class User {
		
		private $accessLevel = AccessLevel::NONE;
		
		private $isLoggedIn = false;
		
		public function __construct(){
		
		}
		
		public function isLoggedIn(){
			return $isLoggedIn;
		}
		
		public function checkAccessLevel($level){
			return ($this->accessLevel & $level) === $level;
		}
		
		public static function fromSession($sessionVars){
			return new User();
		}
	}
	
	class AccessLevel {
		
		const NONE = 1;
		const VIEW = 2;
		const ADMIN = 4;
		
	}