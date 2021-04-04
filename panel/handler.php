<?php
	include('config.php');
	session_start();
	if (!isset($_SESSION['loggedin'])) {
		header('Location: index.php');
		exit;
	}
	ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
	function generateRandomString($length = 10) {
		return substr(str_shuffle(str_repeat($x='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
	}

	$product_key = $_GET['product_key'];
	$action = $_GET['action'];
	$html_text = "";

	if($action == "ban"){
		$res = $dbforum->query("UPDATE `consumers` SET `is_banned` = '1' WHERE `consumers`.`product_key` = '$product_key';");
		$html_text = '<h1>'.$product_key.' has been banned.</h1>';
	} else if($action == "unban"){
		$res = $dbforum->query("UPDATE `consumers` SET `is_banned` = '0' WHERE `consumers`.`product_key` = '$product_key';");
		$html_text = '<h1>'.$product_key.' has been un-banned.</h1>';
	} else if ($action == "remove"){
		$res = $dbforum->query("DELETE FROM `consumers` WHERE `consumers`.`product_key` = '$product_key';");
		$html_text = '<h1>'.$product_key.' has been removed.</h1>';
	} else if ($action == "add_time"){
		$duration = $_GET['duration'];
		$module = $_GET['module'];

		$res = $dbforum->query("SELECT * FROM consumers WHERE product_key='$product_key';");
		$row = $res->fetch_assoc();

		$module_str = $row[$module];
		$new_days = '';

		if($module_str == ''){
			$new_days = $duration.' Days';
		} else if ($module_str == 'LIFETIME'){
			$new_days = $duration.' Days';
		} else {
			$current_days = '';
			$module_exploded = explode("|", $module_str);
			$current_days = explode(" ", $module_exploded[0])[0];
			$new_days = $duration + $current_days;
			if($current_days == '0'){
				$new_date = str_replace($current_days, $new_days.' Days', $module_str);
			} else {
				$new_date = str_replace($current_days, $new_days, $module_str);
			}

			$res = $dbforum->query("UPDATE `consumers` SET `$module` = '$new_date' WHERE `consumers`.`product_key` = '$product_key';");
			$html_text = '<h1>'.$duration.' days have been added to '.$product_key.' for '.$module.'</h1>';
		}
	} else if ($action == "set_lifetime"){
		$module = $_GET['module'];

		$res = $dbforum->query("UPDATE `consumers` SET `$module` = 'LIFETIME' WHERE `consumers`.`product_key` = '$product_key';");
		$html_text = '<h1>'.$product_key.' has been given lifetime.</h1>';
	} else if ($action == "reset_time"){
		$module = $_GET['module'];
		$res = $dbforum->query("UPDATE `consumers` SET `$module` = '0' WHERE `consumers`.`product_key` = '$product_key';");
		$html_text = '<h1>'.$product_key.'\'s time has been reset.</h1>';
	} else if ($action == "reset_hwid"){
		$res = $dbforum->query("UPDATE `consumers` SET `hwid` = '0' WHERE `consumers`.`product_key` = '$product_key';");
		$html_text = '<h1>'.$product_key.'\'s hwid has been reset.</h1>';
	} else if ($action == "create"){
		$amount = $_GET['amount'];
		$module = $_GET['module'];
		
		for ($x = 1; $x <= $amount; $x++) {
			$serial = generateRandomString(5).'-'.generateRandomString(5).'-'.generateRandomString(5);
			$html_text = $html_text.'<h1>'.$serial.'</h1>';
			$check = "INSERT INTO `consumers` (`hwid`, `start_date`, `product_key`, `script_public`, `script_private`, `is_banned`, `ip`) VALUES ('0', '0', '$serial', '0', '0', '0', '0');";
			$res = $dbforum->query($check);
		} 
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
			<?=$html_text?>
			</div>
		</div>
	</body>
</html>