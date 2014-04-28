<?php

	require_once 'includes/session.php';
	require_once 'includes/connection.php';
	require_once 'includes/password.php';

	if (!$site_uses_auth) { header('Location: ' . $site_base_url); }
	if (!isset($_SESSION['userID'])) { header('Location: ' . $site_base_url); exit("Not logged in"); }

	// No user means we load the logged-in user
	if (isset($_GET['userID'])) {
		$uid = $_GET['userID'];
	} else {
		$uid = $_SESSION['userID'];
	}

	// If we're not an admin, force the user we're editing to the user we're logged in as
	if ($_SESSION['userClass'] > 0) {
		$uid = $_SESSION['userID'];
	}
	
	$row = array();
	$userDataStmt = mysqli_prepare($dbc, 'select userID, userName, userPass, userEmail, userClass from users where userID = ?');
	mysqli_stmt_bind_param($userDataStmt, 'i', $uid);
	mysqli_stmt_execute($userDataStmt) or die('Failed to get user data: ' . mysqli_error($dbc));
	mysqli_stmt_store_result($userDataStmt);
	mysqli_stmt_bind_result($userDataStmt, $row['userID'], $row['userName'], $row['userPass'], $row['userEmail'], $row['userClass']);
	mysqli_stmt_fetch($userDataStmt);
	mysqli_stmt_free_result($userDataStmt);

	if($_POST) {
		try {
			mysqli_autocommit($dbc, false);

			if($_POST['newpw1'] && $_POST['newpw2']) {
				if($_SESSION['userClass'] > 0 && $uid != $_SESSION['userID']) throw new Exception('Tried to change another user\'s password');
				if($_SESSION['userClass'] > 0 && !password_verify($_POST['oldpw'], $row['userPass'])) throw new Exception('Failed to change password: Old password is invalid');
				if($_POST['newpw1'] != $_POST['newpw2']) throw new Exception('Failed to change password: New password mismatch');

				$stmt = mysqli_prepare($dbc, 'update users set userPass=? where userID=?');
				$newpwhash = password_hash($_POST['newpw1'], PASSWORD_DEFAULT);
				mysqli_stmt_bind_param($stmt, 'si', $newpwhash, $uid);
				if(!mysqli_stmt_execute($stmt)) throw new Exception('Failed to update password: ' . mysqli_error($dbc));
			}

			// Only let admins change some values
			if($_SESSION['userClass'] == 0) {
				$stmt = mysqli_prepare($dbc, 'update users set userEmail=?, userClass=? where userID=?');
				mysqli_stmt_bind_param($stmt, 'sii', $_POST['email'], $_POST['class'], $uid);
				if(!mysqli_stmt_execute($stmt)) throw new Exception('Failed to update account information: ' . mysqli_error($dbc));
			} else {
				$stmt = mysqli_prepare($dbc, 'update users set userEmail=? where userID=?');
				mysqli_stmt_bind_param($stmt, 'si', $_POST['email'], $uid);
				if(!mysqli_stmt_execute($stmt)) throw new Exception('Failed to update account information: ' . mysqli_error($dbc));
			}

			mysqli_commit($dbc);
			mysqli_autocommit($dbc, true);
			$_SESSION['dialogText'] = 'Successfully updated user data.';
		} catch (Exception $e) {
			mysqli_rollback($dbc);
			mysqli_autocommit($dbc, true);
			$_SESSION['dialogText'] = 'Error: ' . $e->getMessage();
		}
	}

	$page_title = $row['userName'] . ' - User edit';
	require_once 'includes/header.php';

	// reuse $userDataStmt from above
	mysqli_stmt_execute($userDataStmt) or die('Failed to get user data: ' . mysqli_error($dbc));
	mysqli_stmt_store_result($userDataStmt);
	mysqli_stmt_bind_result($userDataStmt, $row['userID'], $row['userName'], $row['userPass'], $row['userEmail'], $row['userClass']);
	mysqli_stmt_fetch($userDataStmt);
	mysqli_stmt_free_result($userDataStmt);

	echo '<h1>Edit user '.$row['userName'].'</h1>
		<form method="post">
		<table>
			<tr><th>Account information</th><td></td></tr>
			<tr><th><label for="username">Username</label></th><td><input id="username" name="username" type="text" disabled="disabled" value="'.$row['userName'].'"/></td></tr>
			<tr><th><label for="email">Email</label></th><td><input id="email" name="email" type="email" value="'.$row['userEmail'].'"/></td></tr>';
			// Only admins are allowed to see these fields
			if ($_SESSION['userClass'] == 0) {
				echo '<tr><th><label for="class">Class</label></th><td><input id="class" name="class" type="text" value="'.$row['userClass'].'" min="0" title="The lower the number, the more permissions. 0 = All permissions."/></td>';
			}
			echo '<tr><th>Password</th><td>(leave blank to keep current password)</td></tr>';
			// Admins don't need to know the user's old password to change it
			if($_SESSION['userClass'] > 0) {
				echo '<tr><th><label for="oldpw">Old password</label></th><td><input id="oldpw" name="oldpw" type="password"/></td></tr>';
			}
			echo '<tr><th><label for="newpw1">New password</label></th><td><input id="newpw1" name="newpw1" type="password"/></td></tr>
			<tr><th><label for="newpw2">Confirm password</label></th><td><input id="newpw2" name="newpw2" type="password"/></td></tr>
			<tr><th><input id="submit" name="submit" type="submit"/></th><td></td></tr>
		</table>
	</form>';

	$jsOutput .= '$("#class").spinner();';
	require_once 'includes/footer.php';

?>
