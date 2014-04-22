<?php

	require_once 'includes/header.php';

	$_SESSION = array();
	session_destroy();

  header('Location: ' . $site_base_url);
	
?>
