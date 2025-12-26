<?php
require '../config/dbh.inc.php';
$json = array();

$sql_query = "SELECT * FROM settings";
$result_query = mysqli_query($conn,$sql_query) or die("Query Failed");
$resultCheck = mysqli_num_rows($result_query);
if($resultCheck > 0) {
	while($row = mysqli_fetch_assoc($result_query)) {
        $json[] = $row;
    }
    echo json_encode($json);
}

mysqli_close($conn);
?>