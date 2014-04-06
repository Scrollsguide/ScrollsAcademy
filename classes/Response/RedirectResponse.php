<?php
	class RedirectResponse extends Response {
		
		private $target; // target url of the redirect
		
		public function __construct($target){
			$this->target = $target;
		}
		
		public function getTarget(){
			return $this->target;
		}
		
	}