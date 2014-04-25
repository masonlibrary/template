<?php
	require_once('control/connection.php');
	require_once('control/startSession.php');

	if (!isset($_SESSION['userID'])) { header("Location: login.php"); exit("Not logged in"); }
	if ($_SESSION['roleID'] != 1) { header("HTTP/1.0 403 Forbidden"); die("Unauthorized"); } // Only let admins add users
	
	$page_title = 'Add user';
	include('includes/header.php');
	
	if($_POST) {
		try {
			mysqli_autocommit($dbc, false);

			if(!$_POST['newpw1']) throw new Exception("Couldn't add user: Blank password");
			if($_POST['newpw1'] != $_POST['newpw2']) throw new Exception("Couldn't add user: Passwords don't match");
			$stmt = mysqli_prepare($dbc, 'insert into users (userName, userPass) values (?, ?)');
			$newpwhash = sha1($_POST['newpw1']);
			mysqli_stmt_bind_param($stmt, 'ss', $_POST['username'], $newpwhash);
			if(!mysqli_stmt_execute($stmt)) throw new Exception("Couldn't add user: " . mysqli_error($dbc));
			
			// Grab out the new user ID
			$result = mysqli_query($dbc, 'select LAST_INSERT_ID() as userID');
			if(!$result) throw new Exception("Couldn't select new user ID: " . mysqli_error($dbc));
			$row = mysqli_fetch_assoc($result);
			if(!$row) throw new Exception("Couldn't fetch new user ID: " . mysqli_error($dbc));
			$userID = $row['userID'];

			$stmt = mysqli_prepare($dbc, 'insert into people (ppleFName, ppleLName, ppleEmail) values (?, ?, ?)');
			mysqli_stmt_bind_param($stmt, 'sss', $_POST['fname'], $_POST['lname'], $_POST['email']);
			if(!mysqli_stmt_execute($stmt)) throw new Exception("Couldn't add account information: " . mysqli_error($dbc));
			
			// Grab out the new people ID
			$result = mysqli_query($dbc, 'select LAST_INSERT_ID() as ppleID');
			if(!$result) throw new Exception("Couldn't select new people ID: " . mysqli_error($dbc));
			$row = mysqli_fetch_assoc($result);
			if(!$row) throw new Exception("Couldn't fetch new people ID: " . mysqli_error($dbc));
			$ppleID = $row['ppleID'];

			$stmt = mysqli_prepare($dbc, 'insert into userroles (roleuserID, roleroleID) values (?, ?)');
			mysqli_stmt_bind_param($stmt, 'ii', $userID, $_POST['role']);
			if(!mysqli_stmt_execute($stmt)) throw new Exception("Couldn't add user role: " . mysqli_error($dbc));

			// Only handle valid values for librarianmap
			if(!($_POST['status']=='active' || $_POST['status']=='inactive')) throw new Exception("Couldn't add librarian map: Invalid status");
			
			$stmt = mysqli_prepare($dbc, 'insert into librarianmap (libmuserID, libmppleID, libmStatus) values (?, ?, ?)');
			mysqli_stmt_bind_param($stmt, 'iis', $userID, $ppleID, $_POST['status']);
			if(!mysqli_stmt_execute($stmt)) throw new Exception("Couldn't update user status: " . mysqli_error($dbc));

			mysqli_commit($dbc);
			
			echo "User " . $_POST['username'] . 'added successfully.';
			
			mysqli_autocommit($dbc, true);
		} catch (Exception $e) {
			mysqli_rollback($dbc);
			mysqli_autocommit($dbc, true);
			echo "Error: " . $e->getMessage();
		}

	}
	
	echo '<h1>Add user</h1>
		<form method="post">
		<table>
			<tr><th>Account information</th><td></td></tr>
			<tr><th><label for="username">Username</label></th><td><input id="username" name="username" type="text"/></td></tr>
			<tr><th><label for="fname">First name</label></th><td><input id="fname" name="fname" type="text"/></td></tr>
			<tr><th><label for="lname">Last name</label></th><td><input id="lname" name="lname" type="text"/></td></tr>
			<tr><th><label for="email">Email</label></th><td><input id="email" name="email" type="email"/></td></tr>
			<tr><th><label for="role">Role</label></th><td><select id="role" name="role">';

			$s = array();
			$stmt = mysqli_prepare($dbc, 'select roleID, roleName from roles');
			mysqli_stmt_execute($stmt) or die("Failed to retrieve roles: " . mysqli_error($dbc));
			mysqli_stmt_store_result($stmt);
			mysqli_stmt_bind_result($stmt, $s['roleID'], $s['roleName']);
			while (mysqli_stmt_fetch($stmt)) {
				echo '<option value="'.$s['roleID'].'">'.$s['roleName'].'</option>';
			}
			mysqli_stmt_free_result($stmt);

			echo '</select></td></tr>
			<tr><th><label for="status">Status</label></th><td>
				<select id="status" name="status">
					<option value="active">Active</option>
					<option value="inactive">Inactive</option>
				</select>
			</td></tr>
			<tr><th>Password</th><td></td></tr>
			<tr><th><label for="newpw1">New password</label></th><td><input id="newpw1" name="newpw1" type="password"/></td></tr>
			<tr><th><label for="newpw2">Confirm password</label></th><td><input id="newpw2" name="newpw2" type="password"/></td></tr>
			<tr><th><input id="submit" name="submit" type="submit"/></th><td></td></tr>
		</table>
	</form>';
	
	include("includes/footer.php");
?>
