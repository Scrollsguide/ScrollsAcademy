<?php

	class SeriesController extends BaseController {

		public function indexAction() {
			$cacheKey = "series_index";
			if (($series = $this->getCache()->get($cacheKey)) === null){
				// set up entity and repository
				$em = $this->getApp()->get("EntityManager");
				$seriesRepository = $em->getRepository("Series");

				// look for series in the repo
				$series = $seriesRepository->findAll();

				$guideRepository = $em->getRepository("Guide");
				foreach ($series as $list) {
					$guides = $guideRepository->findAllBySeries($list);

					$count = 0;
					foreach ($guides as $guide) {
						$count++;
						// update guide title with index
						$guide->setTitle($count . ". " . $guide->getTitle());
						$list->addGuide($guide);
					}
				}
				
				$this->getCache()->set($cacheKey, $series, 600);
			}

			return $this->render("serieslist.html", array(
				"title"  => "Series",
				"series" => $series
			));
		}

		public function viewSeriesAction($url) {
			$cacheKey = md5($url);
			if (($series = $this->getCache()->get($cacheKey)) === null){
				// set up entity and repository
				$em = $this->getApp()->get("EntityManager");
				$seriesRepository = $em->getRepository("Series");
				$guideRepository = $em->getRepository("Guide");

				// look for series in the repo
				if (($series = $seriesRepository->findOneBy('url', $url)) === null) {
					return $this->p404();
				}
				
				// load guides for series
				$guides = $guideRepository->findAllBySeries($series);

				$count = 0;
				foreach ($guides as $guide) {
					$count++;
					// update guide title with index
					$guide->setTitle($count . ". " . $guide->getTitle());

					$series->addGuide($guide);
				}
				
				$this->getCache()->set($cacheKey, $series, 600);
			}

			return $this->render("series.html", array(
				"title"  => $series->getTitle(),
				"series" => $series
			));
		}
	}