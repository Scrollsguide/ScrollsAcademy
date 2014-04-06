<?php
	class GuideController extends Controller {
	
		public function viewGuideAction($url){
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$articleRepository = $em->getRepository("Article");
			
			// look for guide in the repo
			if (($article = $articleRepository->findOneBy("url", $url)) !== false){
				$r = new HtmlResponse();
				$r->setContent($article->getTitle());
			} else { // guide not found in the repository
				$r = new HtmlResponse();
				$r->setContent("Guide not found");
			}
			return $r;
		}
	}