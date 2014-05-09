<?php

	class GuideController extends BaseController {

		public function viewGuideAction($url, Series $series = null) {
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");

			// look for guide in the repo
			if (($guide = $guideRepository->findOneBy("url", $url)) !== false) {
				// check visibility
				if ($guide->getStatus() === GuideStatus::VISIBLE || $guide->getStatus() === GuideStatus::VISIBLE_WITH_URL) {
					// load series this guide is part of
					$seriesRepository = $em->getRepository("Series");
					$allSeries = $seriesRepository->findSeriesByGuide($guide);

					// For now we just get one guide of each level - TODO use the tags from the guide
					$relatedGuides = array();
					//get the random guides for the relateds
					$relatedGuides[] = $guideRepository->findRandomByCategory("beginner");
					$relatedGuides[] = $guideRepository->findRandomByCategory("intermediate");
					$relatedGuides[] = $guideRepository->findRandomByCategory("master");

					// remove empty guides
					$relatedGuides = array_filter($relatedGuides, function ($e) {
						return $e !== false;
					});

					$categories = $guideRepository->findGuideCategories($guide);
					foreach ($categories as $category) {
						$guide->addCategory($category);
					}

					// make page title, append series name if it's a series guide
					$title = $guide->getTitle();
					if ($series !== null){
						$title .= " - " . $series->getTitle();
					}

					return $this->render("guide.html", array(
						"guide"         => $guide,
						"title"         => $title,
						"allSeries"     => $allSeries,
						"series"        => $series,
						"relatedGuides" => $relatedGuides
					));
				} else { // guide not visible
					return $this->p404();
				}
			} else { // guide not found in the repository
				return $this->p404();
			}
		}

		public function viewSeriesGuideAction($seriesUrl, $guideUrl) {
			// look up series
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$seriesRepository = $em->getRepository("Series");

			// look for series in the repo
			if (($series = $seriesRepository->findOneBy("url", $seriesUrl)) !== false) {
				// load guides for series
				$guideRepository = $em->getRepository("Guide");

				$guides = $guideRepository->findAllBySeries($series);

				foreach ($guides as $g) {
					$series->addGuide($g);
				}

				return $this->viewGuideAction($guideUrl, $series);
			} else {
				return $this->p404();
			}
		}
	}