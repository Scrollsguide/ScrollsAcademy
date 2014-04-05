<?php
	$root = dirname(__DIR__);
	
	require_once $root . "/classes/Autoloader.php";
	$autoloader = Autoloader::register($root);
	$autoloader->add("classes");
	
	$a = new App($root);
	$a->setClassloader($autoloader);
	
	$a->init();
	$a->run();