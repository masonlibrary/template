<?php

	require_once 'includes/session.php';
	require_once 'includes/connection.php';
	require_once 'includes/password.php';

	if (!$site_uses_auth) { header('Location: ' . $site_base_url); }
	if (!isset($_SESSION['userID'])) { header('Location: ' . $site_base_url); exit("Not logged in"); }
	if ($_SESSION['userClass'] > 0) { header("HTTP/1.0 403 Forbidden"); die("Unauthorized"); } // Only let admins add users
	
	if($_POST) {
		try {
			mysqli_autocommit($dbc, false);

			if(!$_POST['newpw1']) throw new Exception("Couldn't add user: Blank password");
			if($_POST['newpw1'] != $_POST['newpw2']) throw new Exception('Failed to add user: Password mismatch');

			$stmt = mysqli_prepare($dbc, 'insert into users (userName, userPass, userEmail, userClass) values (?, ?, ?, ?)');
			$newpwhash = password_hash($_POST['newpw1'], PASSWORD_DEFAULT);
			mysqli_stmt_bind_param($stmt, 'sssi', $_POST['username'], $newpwhash, $_POST['email'], $_POST['class']);
			if(!mysqli_stmt_execute($stmt)) throw new Exception('Failed to add user: ' . mysqli_error($dbc));
			
			mysqli_commit($dbc);
			
			$_SESSION['dialogText'] = 'User ' . $_POST['username'] . ' added successfully.';
			
			mysqli_autocommit($dbc, true);
		} catch (Exception $e) {
			mysqli_rollback($dbc);
			mysqli_autocommit($dbc, true);
			$_SESSION['dialogText'] = 'Error: ' . $e->getMessage();
		}
	}

	$page_title = 'Add user';
	require_once 'includes/header.php';

?>

<form method="post">
	<table>
		<tr>
			<th>Account information</th>
			<td></td>
		</tr>
		<tr>
			<th><label for="username">Username</label></th>
			<td><input id="username" name="username" type="text" maxlength="16"/></td>
		</tr>
		<tr>
			<th><label for="email">Email</label></th>
			<td><input id="email" name="email" type="email" maxlength="255"/></td>
		</tr>
		<tr>
			<th><label for="class">Class</label></th>
			<!-- Currently only Chrome and Opera support type="number", so for now we have to use jQuery UI. -->
			<td><input id="class" name="class" type="text" value="0" min="0" title="The lower the number, the more permissions. 0 = All permissions."/></td>
		</tr>
		<tr>
			<th>Password</th>
			<td></td>
		</tr>
		<tr>
			<th><label for="newpw1">New password</label></th>
			<td><input id="newpw1" name="newpw1" type="password"/></td>
		</tr>
		<tr>
			<th><label for="newpw2">Confirm password</label></th>
			<td><input id="newpw2" name="newpw2" type="password"/></td>
		</tr>
		<tr>
			<th><input id="submit" name="submit" type="submit"/></th>
			<td></td>
		</tr>
	</table>
</form>

<?php
	$jsOutput .= '$("#class").spinner();';
	require_once 'includes/footer.php';
?>
