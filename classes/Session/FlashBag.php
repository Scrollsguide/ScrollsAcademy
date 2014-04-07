<?php
	class FlashBag {
		
		private $flashes = array();
		
		public function add($key, $value){
			if (!isset($this->flashes[$key])){
				$flash = new Flash();
				$this->flashes[$key] = $flash;
			}
			
			$this->flashes[$key]->add($value);
		}
		
		public function size(){
			return count($this->flashes);
		}
		
		public function get($key){
			if (!isset($this->flashes[$key])){
				return null;
			}
			
			$out = $this->flashes[$key]->get();
			unset($this->flashes[$key]);
			return $out;
		}
		
		public function tick(){
			// clean up flashes that have been around for more than 1 request
			foreach ($this->flashes as $key => $flash){
				if ($flash->getAge() > 1){
					unset($this->flashes[$key]);
				}
			}
		}
		
		public static function createFromSession($sessionVars){
			if (!isset($_SESSION['flashbag'])){
				// create new flashbag
				$f = new FlashBag();
				
				return $f;
			} else {
				$f = unserialize($_SESSION['flashbag']);
				
				// clean up old flashes
				$f->tick();
				
				return $f;
			}
		}
		
		public function __sleep(){
			return array("flashes");
		}
		
	}
	
	class Flash {
		
		private $values = array();
		
		private $age = 0;
		
		public function __construct(){
		}
		
		public function add($value){
			$this->values[] = $value;
		}
		
		public function get(){
			return $this->values;
		}
		
		public function getAge(){
			return $this->age;
		}
		
		public function __sleep(){
			return array(
				'values',
				'age'
			);
		}
		
		public function __wakeup(){
			$this->age++;
		}
	}