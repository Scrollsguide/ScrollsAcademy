<?php

	class VoteController extends BaseController {

		public function __construct(App $app) {
			parent::__construct($app);

			// don't cache voting pages
			$this->setCacheRules(array(
				"cache" => false
			));
		}

		public function voteAction($guideUrl, $vote) {
			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");

			if (($guide = $guideRepository->findOneBy("url", $guideUrl)) !== false) {
				$voteRepository = $em->getRepository("Vote");

				

				if ($this->getApp()->getRequest()->isAjax()) {
					$r = new JsonResponse();

					$r->setContent(array('result' => 'voted'));

					return $r;
				} else { // client doesn't support ajax, redirect to guide page
					return new RedirectResponse($this->getApp()->getRouter()->generateUrl("view_guide", array("title" => $guideUrl)));
				}
			} else { // guide not found
				if ($this->getApp()->getRequest()->isAjax()){
					// empty json response
					return new JsonResponse();
				} else {
					return $this->p404();
				}
			}
		}
	}