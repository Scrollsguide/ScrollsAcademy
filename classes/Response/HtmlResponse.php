<?php

	class HtmlResponse extends Response implements ContentResponse {

		private $content = "";

		public function __construct() {
			parent::__construct();

			$this->setContentType("html");
		}

		public function setContent($content) {
			$this->content = $content;
		}

		public function getContent() {
			return $this->content;
		}

	}