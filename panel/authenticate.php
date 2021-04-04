<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	include('config.php');	
	session_start();
	if ( !isset($_POST['username'], $_POST['password']) ) {
		header('Location: index.php');
	}

	$username_str = $_POST['username'];
	
	# do they exist
	if($username_str != 'woodythenoob123'){
		header('Location: index.php');
		return;
	}
	

	session_regenerate_id();
	$_SESSION['loggedin'] = TRUE;
	$_SESSION['name'] = $_POST['username'];
	$_SESSION['id'] = $id;
	header('Location: home.php');
?>