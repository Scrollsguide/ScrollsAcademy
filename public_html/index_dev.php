<?php
	error_reporting(0);
	$root = dirname(__DIR__);

	require_once $root . "/classes/Autoloader.php";
	$autoloader = Autoloader::register($root);
	// recursively add every directory in "classes"
	$autoloader->addDirectory("classes", true);

	if (!Debug::accessible()) {
		header("HTTP/1.0 403 Forbidden");
		exit("You are not allowed to access this file.");
	}

	Debug::start();

	$a = new App($root);
	$a->setClassloader($autoloader);

	$a->init();
	$a->run();
	$a->close();