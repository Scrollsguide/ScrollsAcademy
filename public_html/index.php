<?php
	$root = dirname(__DIR__);
	
	require_once $root . "/classes/Autoloader.php";
	$autoloader = Autoloader::register($root);
	// recursively add every directory in "classes"
	$autoloader->addDirectory("classes", true);
	
	$a = new App($root);
	$a->setClassloader($autoloader);
	
	$a->init();
	$a->run();
	$a->close();
	
	print_r($_SERVER);