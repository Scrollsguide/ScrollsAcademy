<?php

	class GuideFilter {

		private $options;
		
		private $reverse;

		public function __construct($options = array(), $reverse = false) {
			$this->options = $options;
			$this->reverse = $reverse;
		}

		public function compare(Guide $guide) {
			$match = true;

			foreach ($this->options as $method => $value) {
				if (!$this->reverse){
					$match = $match && $guide->$method() === $value;
				} else {
					$match = $match && $guide->$method() !== $value;				
				}
			}

			return $match;
		}

	}