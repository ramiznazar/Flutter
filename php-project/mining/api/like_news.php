<?php 
require '../config/dbh.inc.php';

$Email = $_POST['Email'];
$NewsID = $_POST['NewsID'];
// $Email = "user786@krypton.com";
// $NewsID = "1";
$MyUID = 0;
$TotalLike = 0;

$sql_email = "SELECT * From users Where email = '$Email'";
$result_email = mysqli_query($conn,$sql_email) or die("Query Failed");
$resultCheck = mysqli_num_rows($result_email);
if($resultCheck > 0) {
	while($row = mysqli_fetch_assoc($result_email)) {
        $MyUID = $row["id"];
    }
    $sql_email = "SELECT * From news WHERE ID = '$NewsID'";
    $result_email = mysqli_query($conn,$sql_email) or die("Query Failed");
    $resultCheck = mysqli_num_rows($result_email);
    if($resultCheck > 0) {
        while($row = mysqli_fetch_assoc($result_email)) {
            $TotalLike = $row["Likes"];
        }

        $sql_email = "SELECT * From news_like WHERE News_ID = '$NewsID' AND User_ID = '$MyUID'";
        $result_email = mysqli_query($conn,$sql_email) or die("Query Failed");
        $resultCheck = mysqli_num_rows($result_email);
        if($resultCheck > 0) {
            while($row = mysqli_fetch_assoc($result_email)) {

            }
            echo "ERROR";
        }
        else{
            $TotalLike = $TotalLike + 1;

            $sql = "UPDATE news SET Likes='$TotalLike' WHERE ID='$NewsID'";
            $result1 = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    
            $CreatedAt = time();
            $CreatedAt = date("Y-m-d-H:i:s", $CreatedAt);
    
            $sql ="insert into news_like
            (User_ID,News_ID,CreatedAt)
            values ('$MyUID','$NewsID','$CreatedAt')";
            $result = mysqli_query($conn,$sql) or die(mysqli_error($conn));
    
            echo "OK";
        }
    }
}
mysqli_close($conn);
?>