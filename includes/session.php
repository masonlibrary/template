<?php

	require_once 'includes/config.php';

	session_name(preg_replace('/[^A-Za-z0-9]/', '', $site_title)); // only alphanumeric characters allowed
	session_start();

?>