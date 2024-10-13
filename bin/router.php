<?php

use Fenrir\Framework\Application;

$root = realpath('./');

if (file_exists($root."/public/index.php")) {
    return require $root . "/public/index.php";
}

require $root.'/vendor/autoload.php';
$app = new Application($root);
$app->run();