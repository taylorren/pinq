<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->get('/', function() {
    return 'Silex welcomes you';
});

$app->get('/list/{name}', function ($name) {
    return 'A simple function with param: '.$name;
});

$app->run();
