<?php
require_once dirname(__DIR__)."/src/Core/SessionManager.php";
require_once dirname(__DIR__) . "/src/Core/Routeur.php";

$session = new SessionManager();
$session->init();

$router = new Router();
$router->run();
