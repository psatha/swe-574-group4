<?php
function dbconnection($sql){
	$con = mysql_connect("localhost","root","");
	if (!$con)
	{
		die('Could not connect: ' . mysql_error());
	}

	mysql_select_db("erisimdb", $con);

	$result = mysql_query($sql);

	mysql_close($con);
	return $result;
}

function insertScript($sql){
	$con = mysql_connect("localhost","root","");
	if (!$con)
  	{
  		die('Could not connect: ' . mysql_error());
  	}

	mysql_select_db("erisimdb", $con);

	mysql_query($sql);

	mysql_close($con);
}

?>