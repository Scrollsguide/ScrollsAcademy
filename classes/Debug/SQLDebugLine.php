<?php

	class SQLDebugLine extends DebugLine {

		private $statement = "";

		public function __construct($statement) {
			$this->statement = $statement;
		}

		public function output() {
			return $this->statement;
		}

	}