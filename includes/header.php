<?php
	require_once 'includes/config.php';
	require_once 'includes/session.php';

	// Don't redirect-loop if we end with '/login.php'. Credit: http://stackoverflow.com/a/834355/217374
	if ($site_uses_auth && !isset($_SESSION['userID']) && !(substr($_SERVER['SCRIPT_NAME'], -strlen('/login.php'))==='/login.php')) {
		header('Location: login.php?from=' . rawurlencode($_SERVER['REQUEST_URI']));
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
			<a class="left" href="http://www.keene.edu"><img src="images/KSC-wordmark-150px.png" alt="Keene State College" /></a>
			<div class="left">
				<h1><a href="http://keene.edu/academics/library/">Wallace E. Mason Library</a></h1>
				<h1 id="site_title" ><?php echo '<a href="'.$site_base_url.'">'.$site_title.'</a>'; ?></h1>
			</div>
			<div class="right">
				<h2><?php echo $page_title; ?></h2>
				<?php
					if($site_uses_auth && isset($_SESSION['userID'])) {
						echo '<div>You are logged in as <strong>'.$_SESSION['userName'].'</strong>.</div>';
						echo '<div><a href="userEdit.php">Account</a> &bull; <a href="logout.php">Logout</a></div>';
					}
				?>
			</div>
			<?php
				if ($site_collapsible_menu) {
					echo '<div id="menu" style="display: none;">';
				} else {
					echo '<div id="menu">';
				}
			?>
				<div>
					<ul>
						<li><strong>test</strong></li>
						<li><a href="#">test</a></li>
						<li><a href="#">test</a></li>
					</ul>
				</div>
				<div>
					<ul>
						<li>test2</li>
						<li>test2</li>
						<li>test2</li>
					</ul>
				</div>
				<div>
					<ul>
						<li>test3</li>
						<li>test3</li>
						<li>test3</li>
					</ul>
				</div>
				<div>
					<ul>
						<li>test4</li>
						<li>test4</li>
						<li>test4</li>
					</ul>
				</div>
				<div>
					<ul>
						<li><strong>Admin</strong></li>
						<li><a href="userAdmin.php">Manage users</a></li>
						<li>test5</li>
					</ul>
				</div>
			</div> <!-- menu -->
		</header>
		<?php if ($site_collapsible_menu) { echo '<a id="tab"><div>menu</div></a>'; } ?>
		<div id="content">

			<?php
				if (isset($_SESSION['dialogTitle']) || isset($_SESSION['dialogText'])) {
					echo '<div id="messagebox" class="">';
						echo '<a id="messagebox-close" class="right">&#10006;</a>';
						if (isset($_SESSION['dialogTitle'])) { echo '<strong>' . $_SESSION['dialogTitle'] . '</strong><br/>'; }
						if (isset($_SESSION['dialogText'])) { echo $_SESSION['dialogText']; }
					echo '</div>';
				}
				unset($_SESSION['dialogTitle']);
				unset($_SESSION['dialogText']);

				echo '<h1>'.$page_title.'</h1>';

			?>
