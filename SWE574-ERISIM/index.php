<html>
<head>
<title>Bu bir deneme</title>
</head>
<body>
<?php

echo "<a href='ilanadresi.net'>deneme link</a><br/>";
?>

	<table>
		<tr>
			<td><label>Username:</label></td>
			<td><input name="username" type="text"></input></td>
		</tr>
		<tr>
			<td><label>Password:</label>
			</td>
			<td><input type="password"></input>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><button type="submit" onclick="onclk()">Go</button>
			</td>
		</tr>
	</table>
	<br />

	<?php
	include 'dbconnect.php';

	$sql = "SELECT * FROM usertype";
	$result=dbconnection($sql);
	while($row = mysql_fetch_array($result))
	{
		echo $row['roleID'] . " " . $row['roleName'];
		echo "<br />";
  }
?>
</body>
</html>
