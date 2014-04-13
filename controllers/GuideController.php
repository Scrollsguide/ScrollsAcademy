<?php
	class GuideController extends BaseController {
	
		public function viewGuideAction($url){
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");
			
			// look for guide in the repo
			if (($guide = $guideRepository->findOneBy("url", $url)) !== false){
				// check visibility
				if ($guide->getStatus() > 0){
					// For now we just get one guide of each level - TODO use the tags from the guide
					$relatedGuides = array();
					//get the random guides for the relateds
					$relatedGuides[] = $guideRepository->findRandomByCategory("beginner");
					$relatedGuides[] = $guideRepository->findRandomByCategory("intermediate");
					$relatedGuides[] = $guideRepository->findRandomByCategory("master");

					// remove empty guides
					$relatedGuides = array_filter($relatedGuides, function($e){ return $e !== false; });

					$categories = $guideRepository->findGuideCategories($guide);
					foreach ($categories as $category){
						$guide->addCategory($category);
					}

					return $this->render("guide.html", array(
						"guide" => $guide,
						"title" => $guide->getTitle(),
						"relatedGuides" => $relatedGuides
					));
				} else { // guide not visible
					return $this->p404();
				}
			} else { // guide not found in the repository
				return $this->p404();
			}
		}
	}