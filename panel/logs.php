<?php
	include('config.php');
	session_start();
	if (!isset($_SESSION['loggedin'])) {
		header('Location: index.php');
		exit;
	}
	
	$file = file_get_contents('/var/www/logs.txt', true);
	if(isset($_GET['username'])){
		$file = '';
		$fn = fopen('/var/www/logs.txt',"r");
		while(!feof($fn))  {
			$result = fgets($fn);
			if(containsWord($result, $_GET['username'])){
				$file = $file.$result;
			}
		}
		fclose($fn);
	}

	$file = str_replace("\n","<br>",$file);
	
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
			<?=$file?>
			</div>
		</div>
	</body>
</html>