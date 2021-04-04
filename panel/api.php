<?php
	include('config.php');

	function generateRandomString($length = 10) {
		return substr(str_shuffle(str_repeat($x='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
	}

	$product_key = $_GET['product_key'];
	$key = $_GET['key'];
	$action = $_GET['action'];
	$html_text = "";

	if($key != "zenaosciji3wu9"){
		return;
	}

	if($action == "ban"){
		$res = $dbforum->query("UPDATE `consumers` SET `is_banned` = '1' WHERE `consumers`.`product_key` = '$product_key';");
		$html_text = 'SUCCESS';
	} else if($action == "unban"){
		$res = $dbforum->query("UPDATE `consumers` SET `is_banned` = '0' WHERE `consumers`.`product_key` = '$product_key';");
		$html_text = 'SUCCESS';
	} else if ($action == "remove"){
		$res = $dbforum->query("DELETE FROM `consumers` WHERE `consumers`.`product_key` = '$product_key';");
		$html_text = 'SUCCESS';
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
			$html_text = 'SUCCESS';
		}
	} else if ($action == "set_lifetime"){
		$module = $_GET['module'];

		$res = $dbforum->query("UPDATE `consumers` SET `$module` = 'LIFETIME' WHERE `consumers`.`product_key` = '$product_key';");
		$html_text = 'SUCCESS';
	} else if ($action == "reset_time"){
		$module = $_GET['module'];
		$res = $dbforum->query("UPDATE `consumers` SET `$module` = '0' WHERE `consumers`.`product_key` = '$product_key';");
		$html_text = 'SUCCESS';
	} else if ($action == "reset_hwid"){
		$res = $dbforum->query("UPDATE `consumers` SET `hwid` = '0' WHERE `consumers`.`product_key` = '$product_key';");
		$html_text = 'SUCCESS';
	} else if ($action == "create"){
		$amount = $_GET['amount'];
		$module = $_GET['module'];
		$duration = $_GET['duration'];

		for ($x = 1; $x <= $amount; $x++) {
			$serial = generateRandomString(5).'-'.generateRandomString(5).'-'.generateRandomString(5);
			$html_text = $html_text.$serial.'
			';
			if($module == "private"){
				$check = "INSERT INTO `consumers` (`hwid`, `start_date`, `product_key`, `script_public`, `script_private`, `is_banned`, `ip`) VALUES ('0', '0', '$serial', '0', '$duration days', '0', '0');";
			} else {
				$check = "INSERT INTO `consumers` (`hwid`, `start_date`, `product_key`, `script_public`, `script_private`, `is_banned`, `ip`) VALUES ('0', '0', '$serial', '$duration days', '0', '0', '0');";

			}
			$res = $dbforum->query($check);
		} 

	}
	
?>

<?=$html_text?>