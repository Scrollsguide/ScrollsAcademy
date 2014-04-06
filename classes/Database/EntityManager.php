<?php
	class EntityManager {
		
		private $app;
		
		private $repos = array();
		
		public function __construct(App $app){
			$this->app = $app;
		}
		
		public function getRepository($entity){
			if (!isset($repos[$entity])){
				$repoName = $entity . "Repository";
				
				$repo = new $repoName($this->app->get("database"));
				
				if (!($repo instanceof Repository)){
					throw new Exception(sprintf("'%s' is not an instance of Repository.", $repoName));
				}
				
				$repos[$entity] = $repo;
			}
			
			return $repos[$entity];			
		}
		
	}