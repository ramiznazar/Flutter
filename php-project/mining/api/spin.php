<?php 
require '../config/dbh.inc.php';

$json = array();

$sql_email = "SELECT * From spin";
$result_email = mysqli_query($conn,$sql_email) or die("Query Failed");
$resultCheck = mysqli_num_rows($result_email);  
if($resultCheck > 0) {
	while($row = mysqli_fetch_assoc($result_email)) {
        $json[] = $row;
    }
    echo json_encode($json);
}
mysqli_close($conn);
?>