<?php
	class TextDebugLine extends DebugLine {

		private $line = "";

		public function __construct($line){
			$this->line = $line;
		}

		public function output(){
			return $this->line;
		}

	}