<?php
	class GuideController extends Controller {
	
		public function viewGuideAction($url){
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");
			
			// look for guide in the repo
			if (($guide = $guideRepository->findOneBy("url", $url)) !== false){
				return $this->render("guide.html", array(
					"guide" => $guide
				));
			} else { // guide not found in the repository
				$r = new HtmlResponse();
				$r->setContent("Guide not found");
			}
			
			return $r;
		}
	}