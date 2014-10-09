<?php

	class GuideHelper {

		public static function makeURL(Guide $g, GuideRepository $gr) {
			$start = URLUtils::makeBlob($g->getTitle());

			$count = 1;

			while (($gr->findOneBy("url", $start)) !== null){
				$start = URLUtils::makeBlob($g->getTitle() . "-" . (++$count));
			}

			return $start;
		}

	}