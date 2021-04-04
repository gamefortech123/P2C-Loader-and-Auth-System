<?php
	include('config.php');
	session_start();
	if (!isset($_SESSION['loggedin'])) {
		header('Location: index.php');
		exit;
	}

	$html_text = "";
	$res = $dbforum->query("SELECT * FROM consumers;");

	while ($row = $res->fetch_assoc()) {
		$time_left_days = 'n/a';
		$time_end_str ='';
		$time_end_date = new DateTime();
		$current_date = new DateTime();
		$time_html = '';

		$module_str = $row['script_private'];
		if($module_str != "0"){
			$module_exploded = explode("|", $module_str);
			if($module_exploded[0] == "LIFETIME"){
				$time_left_days = 'never';
			} else {
				$time_left_days = 'not started. ('.$module_exploded[0].' to be used.)';
			}
			if(sizeof($module_exploded) > 1) {
				$start_time = $module_exploded[1];
				$time_end_str = date('Y-m-d H:i', strtotime($start_time.'+ '.$module_exploded[0]));
				$time_end_date = new DateTime($time_end_str);
				$interval = $time_end_date->diff($current_date);
				$time_left_days = $interval->format('%m month(s) %d day(s) %h hour(s)');
			}
		} else {
			$time_left_days = 'n/a';
		}
		$time_html = $time_html.'Script (private)<br> expires: '.$time_left_days.'<br><ul class="button-group"><li><a href="handler.php?action=add_time&duration=1&module=script_private&product_key='.$row['product_key'].'" class="button">+1 day</a><a href="handler.php?action=add_time&duration=7&module=script_private&product_key='.$row['product_key'].'" class="button">+7 days</a><a href="handler.php?action=add_time&duration=31&module=script_private&product_key='.$row['product_key'].'" class="button">+31 days</a><a href="handler.php?action=set_lifetime&module=script_private&product_key='.$row['product_key'].'" class="button">lifetime</a><a href="handler.php?action=reset_time&module=script_private&product_key='.$row['product_key'].'" class="button">reset</a></ul>';


		$module_str = $row['script_public'];
		if($module_str != "0"){
			$module_exploded = explode("|", $module_str);
			if($module_exploded[0] == "LIFETIME"){
				$time_left_days = 'never';
			} else {
				$time_left_days = 'not started. ('.$module_exploded[0].' to be used.)';
			}
			if(sizeof($module_exploded) > 1) {
				$start_time = $module_exploded[1];
				$time_end_str = date('Y-m-d H:i', strtotime($start_time.'+ '.$module_exploded[0]));
				$time_end_date = new DateTime($time_end_str);
				$interval = $time_end_date->diff($current_date);
				$time_left_days = $interval->format('%m month(s) %d day(s) %h hour(s)');
			}
		} else {
			$time_left_days = 'n/a';
		}
		$time_html = $time_html.'<br> Script (public)<br> expires: '.$time_left_days.'<br><ul class="button-group"><li><a href="handler.php?action=add_time&duration=1&module=script_public&product_key='.$row['product_key'].'" class="button">+1 day</a><a href="handler.php?action=add_time&duration=7&module=script_public&product_key='.$row['product_key'].'" class="button">+7 days</a><a href="handler.php?action=add_time&duration=31&module=script_public&product_key='.$row['product_key'].'" class="button">+31 days</a><a href="handler.php?action=set_lifetime&module=script_public&product_key='.$row['product_key'].'" class="button">lifetime</a><a href="handler.php?action=reset_time&module=script_public&product_key='.$row['product_key'].'" class="button">reset</a></ul>';

		$ban_button = '';
		if($row['is_banned'] == '1'){
			$ban_button = '<a href="handler.php?action=unban&product_key='.$row['product_key'].'" class="button">unban</a>';
		} else {
			$ban_button = '<a href="handler.php?action=ban&product_key='.$row['product_key'].'" class="button">ban</a>';
		}

		$html_text = $html_text.'<div class="user_information">'.$row['product_key'].' {hwid='.$row['hwid'].', ip='.$row['ip'].'} <ul class="button-group" style="float: right;"><li>'.$ban_button.'<a href="handler.php?action=reset_hwid&product_key='.$row['product_key'].'" class="button">reset hwid</a><a href="logs.php?product_key='.$row['product_key'].'" class="button">logs</a><a href="handler.php?action=remove&product_key='.$row['product_key'].'" class="button">remove</a></ul><hr>'.$time_html.' </div>';
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
			<h3>woody's little femmies</h3>
			<?=$html_text?>
			</div>
		</div>
	</body>
</html>