<?php

	class Profiler {

		private static $profiler = array();

		public static function start() {
			array_push(self::$profiler, microtime(true));
		}

		public static function end() {
			return (microtime(true) - array_pop(self::$profiler)) * 1000;
		}

	}