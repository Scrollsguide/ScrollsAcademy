<?php

	class App {

		private $baseDir;

		private $config;

		private $router;

		private $cache;

		private $classloader;

		private $route;

		private $controller;

		private $session;

		// array of objects not used for every request, for example the database object
		private $optObjects;

		public function __construct($baseDir) {
			$this->baseDir = $baseDir;
		}

		public function setClassloader(Autoloader $autoloader) {
			$this->classloader = $autoloader;
		}

		public function init() {
			// add user controller directory to classloader
			$this->classloader->addDirectory("controllers");
			$this->classloader->addDirectory("models", true);
			$this->classloader->addDirectory("extensions");

			// set up cache
			$this->cache = new Cache($this, $this->baseDir . "/cache");

			// read config files
			$this->config = new Config();
			$this->config->addConfigFile($this->baseDir . "/config/config.ini");

			$this->session = Session::getInstance();

			$this->request = Request::createFromServer();

			// set up router
			$this->router = new Router($this);
			$this->router->addRouteFile($this->baseDir . "/config", "routes.json");
			$this->router->addRouteFile($this->baseDir . "/config", "api_routes.json");
			$this->router->addRouteFile($this->baseDir . "/config", "admin_routes.json");
			
			$this->setupDatabase();
		}

		public function run() {
			// retrieve matching route, if any
			$this->route = $this->matchRoute();

			$this->setupController();

			// check cache here, load controller if cache cannot be used
			$usedCache = false;
			$shouldCache = !Debug::started() && $this->controller->getCacheRule("cache");
			if ($shouldCache) {
				$cacheKey = "Pages/" . RouteHelper::getCacheKey($this->getRoute());
				if ($this->getCache()->isValid($cacheKey, $this->controller->getCacheRule("ttl"))) {
					if (($contentFromCache = $this->getCache()->load($cacheKey)) !== false) {
						// successfully loaded from cache
						$usedCache = true;

						// create new response to process headers
						$r = new Response();
						$r->setStatusCode($this->controller->getCacheRule("statusCode"));
						$r->setContentType($this->controller->getCacheRule("contentType"));
						$r->process();

						echo $contentFromCache;
					}
				}
			}

			// no hit on the cache, execute request through the controller
			if (!$usedCache) {
				$this->setupTemplateEngine();

				// returns instance of Response class
				$response = $this->runAction();

				// check response type and process
				if (!($response instanceof Response)) {
					throw new Exception(sprintf("Action '%s' should return Response.", $this->route->getActionName()));
				}

				$response->process();

				// cache the response if necessary
				// and output the page if it has content
				if ($response instanceof ContentResponse) {
					if ($shouldCache) {
						$this->tryCache($response, $cacheKey);
					}

					echo $response->getContent();
				}
			}
		}

		public function close() {
			if (($db = $this->get("database")) !== null) {
				$db->close();
			}

			$this->session->close();
		}

		private function setupDatabase() {
			$c = $this->getConfig();

			$database = new Database($this);
			$database->setHost($c->get(Config::PDO_HOST), $c->get(Config::PDO_PORT));
			$database->setCredentials($c->get(Config::PDO_USER), $c->get(Config::PDO_PASS));
			$database->setDatabaseName($c->get(Config::PDO_DB));

			$this->put("database", $database);

			// add entity manager
			$em = new EntityManager($this);
			$this->put("EntityManager", $em);
		}

		private function setupTemplateEngine() {
			$this->getClassloader()->tryRequire($this->getBaseDir() . "/libs/Twig/lib/Twig/Autoloader.php");
			Twig_Autoloader::register();

			$loader = new Twig_Loader_Filesystem($this->getBaseDir() . "/views");

			// set up cache for twig
			$this->getCache()->prepareDirectory($this->getCache()->getPathForFile("TwigViews"));
			$twig = new Twig_Environment($loader, array(
				"cache"       => $this->getCache()->getPathForFile("TwigViews"),
				"auto_reload" => true
			));

			// add extensions
			TwigHelper::registerHelpers($this, $twig);

			$this->put("twig", $twig);
		}

		private function setupController() {
			$this->controller = $this->getControllerForRoute($this->route);
		}

		private function runAction() {
			return call_user_func_array(array($this->controller, $this->route->getActionName()), $this->route->getUrlParameters());
		}

		private function tryCache(ContentResponse $response, $location) {
			// check whether the response can be cached
			$this->getCache()->save($location, $response->getContent());
		}

		private function matchRoute() {
			// exlude get parameters, pass false
			$relPath = $this->getRequest()->getURL()->getPath(true);

			return $this->router->match($relPath);
		}

		/**
		 * @param Route $route
		 * @return Controller
		 * @throws Exception
		 */
		private function getControllerForRoute(Route $route) {
			// load the controller
			// the classloader checks whether the class exists or not
			$fullControllerName = $route->getControllerName();
			$controller = new $fullControllerName($this);

			if (!$controller instanceof Controller) {
				throw new Exception(sprintf("Class '%s' not an instance of Controller.", $controller));
			}
			// now check whether the action is available in the controller
			$fullActionName = $route->getActionname();
			if (!method_exists($controller, $fullActionName)) {
				throw new Exception(sprintf("Action '%s' not found in '%s'.", $fullActionName, $fullControllerName));
			}

			// everything seems to be in order, return controller
			return $controller;
		}

		public function getBaseDir() {
			return $this->baseDir;
		}

		/**
		 * @return Session
		 */
		public function getSession() {
			return $this->session;
		}

		/**
		 * @return Request
		 */
		public function getRequest() {
			return $this->request;
		}

		/**
		 * @return Router
		 */
		public function getRouter() {
			return $this->router;
		}

		/**
		 * @return Route
		 */
		public function getRoute() {
			return $this->route;
		}

		/**
		 * @return Config
		 */
		public function getConfig() {
			return $this->config;
		}

		/**
		 * @return Autoloader
		 */
		public function getClassloader() {
			return $this->classloader;
		}

		/**
		 * @return Twig_Environment
		 */
		public function getTwig() {
			return $this->twig;
		}

		/**
		 * @return Cache
		 */
		public function getCache() {
			return $this->cache;
		}

		public function put($key, $obj) {
			$this->optObjects[$key] = $obj;
		}

		public function get($obj) {
			if (!isset($this->optObjects[$obj])) {
				return null;
			}

			return $this->optObjects[$obj];
		}

		public function getDebug(){
			return Debug::output();
		}
	}