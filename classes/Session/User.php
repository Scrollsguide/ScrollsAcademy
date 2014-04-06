<?php
	class User {
		
		private $accessLevel;
		
		public function __construct(){
		
		}
		
		public function checkAccessLevel($level){
			return ($this->accessLevel & $level) === $level;
		}
	}
	
	class AccessLevel {
		
		const NONE = 1;
		const VIEW = 2;
		const ADMIN = 4;
		
	}