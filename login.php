<?php

	$page_title = 'Log in';
	require_once 'includes/header.php';
	require_once 'includes/connection.php';
	require_once 'includes/password.php';

	if (!$site_uses_auth) {
		header('Location: ' . $site_base_url);
	}

	if ($_POST) {

		$row = array();
		$stmt = mysqli_prepare($dbc, 'select userID, userName, userPass, userEmail, userClass from users where userName=?');
		mysqli_bind_param($stmt, 's', $_POST['username']);
		mysqli_stmt_execute($stmt) or die('Failed to look up user: ' . mysqli_error($dbc));
		mysqli_stmt_store_result($stmt);
		mysqli_stmt_bind_result($stmt, $row['userID'], $row['userName'], $row['userPass'], $row['userEmail'], $row['userClass']);
		mysqli_stmt_fetch($stmt);

		if (mysqli_stmt_num_rows($stmt) == 1 && password_verify($_POST['password'], $row['userPass'])) {

			// The log-in is OK so set the session vars, and redirect to the home page
			$_SESSION['userID'] = $row['userID'];
			$_SESSION['userName'] = $row['userName'];
			$_SESSION['userEmail'] = $row['userEmail'];
			$_SESSION['userClass'] = $row['userClass'];

			if (isset($_GET['from']) && $_GET['from']) {
				// Prevent someone from passing '?from=http://example.com/malicious-script.php'
				// by removing $site_base_url from the beginning, if applicable, then re-adding it.
				// Note that we use matching curly brackets as delimiters, not slashes.
				// preg_quote() is also used to ensure we're not passing PCRE special chars unescaped.
				$from = $site_base_url . preg_replace('{^'.preg_quote($site_base_url).'}', '', $_GET['from']);
			} else {
				$from = $site_base_url;
			}

			header('Location: ' . $from);

		} else {

			$_SESSION['dialogText'] = 'Invalid user name or password.';
			header('Location: ' . $site_base_url . 'login.php');

		}
	}

?>

<form method="post">
	<table>
		<tr>
			<th><label for="username">Username</label></th>
			<td><input type="text" name="username" id="username" autofocus value="<?php if (isset($_POST['username'])) { echo $_POST['username']; } ?>" /></td>
		</tr>
		<tr>
			<th><label for="password">Password</label></th>
			<td><input type="password" name="password" id="password" /></td>
		</tr>
	</table>
	<input class="submit" type="submit" name="submit" value="Log in" />
</form>

<?php
	require_once 'includes/footer.php';
?>
