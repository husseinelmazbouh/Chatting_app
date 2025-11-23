<?php

function authenticateParams() {
    header('Content-Type: application/json');
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST");

    //Check for User ID 
    if (isset($_REQUEST['user_id'])) {
        $userId = (int)$_REQUEST['user_id'];
    } else {
        $userId = 0;
    }

    if ($userId === 0) {
        echo json_encode([
            "status" => "error", 
            "message" => "Unauthorized: Missing user_id in request"
        ]);
        exit; 
    }

    return $userId;
}