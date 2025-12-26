<?php 
header("Access-Control-Allow-Origin: *");

require '../config/dbh.inc.php';

$Email = $_POST['Email'];

//$Email = "user786@krypton.com";
$MyUID = 0;
$json = ""; 


$sql_email = "SELECT * From users Where email = '$Email'";
$result_email = mysqli_query($conn,$sql_email) or die("Query Failed");
$resultCheck = mysqli_num_rows($result_email);
if($resultCheck > 0) {
	while($row = mysqli_fetch_assoc($result_email)) {
        $MyUID = $row["id"];
    }
    $sql_email = "SELECT * From news order by ID desc";
    $result_email = mysqli_query($conn,$sql_email) or die("Query Failed");
    $resultCheck = mysqli_num_rows($result_email);
    if($resultCheck > 0) {
   
        $json = $json . "[";
        while($row = mysqli_fetch_assoc($result_email)) {
            $NewsID = $row['ID'];
            $sql = "SELECT * From news_like WHERE News_ID = '$NewsID' AND User_ID = '$MyUID'";
            $result = mysqli_query($conn,$sql) or die("Query Failed");
            $resultCheck1 = mysqli_num_rows($result);
            if($resultCheck1 > 0) {
                $json = $json . '{"ID":"'.$row["ID"].'"
                    ,"Image":"'.$row["Image"].'"
                    ,"Title":"'.$row["Title"].'"
                    ,"Description":"'.$row["Description"].'"
                    ,"CreatedAt":"'.$row["CreatedAt"].'"
                    ,"AdShow":"'.$row["AdShow"].'"
                    ,"RAdShow":"'.$row["RAdShow"].'"
                    ,"Likes":"'.$row["Likes"].'"
                    ,"isliked":"1"
                    ,"Status":"'.$row["Status"].'"
                },';
            }
            else{
                $json = $json . '{"ID":"'.$row["ID"].'"
                    ,"Image":"'.$row["Image"].'"
                    ,"Title":"'.$row["Title"].'"
                    ,"Description":"'.$row["Description"].'"
                    ,"CreatedAt":"'.$row["CreatedAt"].'"
                    ,"AdShow":"'.$row["AdShow"].'"
                    ,"RAdShow":"'.$row["RAdShow"].'"
                    ,"Likes":"'.$row["Likes"].'"
                    ,"isliked":"0"
                    ,"Status":"'.$row["Status"].'"
                },';
            }
        }
        $json = $json .  "]";
        echo str_replace("},]","}]",$json);
    }
}
mysqli_close($conn);
?>