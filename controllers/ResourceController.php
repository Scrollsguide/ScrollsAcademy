<?php

	use Assetic\Asset\AssetCollection;
	use Assetic\Asset\FileAsset;

	class ResourceController extends BaseController {

		private $resourceMap = array(
			'css/main.css'    => array(
				'/styles/main.css',
				'/styles/tablet.css',
				'/styles/desktop.css'
			),
			'css/vendors.css' => array(
				'/styles/vendor/bootstrap.min.css',
				'/styles/vendor/font-awesome.min.css',
				'/styles/vendor/rrssb.css'
			),

			'js/vendors.js'   => array(
				'/js/vendor/jquery-1.11.0.min.js',
				'/js/vendor/bootstrap/affix.js',
				'/js/vendor/bootstrap/alert.js',
				'/js/vendor/bootstrap/dropdown.js',
				'/js/vendor/bootstrap/tooltip.js',
				'/js/vendor/bootstrap/modal.js',
				'/js/vendor/bootstrap/transition.js',
				'/js/vendor/bootstrap/button.js',
				'/js/vendor/bootstrap/popover.js',
				'/js/vendor/bootstrap/carousel.js',
				'/js/vendor/bootstrap/scrollspy.js',
				'/js/vendor/bootstrap/collapse.js',
				'/js/vendor/bootstrap/tab.js',
				'/js/vendor/rrssb.js'
			)
		);

		public function getAvatarAction($username) {
			return Response::emptyResponse();
		}

		public function assetCacheAction($fileType, $filename) {
			$cachePath = $fileType . "/" . $filename . "." . $fileType;

			// is this a valid resource?
			if (!isset($this->resourceMap[$cachePath])) {
				// not valid, return empty response to not mess with the rest of the page/scripts/html
				return Response::emptyResponse();
			}

			// resource is valid, create from all assets
			$resources = $this->resourceMap[$cachePath];

			$cache = new Cache($this->getApp()->getBaseDir() . "/public_html/assets/cache");

			$this->getApp()->getClassloader()->addDirectory("libs/Assetic/src", true);

			$assetCollection = new AssetCollection();
			// add all resources to collection
			$assetBase = $this->getApp()->getBaseDir() . "/public_html/assets";
			foreach ($resources as $r) {
				$assetCollection->add(new FileAsset($assetBase . $r));
			}

			// get contents of all merged files
			$assetContent = $assetCollection->dump($this->getFilter($fileType));

			$cache->save($cachePath, $assetContent);

			// show file for the first time, next time it will be loaded from cache
			$r = new HtmlResponse();
			$r->setHeader("Content-type: " . ResponseContentTypes::getContentType($fileType));
			$r->setContent($assetContent);

			return $r;
		}

		private function getFilter($fileType) {
			/* filters mess with the responsive layout, so don't use any
			if ($fileType === "css"){
				$this->getApp()->getClassloader()->tryRequire($this->getApp()->getBaseDir() . "/libs/min/cssmin.php");
				return new CssMinFilter();
			}
			*/

			// no filters needed
			return null;
		}

	}