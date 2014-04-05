<?php
	class Util {
		
		public static function array_empty_merge(){
			$numArrays = func_num_args();
			$arrs = func_get_args();

			$ret = array();

			for ($i = 0; $i < $numArrays; $i++) {
				if (!empty($arrs[$i])) {
					$ret = array_merge($ret, $arrs[$i]);
				}
			}

			return $ret;
		}
	}