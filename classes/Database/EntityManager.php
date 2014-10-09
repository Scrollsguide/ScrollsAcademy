<?php

	class EntityManager {

		private $app;

		private $repos = array();

		public function __construct(App $app) {
			$this->app = $app;
		}

		/**
		 * @param $entity
		 * @return Repository
		 * @throws Exception
		 */
		public function getRepository($entity, $database = null) {
			if (!isset($database)){
				// use default database if none is provided
				$database = $this->app->get("database");
			}
			if (!isset($repos[$entity])) {
				$repoName = $entity . "Repository";

				$repo = new $repoName($database);

				if (!($repo instanceof Repository)) {
					throw new Exception(sprintf("'%s' is not an instance of Repository.", $repoName));
				}

				$repos[$entity] = $repo;
			}

			return $repos[$entity];
		}

	}