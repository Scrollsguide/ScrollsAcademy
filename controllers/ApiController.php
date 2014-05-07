<?php
	class ApiController extends Controller {

		public function categoriesAction(){
			$r = new JsonResponse();
			$r->setContent(array('api' => '123'));

			return $r;
		}

	}