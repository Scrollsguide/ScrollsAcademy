<?php

	class Debug {

		const SCRIPT_NAME = "index_dev.php";

		private static $started = false;

		private static $debugLines = array();

		public static function accessible() {
			return $_SERVER['REMOTE_ADDR'] === "127.0.0.1";
		}

		public static function start() {
			self::$started = true;

			// enable all error reporting
			error_reporting(E_ALL);
		}

		public static function started() {
			return self::$started;
		}

		public static function addLine(DebugLine $line) {
			// no need to save debug if it's not needed
			if (!self::started()) {
				return;
			}

			self::$debugLines[] = $line;
		}

		public static function output() {
			$out = "";
			foreach (self::$debugLines as $line) {
				$out .= $line->output() . "<br />";
			}

			return $out;
		}

	}