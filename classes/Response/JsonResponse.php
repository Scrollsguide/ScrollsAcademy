<?php
	class JsonResponse extends Response implements ContentResponse {
		
		private $content = array();
		
		public function setContent($content){
			$this->content = $content;
		}
		
		public function getContent(){
			return json_encode($this->content);
		}
		
	}