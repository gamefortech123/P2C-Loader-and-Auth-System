<?php
	include('config.php');

	$invalid_key = "jEL8q7ack";
	$invalid_hwid = "byYkP36DrwwJ";
	$sub_expired = "6n9prTpS538B";
	$is_banned = "4e9mMzxqfRA4";
	
	$product_key = $_GET['product_key'];
	$module = $_GET['module'];
	$hwid = $_GET['hwid'];
	$id = $_GET['id'];
	
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip_address = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip_address = $_SERVER['REMOTE_ADDR'];
	}
	
	if(!empty($product_key)) {
		$log = new Logger("/var/www/logs.txt");
		$log->setTimestamp("D M d 'y h.i A");

		$check = "SELECT * FROM consumers WHERE product_key='$product_key';";
		$res = $dbforum->query($check);
		$row = $res->fetch_assoc();
		if($row == ''){
			echo $invalid_key;
			return;
		}
		
		$res = $dbforum->query("UPDATE `consumers` SET `ip` = '$ip_address' WHERE `consumers`.`product_key` = '$product_key';");

		if($row['is_banned'] == '1'){
			echo $is_banned;
			return;
		}

		if($row['hwid'] == '0' || $row['hwid'] == ''){
			$res = $dbforum->query("UPDATE `consumers` SET `hwid` = '$hwid' WHERE `consumers`.`product_key` = '$product_key';");
		} else if ($hwid != $row['hwid']) {
			echo $invalid_hwid;
			return;
		}

		# checking if they have module info (if not they dont have sub)
		$module_str = $row[$module];
		if($module_str == "0"){
			echo $sub_expired;
			return;
		}

		if(!containsWord($module_str, 'LIFETIME')){
			$module_exploded = explode("|", $module_str);
			if(sizeof($module_exploded) == 1){
				$current_date = new DateTime();
				$start_str = $current_date->format('Y-m-d H:i');
				$to_write = explode("|", $row[$module])[0].'|'.$start_str;
				$query = "UPDATE `consumers` SET `$module` = '$to_write' WHERE `consumers`.`product_key` = '$product_key';";
				$res = $dbforum->query($query);
			}

			$end_date_str = date('Y-m-d H:i', strtotime($module_exploded[1].'+ '.$module_exploded[0]));
			$end_date =  new DateTime($end_date_str);
			
			$current_date = new DateTime();
			if($end_date < $current_date){
				echo $sub_expired;
				return;
			}
			
			$interval = $end_date->diff($current_date);
			echo $interval->format('0:%a:%h:');
		} else {
			echo "LIFETIME:0:0:";
		}
				
		$log->putLog("[".$id."]", " Login successful. {key=".$product_key.", ip=".$ip_address.", hwid=".$hwid."}\n");
		echo $id;
		return;
	}
?>