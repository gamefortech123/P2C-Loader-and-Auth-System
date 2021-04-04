<?php
	include('config.php');
	session_start();
	if (!isset($_SESSION['loggedin'])) {
		header('Location: index.php');
		exit;
	}
	
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>woody panel</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>

	<body class="loggedin">
	<div class="content">
			<div style="margin-left: 15%; width: 70%; height: 100%;">
			<center><h2>auth panel</h2><ul class="button-group"><li><a href="/home.php" class="small button">users</a><a href="/create.php" class="small button">create</a><a href="/logs.php" class="small button">logs</a><a href="/logout.php" class="small button">logout</a></ul></center>
			<br>
			<label for="username">
			<form action="handler.php" method="get">
			<input type="text" name="amount" placeholder="amount" id="amount" required><br>
			<input type="hidden" name="action" value="create" />
			<input type="hidden" name="product_key" value="0" />
			<button formaction="handler.php" formmethod="get" class="small button">generate keys</button>
			</form>
			</div>
		</div>
	</body>
</html>