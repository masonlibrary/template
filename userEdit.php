<?php
	require_once('control/connection.php');
    require_once('control/startSession.php');

	if (!isset($_SESSION['userID'])) { header("Location: login.php"); exit("Not logged in"); }
	if (!isset($_GET['userID'])) { $_GET['userID'] = $_SESSION['userID']; } // No user means we load the logged-in user
	if (!($_SESSION['roleID'] == 1 || $_GET['userID']) == $_SESSION['userID']) { header("HTTP/1.0 403 Forbidden"); die("Unauthorized"); }

	// If we're not an admin, force the user we're editing to the user we're logged in as
	$uid = ($_SESSION['roleID'] == 1 ? $_GET['userID'] : $_SESSION['userID']);

	$row = array();
	$userDataStmt = mysqli_prepare($dbc, 'select userID, userName, userPass, roleroleID, libmStatus, ppleID, ppleFName, ppleLName, ppleEmail from users u
		left outer join userroles ur on u.userID = ur.roleuserID
		left outer join roles r on ur.roleroleID = r.roleID
		left outer join librarianmap l on u.userID = l.libmuserID
		left outer join people p on l.libmppleID = p.ppleID
		where u.userid = ?');
	mysqli_stmt_bind_param($userDataStmt, "i", $uid);
	mysqli_stmt_execute($userDataStmt) or die("Failed to get user data: " . mysqli_error($dbc));
	mysqli_stmt_store_result($userDataStmt);
	mysqli_stmt_bind_result($userDataStmt, $row['userID'], $row['userName'], $row['userPass'], $row['roleroleID'],
		$row['libmStatus'], $row['ppleID'], $row['ppleFName'], $row['ppleLName'], $row['ppleEmail']);
	mysqli_stmt_fetch($userDataStmt);
	mysqli_stmt_free_result($userDataStmt);
//	$ppleID = $row['ppleID']; // used for setting real name below

	if($_POST) {
		try {
			mysqli_autocommit($dbc, false);

			if($_POST['newpw1'] && $_POST['newpw2']) {
				if($uid != $_SESSION['userID'] && $_SESSION['roleID'] != 1) throw new Exception("Tried to change another user's password");
				if($_SESSION['roleID'] != 1 && $row['userPass'] != sha1($_POST['oldpw'])) throw new Exception("Couldn't change password: Old password is invalid");
				if($_POST['newpw1'] != $_POST['newpw2']) throw new Exception("Couldn't change password: New passwords don't match");

				$stmt = mysqli_prepare($dbc, 'update users set userPass=? where userID=?');
				$newpwhash = sha1($_POST['newpw1']);
				mysqli_stmt_bind_param($stmt, 'si', $newpwhash, $uid);
				if(!mysqli_stmt_execute($stmt)) throw new Exception("Couldn't update password: " . mysqli_error($dbc));
			}

			$stmt = mysqli_prepare($dbc, 'update people set ppleFName=?, ppleLName=?, ppleEmail=? where ppleID=?');
			mysqli_stmt_bind_param($stmt, 'sssi', $_POST['fname'], $_POST['lname'], $_POST['email'], $row['ppleID']);
			if(!mysqli_stmt_execute($stmt)) throw new Exception("Couldn't update account information: " . mysqli_error($dbc));

			// Only let admins change these values
			if($_SESSION['roleID'] == 1) {
				$stmt = mysqli_prepare($dbc, 'update userroles set roleroleID=? where roleuserID=?');
				mysqli_stmt_bind_param($stmt, 'ii', $_POST['role'], $uid);
				if(!mysqli_stmt_execute($stmt)) throw new Exception("Couldn't update user role: " . mysqli_error($dbc));

				// Only handle valid values
				if($_POST['status']=='active' || $_POST['status']=='inactive') {
					$stmt = mysqli_prepare($dbc, 'update librarianmap set libmStatus=? where libmuserID=?');
					mysqli_stmt_bind_param($stmt, 'si', $_POST['status'], $uid);
					if(!mysqli_stmt_execute($stmt)) throw new Exception("Couldn't update user status: " . mysqli_error($dbc));
				}
			}

			mysqli_commit($dbc);
			mysqli_autocommit($dbc, true);
		} catch (Exception $e) {
			mysqli_rollback($dbc);
			mysqli_autocommit($dbc, true);
			echo "Error: " . $e->getMessage();
		}
	}

	$page_title = $row['userName'] . ' - User edit';
	include('includes/header.php');

	// reuse $userDataStmt from above
	mysqli_stmt_execute($userDataStmt) or die("Failed to get user data: " . mysqli_error($dbc));
	mysqli_stmt_store_result($userDataStmt);
	mysqli_stmt_bind_result($userDataStmt, $row['userID'], $row['userName'], $row['userPass'], $row['roleroleID'],
		$row['libmStatus'], $row['ppleID'], $row['ppleFName'], $row['ppleLName'], $row['ppleEmail']);
	mysqli_stmt_fetch($userDataStmt);
	mysqli_stmt_free_result($userDataStmt);

	echo '<h1>Edit user '.$row['userName'].'</h1>
		<form method="post">
		<table>
			<tr><th>Account information</th><td></td></tr>
			<tr><th><label for="username">Username</label></th><td><input id="username" name="username" type="text" disabled="disabled" value="'.$row['userName'].'"/></td></tr>
			<tr><th><label for="fname">First name</label></th><td><input id="fname" name="fname" type="text" value="'.$row['ppleFName'].'"/></td></tr>
			<tr><th><label for="lname">Last name</label></th><td><input id="lname" name="lname" type="text" value="'.$row['ppleLName'].'"/></td></tr>
			<tr><th><label for="email">Email</label></th><td><input id="email" name="email" type="email" value="'.$row['ppleEmail'].'"/></td></tr>';
			if ($_SESSION['roleID'] == 1) { // Only admins are allowed to see these fields
				echo '<tr><th><label for="role">Role</label></th><td><select id="role" name="role">';

				$s = array();
				$stmt = mysqli_prepare($dbc, 'select roleID, roleName from roles');
				mysqli_stmt_execute($stmt) or die("Failed to retrieve roles: " . mysqli_error($dbc));
				mysqli_stmt_store_result($stmt);
				mysqli_stmt_bind_result($stmt, $s['roleID'], $s['roleName']);
				while (mysqli_stmt_fetch($stmt)) {
					echo '<option value="'.$s['roleID'].'" '.($s['roleID'] == $row['roleroleID'] ? 'selected="selected"' : '').'>'.$s['roleName'].'</option>';
				}
				mysqli_stmt_free_result($stmt);

				echo '</select></td></tr>
				<tr><th><label for="status">Status</label></th><td>
					<select id="status" name="status">
						<option value="active"'.($row['libmStatus'] == 'active' ? ' selected="selected"' : '' ).'>Active</option>
						<option value="inactive"'.($row['libmStatus'] == 'inactive' ? ' selected="selected"' : '' ).'>Inactive</option>
					</select>
				</td></tr>';
			}
			echo '<tr><th>Password</th><td>(leave blank to keep current password)</td></tr>';
			// Admins don't need to know the user's old password to change it
			if($_SESSION['roleID'] != 1) echo '<tr><th><label for="oldpw">Old password</label></th><td><input id="oldpw" name="oldpw" type="password"/></td></tr>';
			echo '<tr><th><label for="newpw1">New password</label></th><td><input id="newpw1" name="newpw1" type="password"/></td></tr>
			<tr><th><label for="newpw2">Confirm password</label></th><td><input id="newpw2" name="newpw2" type="password"/></td></tr>
			<tr><th><input id="submit" name="submit" type="submit"/></th><td></td></tr>
		</table>
	</form>';

	include("includes/footer.php");
?>
