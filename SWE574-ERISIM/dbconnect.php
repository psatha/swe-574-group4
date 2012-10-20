<?php
function dbconnection($sql){
	$con = mysql_connect("localhost","root","");
	if (!$con)
	{
		die('Could not connect: ' . mysql_error());
	}

	mysql_select_db("engel", $con);

	$result = mysql_query($sql);

	mysql_close($con);
	return $result;
}

function login(){
	$sql="select ";
}
?>