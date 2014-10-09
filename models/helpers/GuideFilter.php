<?php

	class GuideFilter {

		private $options;

		public function __construct($options = array()) {
			$this->options = $options;
		}

		public function compare(Guide $guide) {
			$match = true;

			foreach ($this->options as $method => $value) {
				$match = $match && $guide->$method() === $value;
			}

			return $match;
		}

	}