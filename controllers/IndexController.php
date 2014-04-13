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
				$ids = explode(',', $block['guideids']);
				foreach ($ids as $id) {
					if ($guide = $guideRepository->findOneBy('id', $id)) {
						$categories = $guideRepository->findGuideCategories($guide);
						foreach ($categories as $category){
							$guide->addCategory($category);
						}
						$block['guides'][] = $guide;
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