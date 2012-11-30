<?php

/*
 * Following code will create a new product row
 * All product details are read from HTTP Post Request
 */

// array for JSON response
$response = array();

// check for required fields
if (isset($_POST['user_login']) && isset($_POST['user_pass']) && isset($_POST['user_nicename'])  &&  isset($_POST['user_email']) && isset($_POST['user_registered']) &&  isset($_POST['user_status']) &&   isset($_POST['display_name'])   ) {

    $user_login = $_POST['user_login'];
    $user_pass = $_POST['user_pass'];
    $user_nicename = $_POST['user_nicename'];
    $user_email = $_POST['user_email'];
    $user_registered = $_POST['user_registered'];
    $user_status = $_POST['user_status'];
    $display_name = $_POST['display_name'];

    // include db connect class
    require_once __DIR__ . '/db_connect.php';

    // connecting to db
    $db = new DB_CONNECT();

    // mysql inserting a new row
    $result = mysql_query("INSERT INTO er_users(user_login, user_pass, user_nicename, user_email, user_registered ,user_status, display_name) VALUES ('$user_login', '$user_pass', '$user_nicename', '$user_email', '$user_registered' ,'$user_status', '$display_name')");

    // check if row inserted or not
    if ($result) {
        // successfully inserted into database
        $response["success"] = 1;
        $response["message"] = "User Eklendi.";

        // echoing JSON response
        echo json_encode($response);
    } else {
        // failed to insert row
        $response["success"] = 0;
        $response["message"] = "Oops! An error occurred.";

        // echoing JSON response
        echo json_encode($response);
    }
} else {
    // required field is missing
    $response["success"] = 0;
    $response["message"] = "Required field(s) is missing";

    // echoing JSON response
    echo json_encode($response);
}
