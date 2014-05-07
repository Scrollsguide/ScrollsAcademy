<?php

	class ResourceResponse extends Response implements ContentResponse {

		private $content;

		public function __construct($contentType){
			parent::__construct();

			$this->setContentType($contentType);
		}

		public function setContent($content) {
			$this->content = $content;
		}

		public function getContent() {
			return $this->content;
		}
	}