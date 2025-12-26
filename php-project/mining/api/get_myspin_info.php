<?php 
require '../config/dbh.inc.php';

// $Email = "zxzayn@gmail.com";
// $SpinID = "1";
$Email = $_POST['Email'];
$json = array();
$MyUID = 0;
$Token = 0;
$VIPToken = 0;
$Time=0;
$MaxLimit=0;
$Total=0;
$EndAt=0;
$StartedAt=0;
$AdType = 0;
$ShowAd = 0;
$SpinShow = 0;

$sql_email = "SELECT * FROM users WHERE email = '$Email'";
$result_email = mysqli_query($conn,$sql_email) or die("Query Failed");
$resultCheck = mysqli_num_rows($result_email);  
if($resultCheck > 0) {
	while($row = mysqli_fetch_assoc($result_email)) {
        $MyUID = $row['id'];
        $Token = $row['token'];
    }

    $sql = "SELECT * FROM spin_setting";
    $result = mysqli_query($conn,$sql) or die("Query Failed");
    $resultCheck = mysqli_num_rows($result);  
    if($resultCheck > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $MaxLimit = $row['MaxLimit'];
            $Time = $row['Time'];
            $AdType = $row['AdType'];
            $ShowAd = $row['ShowAd'];
            
            $SpinShow = $row['SpinShow'];
        }

        $sql = "SELECT * FROM spin_cailmed WHERE UserID = '$MyUID'";
        $result = mysqli_query($conn,$sql) or die("Query Failed");
        $resultCheck = mysqli_num_rows($result);  
        if($resultCheck > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $Total = $row['Total'];
                $EndAt = $row['EndAt'];
                $StartedAt = $row['StartedAt'];
            }
            if ($EndAt < date("Y-m-d-H:i:s")) {
                $t=time();
                $StartDate = date("Y-m-d-H:i:s",$t);
                $startTime = date("Y-m-d H:i:s");
                $EndDate = date('Y-m-d-H:i:s', strtotime('+'.$Time.' seconds', strtotime($startTime)));

                $sql = "UPDATE spin_cailmed SET Total='1', EndAt='$EndDate', StartedAt='$StartDate' WHERE UserID='$MyUID'";
                $result1 = mysqli_query($conn, $sql) or die(mysqli_error($conn));
            }
        }
        else{
                $t=time();
                $StartDate = date("Y-m-d-H:i:s",$t);
                $startTime = date("Y-m-d H:i:s");
                $EndDate = date('Y-m-d-H:i:s', strtotime('+'.$Time.' seconds', strtotime($startTime)));

                $sql ="insert into spin_cailmed (UserID,Total,EndAt,StartedAt)
                values ('$MyUID','1','$EndDate','$StartDate')";
                $result = mysqli_query($conn,$sql) or die(mysqli_error($conn));
        }

        $sql = "SELECT * FROM spin_cailmed WHERE UserID = '$MyUID'";
        $result = mysqli_query($conn,$sql) or die("Query Failed");
        $resultCheck = mysqli_num_rows($result);  
        if($resultCheck > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $Total = $row['Total'];
                $EndAt = $row['EndAt'];
                $StartedAt = $row['StartedAt'];
            }
        }
        echo '[{
            "MaxLimit":"'.$MaxLimit.'"
            ,"Time":"'.$Time.'"
            ,"AdType":"'.$AdType.'"
            ,"ShowAd":"'.$ShowAd.'"
            ,"Total":"'.$Total.'"
            ,"EndAt":"'.$EndAt.'"
            ,"StartedAt":"'.$StartedAt.'"
            ,"CurrentTime":"'.date("Y-m-d-H:i:s").'"
            ,"SpinShow":"'.$SpinShow.'"
        }]';
    }
}
mysqli_close($conn);
?>