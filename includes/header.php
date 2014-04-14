<?php
	include('includes/config.php');

	session_name(preg_replace('/[^A-Za-z0-9]/', '', $site_title)); // only alphanumeric characters allowed
	session_start();

	// Don't redirect-loop if we end with '/login.php'. Credit: http://stackoverflow.com/a/834355/217374
	if ($site_uses_auth && !isset($_SESSION['userID']) && !(substr($_SERVER['SCRIPT_NAME'], -strlen('/login.php'))==='/login.php')) {
		header("Location: login.php");
		exit('Not logged in');
	}

	// Initialize for inclusion of JavaScript snippets, will be included in
	// footer after loading of all JS libraries
	$jsOutput = '';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">

		<?php echo '<title>' . $page_title . ' - ' . $site_title . '</title>'; ?>
		<link rel="icon" type="image/png" href="images/favicon.png" />

		<!--<link rel="stylesheet" type="text/css" href="css/jquery-ui.css" />-->
		<!--<link rel="stylesheet" type="text/css" href="css/dataTables.css" />-->
		<!--<link rel="stylesheet" type="text/css" href="css/TableTools.css" />-->
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.4/css/jquery-ui.min.css" />
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/datatables/1.9.4/css/jquery.dataTables.min.css" />
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/datatables-tabletools/2.1.5/css/TableTools.min.css" />
		<link rel="stylesheet" href="css/style.css" />
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
				<?php
					if($site_uses_auth) {
						echo '<div>You are logged in as <strong>'.$_SESSION['userName'].'</strong>.</div>';
						echo '<div><a href="userEdit.php">Account</a> &bull; <a href="logout.php">Logout</a></div>';
					}
				?>
			</div>
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
