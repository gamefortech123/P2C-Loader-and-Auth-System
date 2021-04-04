<?php
    include('config.php');

    $product_key = $_GET['product_key'];
    $hwid = $_GET['hwid'];
    $module = $_GET['module'];

    # get client's ip
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

        $file_location = '';
        if($module == 'private'){
            $file_location = '/var/www/test_dll.dll';
        }
		
        $data = file_get_contents($file_location);
        for($i = 0; $i < strlen($data); ++$i) {
            $char = $data[$i];
            $hex_val = bin2hex($char);
            $dec_val = hexdec($hex_val);
            echo "$dec_val,";
        }
    }
?>