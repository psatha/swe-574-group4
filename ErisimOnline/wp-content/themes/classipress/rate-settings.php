<?php

//MODIFY TO YOUR OWN SETTINGS

//CONNECTS TO YOUR DATABASE
$c = mysql_connect("localhost", "root", "");
$db = mysql_select_db("erisimdb", $c);

//TABLES FOR THE CONTENT AND THE RATINGS (MODIFY IF TABLE NAMES ARE DIFFERENT)
$content = 'er_posts';
$ratings = 'er_av_rating';

$ip = $_SERVER["REMOTE_ADDR"]; //IP ADDRESS

?>