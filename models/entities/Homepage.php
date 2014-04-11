<?php
	class Homepage {
	
		private $id;

		private $blocks = array();
	
		public function __construct(){
			
		}
		
		public function getId(){
			return $this->id;
		}

		public function setId($id){
			$this->id = $id;
		}

		public function addBlock($block) {
			$this->blocks[] = $block;
		}

		public function getBlocks() {
			return $this->blocks;
		}
	}