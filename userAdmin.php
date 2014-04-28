<?php

	require_once 'includes/session.php';
	require_once 'includes/connection.php';

	if (!$site_uses_auth) { header('Location: ' . $site_base_url); }
	if (!isset($_SESSION['userID'])) { header('Location: ' . $site_base_url); exit('Not logged in'); }
	if ($_SESSION['userClass'] > 0) { header('HTTP/1.0 403 Forbidden'); die('Unauthorized'); } // Only let admins add users

	$page_title = 'User administration';
	require_once 'includes/header.php';

	if (isset($_POST) && isset($_POST['userIDs']) && isset($_POST['bulkclasssubmit'])) {

		$stmt = mysqli_prepare($dbc, 'update users set userClass=? where userID=?');
		foreach ($_POST['userIDs'] as $uid) {
			mysqli_stmt_bind_param($stmt, "ii", $_POST['class'], $uid);
			mysqli_stmt_execute($stmt) or die('Failed to assign class '.$_POST['class'].' to uid '.$uid.': '. mysqli_error($dbc));
		}

	}

	$query = 'select userID, userName, userEmail, userClass from users u';
	$result = mysqli_query($dbc, $query) or die('Error querying for users: ' . mysqli_error($dbc));

	?>
	<a href="userAdd.php">Add new user</a>
	<form method="post">
		<label for="class">Bulk class change</label>
		<input id="class" name="class" type="text" value="0" min="0" title="The lower the number, the more permissions. 0 = All permissions."/>
		<input type="submit" id="bulkclasssubmit" name="bulkclasssubmit" value="Apply">
		<table id="userlist"><thead><tr>
			<th></th>
			<th>User name</th>
			<th>User class</th>
			<th>Email</th>
			<th></th>
		</tr></thead><tbody>
	<?php
	while ($row = mysqli_fetch_assoc($result)) {
		echo '<tr>
				<td><input type="checkbox" name="userIDs[]" id="userID'.$row['userID'].'" value="'.$row['userID'].'"/></td>
				<td>'.$row['userName'].'</td>
				<td>'.$row['userClass'].'</td>
				<td><a href="mailto:'.$row['userEmail'].'">'.$row['userEmail'].'</a></td>
				<td><a href="userEdit.php?userID='.$row['userID'].'">edit</a></td>
			</tr>';
	}
	echo '</tbody></table></form>';

	$jsOutput .= '$("#class").spinner();';
	$jsOutput .= '$(document).ready( function(){$("#userlist").dataTable();} );';

	include("includes/footer.php");
?>
