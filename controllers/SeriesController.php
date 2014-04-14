<?php

	class SeriesController extends BaseController {

		public function indexAction() {
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

					// load categories for guides
					$categories = $guideRepository->findGuideCategories($guide);
					foreach ($categories as $category) {
						$guide->addCategory($category);
					}
					$list->addGuide($guide);
				}
			}

			return $this->render("serieslist.html", array(
				"title"  => "Series",
				"series" => $series
			));
		}

		public function viewSeriesAction($url) {
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$seriesRepository = $em->getRepository("Series");
			$guideRepository = $em->getRepository("Guide");

			// look for series in the repo
			if (($series = $seriesRepository->findOneBy('url', $url)) !== false) {
				// load guides for series
				$guides = $guideRepository->findAllBySeries($series);

				$count = 0;
				foreach ($guides as $guide) {
					$count++;
					// update guide title with index
					$guide->setTitle($count . ". " . $guide->getTitle());

					// load categories for guides
					$categories = $guideRepository->findGuideCategories($guide);
					foreach ($categories as $category) {
						$guide->addCategory($category);
					}
					$series->addGuide($guide);
				}

				return $this->render("series.html", array(
					"title"  => $series->getTitle(),
					"series" => $series
				));
			} else {
				// series not found
				return $this->p404();
			}
		}

	}