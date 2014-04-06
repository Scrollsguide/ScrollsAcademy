<?php
	class Session {
	
		private static $instance; 
	
		private function __construct(){
			
		}
		
		public static function getInstance(){
			if (!isset(static::$instance)){
				static::$instance = new static();
			}
			
			return static::$instance;
		}
		
	}