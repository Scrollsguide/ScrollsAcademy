<?php

	class JsonResponse extends Response implements ContentResponse {

		private $content = array();

		public function __construct() {
			parent::__construct();

			$this->setContentType("json");
		}

		public function setContent($content) {
			$this->content = $content;
		}

		public function getContent() {
			return json_encode($this->content);
		}

	}