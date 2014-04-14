<?php
	class SeriesController extends BaseController {
		
		public function indexAction(){
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$seriesRepository = $em->getRepository("Series");
			
			// look for series in the repo
			$series = $seriesRepository->findAll();

			$guideRepository = $em->getRepository("Guide");
			foreach ($series as $list) {
				$guides = $guideRepository->findAllBySeries($list->getId());
				foreach ($guides as $guide) {
					$categories = $guideRepository->findGuideCategories($guide);
					foreach ($categories as $category){
						$guide->addCategory($category);
					}
					$list->addGuide($guide);
				}
			}
			
			return $this->render("serieslist.html", array(
				"series" => $series
			));
		}

		public function viewSeriesAction($url){
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$seriesRepository = $em->getRepository("Series");
			$guideRepository = $em->getRepository("Guide");

			// look for series in the repo
			$series = $seriesRepository->findOneBy('url', $url);

			$guides = $guideRepository->findAllBySeries($series->getId());
			foreach ($guides as $guide) {
				$categories = $guideRepository->findGuideCategories($guide);
				foreach ($categories as $category){
					$guide->addCategory($category);
				}
				$series->addGuide($guide);
			}
			
			return $this->render("series.html", array(
				"series" => $series
			));
		}
			
	}