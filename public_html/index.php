<?php
	$root = dirname(__DIR__);
	
	require_once $root . "/classes/Autoloader.php";
	$autoloader = Autoloader::register($root);
	$autoloader->add("classes");
	
	$a = new App($root);
	
	$a->init();
	$a->run();