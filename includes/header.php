<?php
	session_start();

	if (!isset($_SESSION['userID'])) {
		header("Location: login.php");
		exit('Not logged in');
	}

	include('includes/config.php');

	// Initialize for inclusion of JavaScript snippets, will be included in
	// footer after loading of all JS libraries
	$jsOutput = '';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

		<?php echo '<title>' . $page_title . ' - ' . $site_title . '</title>'; ?>
		<link rel="icon" type="image/png" href="images/favicon.png" />

		<!--<link rel="stylesheet" type="text/css" href="css/jquery-ui.css" />-->
		<!--<link rel="stylesheet" type="text/css" href="css/dataTables.css" />-->
		<!--<link rel="stylesheet" type="text/css" href="css/TableTools.css" />-->
		<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.4/css/jquery-ui.min.css" />
		<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/datatables/1.9.4/css/jquery.dataTables.min.css" />
		<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/datatables-tabletools/2.1.5/css/TableTools.min.css" />
		<link rel="stylesheet" type="text/css" href="css/style.css" />
	</head>
	<body>
		<header>
			<div class="left">
				<a class="left" href="http://www.keene.edu"><img src="images/KSC-wordmark-150px.png" alt="Keene State College" /></a>
				<div class="left">
					<h2><a href="http://keene.edu/academics/library/">Wallace E. Mason Library</a></h2>
					<h2 id="site_title" ><a href="/"><?php echo $site_title; ?></a></h2>
				</div>
			</div>
			<div class="right">
				<h3 id="title"><?php echo $page_title; ?></h3>
				<div id="loginLine" class="login">You are logged in as <strong><?php echo $_SESSION['userName']; ?></strong></div>
				<div id="userlinks"><a href="userEdit.php">Account</a> &bull; <a href="logout.php">Logout</a></div>
				<!--<br />-->
			</div><!-- loginDiv -->
		</header>
		<div id="content">

			<?php
			if ((isset($_SESSION['dialogTitle']) && trim($_SESSION['dialogTitle'])) || (isset($_SESSION['dialogText']) && trim($_SESSION['dialogText']))) {
				// Ensure we don't have an undefined var
				$_SESSION['dialogTitle'] .= '';
				$_SESSION['dialogText'] .= '';
				echo '<div id="messagebox" class="">';
				if ($_SESSION['dialogTitle']) { echo '<strong>' . $_SESSION['dialogTitle'] . '</strong><br/>'; }
				if ($_SESSION['dialogText']) { echo $_SESSION['dialogText']; }
				echo '</div>';
			}
			unset($_SESSION['dialogTitle']);
			unset($_SESSION['dialogText']);
			?>
