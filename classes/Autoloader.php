<?php
	class Autoloader {
		
		private $baseDir;
		
		// list of directories which can contain classes
		private $dirs = array();
		
		public static function register($baseDir = null){
			$loader = new self($baseDir);
			spl_autoload_register(array($loader, 'autoload'));

			return $loader;
		}
		
		public function __construct($baseDir = null){
			if ($baseDir === null){
				$baseDir = dirname(__FILE__) . "/..";
			}
			
			$this->baseDir = $baseDir;
		}
		
		public function add($dir){
			$this->dirs[] = $this->baseDir . "/" . $dir;
		}
		
		public function autoload($class){
			if ($class[0] === '\\'){
				$class = substr($class, 1);
			}

			$found = false;
			for ($i = 0; $i < count($this->dirs) && !$found; $i++){		
				$file = sprintf('%s/%s.php', $this->dirs[$i], $class);
				
				if (is_file($file)){
					require $file;
					$found = true;
				}
			}
		}
	}