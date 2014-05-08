<?php
	class IndexController extends BaseController {
		
		public function indexAction(){
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$homepageRepository = $em->getRepository("Homepage");

			//for now just always use the first homepage (eventually have an active flag or something)
			$homepage = $homepageRepository->findOneBy('id', 1);

			if (!$homepage) {
				$r = new HtmlResponse();
				$r->setContent("Default Homepage not found");
				return $r;
			}

			$blocks = $homepageRepository->findHomepageBlocks($homepage);

			$guideRepository = $em->getRepository("Guide");
			foreach ($blocks as $block) {
				if ($block['layout'] === "recent"){
					// recent guides, just use the 3-across layout
					// DONE THROUGH TWIG, IS NICER
					// $block['layout'] = "3-across";

					// load recent guides
					$block['guides'] = $guideRepository->findRecentGuides();
				} else {
					$ids = explode(',', $block['guideids']);
					foreach ($ids as $id) {
						if ($guide = $guideRepository->findOneById($id)) {
							$guideRepository->findGuideCategories($guide);
							$block['guides'][] = $guide;
						}
					}
				}

				$homepage->addBlock($block);
			}

			return $this->render("index.html", array(
				"homepage" => $homepage
			));
		}
		
		public function p404Action(){
			return $this->p404();
		}

		public function aboutAction() {
			return $this->render("about.html", array(
				'title' => 'About'
			));
		}
	}