<?php
	class IndexController extends BaseController {
		
		public function indexAction(){
			return $this->render("index.html", array(
				"data" => "twigthing"
			));
		}
		
		public function redirectTest(){
			$toUrl = $this->getApp()->getRouter()->generateUrl("categories", array("categoryType" => "beginner"));
			
			return $this->redirect($toUrl);
		}
		
		public function p404Action(){
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");
			
			//get the random guides for the recommended
			$recommendedGuides = array();
			$recommendedGuides[] = $guideRepository->findRandomByCategory("beginner");
			$recommendedGuides[] = $guideRepository->findRandomByCategory("intermediate");
			$recommendedGuides[] = $guideRepository->findRandomByCategory("master");

			// remove empty guides
			$recommendedGuides = array_filter($recommendedGuides, function($e){ return $e !== false; });
			
			return $this->render("404.html", array(
				'title' => 'Page not found',
				'recommendedGuides' => $recommendedGuides
			));
		}

		public function aboutAction() {
			return $this->render("about.html", array(
				'title' => 'About'
			));
		}
	}