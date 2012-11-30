<?php

/*
 * Following code will create a new product row
 * All product details are read from HTTP Post Request
 */

// array for JSON response
$response = array();

// check for required fields
if (isset($_POST['user_login']) && isset($_POST['user_pass'])) {

    $user_login = $_POST['user_login'];
    $user_pass = $_POST['user_pass'];

    // include db connect class
    require_once __DIR__ . '/db_connect.php';

    // connecting to db
    $db = new DB_CONNECT();

    // mysql inserting a new row
    $result = mysql_query("SELECT * FROM er_users WHERE user_login = '$user_login' AND user_pass = '$user_pass'");

    $no_of_rows = mysql_num_rows($result);	
    // check if row inserted or not
    if ($no_of_rows > 0) {
        // successfully inserted into database
        $response["success"] = 1;
        $response["message"] = "Account Correct.";

        // echoing JSON response
        echo json_encode($response);
    } else {
        // failed to insert row
        $response["success"] = 0;
        $response["message"] = "Not correct.";

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
