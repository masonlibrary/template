<?php
	require_once('control/connection.php');
    require_once('control/startSession.php');

	if (!isset($_SESSION['userID'])) { header("Location: login.php"); exit("Not logged in"); }
	if($_SESSION['roleID'] != 1) { header("HTTP/1.0 403 Forbidden"); die("Unauthorized"); }

	$page_title = 'User administration';
	include('includes/header.php');

	if (isset($_POST) && isset($_POST['userIDs'])) {
		if (isset($_POST['bulkrolesubmit'])) {

			switch ($_POST['bulkrole']) {
				case 'admin':
					$role = 1;
					break;
				case 'power':
					$role = 2;
					break;
				case 'user':
					$role = 3;
					break;
				default:
					break;
			}

			$stmt = mysqli_prepare($dbc, "update userroles set roleroleID=? where roleuserID=?");
			foreach ($_POST['userIDs'] as $uid) {
				mysqli_stmt_bind_param($stmt, "ii", $role, $uid);
				mysqli_stmt_execute($stmt) or die("Failed to assign role $role to uid $uid:" . mysqli_error($dbc));
			}

		} else if (isset($_POST['bulkstatussubmit'])) {

			switch ($_POST['bulkstatus']) {
				// Not strictly necessary, but this way we're sure to not use user input in the query. -Webster
				case 'active':
					$status = 'active';
					break;
				case 'inactive':
					$status = 'inactive';
					break;
				default:
					break;
			}

			$stmt = mysqli_prepare($dbc, "update librarianmap set libmStatus=? where libmuserID=?");
			foreach ($_POST['userIDs'] as $uid) {
				mysqli_stmt_bind_param($stmt, "si", $status, $uid);
				mysqli_stmt_execute($stmt) or die("Failed to assign status $status to uid $uid:" . mysqli_error($dbc));
			}

		}
	}

	$query = 'select userID, userName, roleName, libmStatus, ppleLName, ppleFName, ppleEmail from users u
		left outer join userroles ur on u.userID = ur.roleuserID
		left outer join roles r on ur.roleroleID = r.roleID
		left outer join librarianmap l on u.userID = l.libmuserID
		left outer join people p on l.libmppleID = p.ppleID';
	$result = mysqli_query($dbc, $query) or die('Error querying for users: ' . mysqli_error($dbc));

	?>
	<a href="userAdd.php">Add new user</a>
	<form method="post">
		<select id="bulkrole" name="bulkrole">
			<option value="" selected="selected">Change roles</option>
			<option value="admin">Admin</option>
			<option value="power">Power</option>
			<option value="user">User</option>
		</select>
		<input type="submit" id="bulkrolesubmit" name="bulkrolesubmit" value="Apply">
		<select id="bulkstatus" name="bulkstatus">
			<option value="" selected="selected">Change status</option>
			<option value="active">Active</option>
			<option value="inactive">Inactive</option>
		</select>
		<input type="submit" id="bulkstatussubmit" name="bulkstatussubmit" value="Apply">
		<table id="userlist"><thead><tr>
		<th></th>
		<th>User name</th>
		<th>Role</th>
		<th>Status</th>
		<th>Last name</th>
		<th>First name</th>
		<th>Email</th>
		<th></th>
		</tr></thead><tbody>
	<?php
	while ($row = mysqli_fetch_assoc($result)) {
		echo '<tr><td><input type="checkbox" name="userIDs[]" id="userID'.$row['userID'].'" value="'.$row['userID'].'"/></td><td>'.
			$row['userName'].'</td><td>'.
			$row['roleName'].'</td><td>'.
			$row['libmStatus'].'</td><td>'.
			$row['ppleLName'].'</td><td>'.
			$row['ppleFName'].'</td><td>'.
			'<a href="mailto:'.$row['ppleEmail'].'">'.$row['ppleEmail'].'</a></td><td>'.
			'<a href="userEdit.php?userID='.$row['userID'].'">edit</a></td></tr>';
	}
	echo '</tbody></table></form>';

	echo '<script type="text/javascript">$(document).ready( function(){$("#userlist").dataTable();} );</script>';

	include("includes/footer.php");
?>
