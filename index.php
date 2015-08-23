<?php

require 'Slim/Slim.php';
require 'config.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/', function(){
    echo "<html><body><h1>We're counting...</h1></body></html>";
});

$app->get('/event/:code', function() use ($app) {
    $mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
    if ($mysqli->connect_errno) {
        error_log("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
        $app->response->setStatus(500);
        return;
    }

    if (!($stmt = $mysqli->prepare('SELECT event_id FROM `events` WHERE CODE=? AND open<=CURDATE() AND close>=CURDATE()'))) {
        error_log("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
        $app->response->setStatus(500);
        return;
    }

    if (!$stmt->bind_param("s", $code)) {
        error_log("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
        $app->response->setStatus(500);
        return;
    }

    if (!$stmt->execute()) {
        error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        $app->response->setStatus(500);
        return;
    }

    if(!$stmt->bind_result($eventID)){
        error_log("Bind Result failed: (" . $stmt->errno . ") " . $stmt->error);
        $app->response->setStatus(500);
        return;
    }

    $stmt->fetch();

    echo "EventID:".$eventID;
    //$eventID = 1;
    $ip = $app->request->headers->get('x-forwarded-for');
    $stmt->close();

    if(!$mysqli->query("INSERT INTO `clicks` (`event_id`, `ip_address`) VALUES (".$eventID.", INET_ATON('".$ip."'))")) {
        error_log("Insert Query failed: (" . $mysqli->errno . ") " . $mysqli->error);
        $app->response->setStatus(500);
        return;
    }
    
    $mysqli->close();
});

$app->get('/confirm', function() use ($app){
    $app->render("confirmation.html");
});

$app->run();
