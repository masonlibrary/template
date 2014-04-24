<?php
/* SQL QUERY TEMPLATES
 * Note: if there is ANY user input in a query string,
 * ALWAYS use a parameterized query.
 */

// Non-parameterized query, no results:
$result = mysqli_query($dbc, '') or die('Failed to execute query:' . mysqli_error($dbc));

// Non-parameterized query, one/first result:
$result = mysqli_query($dbc, '') or die('Failed to execute query:' . mysqli_error($dbc));
$row = mysqli_fetch_assoc($result);
echo $row['1'] . $row['2'];
mysqli_free_result($result);

// Non-parameterized query, multiple results:
$result = mysqli_query($dbc, '') or die('Failed to execute query:' . mysqli_error($dbc));
while ($row = mysqli_fetch_assoc($result)) {
	echo $row['1'] . $row['2'];
}
mysqli_free_result($result);

// Parameterized query, no results:
$stmt = mysqli_prepare($dbc, '');
mysqli_bind_param($stmt, 'is', $number, $string);
mysqli_stmt_execute($stmt) or die('Failed to execute query: ' . mysqli_error($dbc));

// Parameterized query, one/first result:
$row = array();
$stmt = mysqli_prepare($dbc, '');
mysqli_bind_param($stmt, 'is', $number, $string);
mysqli_stmt_execute($stmt) or die('Failed to execute query: ' . mysqli_error($dbc));
mysqli_stmt_store_result($stmt);
mysqli_stmt_bind_result($stmt, $row['1'], $row['2']);
mysqli_stmt_fetch($stmt);
echo $row['1'] . $row['2'];
mysqli_stmt_free_result($stmt);

// Parameterized query, multiple results:
$row = array();
$stmt = mysqli_prepare($dbc, '');
mysqli_bind_param($stmt, 'is', $number, $string);
mysqli_stmt_execute($stmt) or die('Failed to execute query: ' . mysqli_error($dbc));
mysqli_stmt_store_result($stmt);
mysqli_stmt_bind_result($stmt, $row['1'], $row['2']);
while (mysqli_stmt_fetch($stmt)) {
	echo $row['1'] . $row['2'];
}
mysqli_stmt_free_result($stmt);

// Transactional query (when you want all parts to succeed or fail together):
// Using this with results is left as an exercise to the reader, since it's
// probably not what you want. (Typically this is used for making multiple changes.)
try {
	mysqli_autocommit($dbc, false);

	$stmt = mysqli_prepare($dbc, '');
	mysqli_bind_param($stmt, 'is', $number, $string);
	if (!mysqli_stmt_execute($stmt)) throw new Exception('Failed to execute X query: ' . $stmt->error);

	$stmt = mysqli_prepare($dbc, '');
	mysqli_bind_param($stmt, 'is', $number, $string);
	if (!mysqli_stmt_execute($stmt)) throw new Exception('Failed to execute X query: ' . $stmt->error);

	mysqli_commit($dbc);
	mysqli_autocommit($dbc, true);
} catch (Exception $e) {
	mysqli_rollback($dbc);
	mysqli_autocommit($dbc, true);
	die("Couldn't X: " . $e->getMessage());
}

/* ADVANCED TOPICS */

// Dynamic parameterized query, no results:
// @TODO?

// Performing multiple actions with one query (with example data):
$stmt = mysqli_prepare($dbc, 'insert into outcomedetail (otcdotchID, otcdName) values (?, ?)');
foreach ($_POST['outcomeDetails'] as $detail) {
	if(!$detail) { continue; }
	mysqli_bind_param($stmt, "is", $otchID, $detail);
	mysqli_stmt_execute($stmt) or die('Failed to insert outcome detail: ' . mysqli_error($dbc));
}
?>