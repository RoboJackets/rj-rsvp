<?php

require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/', function(){
    echo "<html><body><h1>We're counting...</h1></body></html>";
});

$app->get('/event/:code', function(){
    //Query if event exists & is between open and close date
    //If yes, add click to database
});

$app->run();
