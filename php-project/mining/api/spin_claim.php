<?php 
require '../config/dbh.inc.php';
require '../api/check_levels.php';

// $Email = "user786@krypton.com";
// $SpinID = "1";

$Email = $_POST['Email'];
$SpinID = $_POST['SpinID'];

$json = array();
$MyUID = 0;
$Token = 0;
$VIPToken = 0;
$Time=0;
$MaxLimit=0;
$Total=0;
$EndAt=0;
$StartedAt=0;

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
            if($Total == 0)
            {
                // $t=time();
                // $StartDate = date("H:i:s",$t);
                // $EndDate = date('H:i:s', strtotime($StartDate. ' + '.$Time.' seconds'));
                // $StartDate = date("Y-m-d-",$t).$StartDate;
                // $EndDate = date("Y-m-d-",$t).$EndDate;
            }
            else{
                if ($EndAt < date("Y-m-d-H:i:s")) {
                    $t=time();
                    $StartDate = date("Y-m-d-H:i:s",$t);
                    $startTime = date("Y-m-d H:i:s");
                    $EndDate = date('Y-m-d-H:i:s', strtotime('+'.$Time.' seconds', strtotime($startTime)));
                    
                    $sql = "UPDATE spin_cailmed SET Total='1', EndAt='$EndDate', StartedAt='$StartDate' WHERE UserID='$MyUID'";
                    $result1 = mysqli_query($conn, $sql) or die(mysqli_error($conn));

                    $Prize;
                    $Type;
                    $sql = "SELECT * FROM spin WHERE ID = '$SpinID'";
                    $result = mysqli_query($conn,$sql) or die("Query Failed");
                    $resultCheck = mysqli_num_rows($result);  
                    if($resultCheck > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $Prize = $row['Prize'];
                            $Type = $row['Type'];
                        }
                    }
    
                    if($Type == "Default")
                    {
                        $Token = $Token + $Prize;
                        $sql = "UPDATE users SET Token='$Token' WHERE ID='$MyUID'";
                        $result1 = mysqli_query($conn, $sql) or die(mysqli_error($conn));
                    }
                    // else{
                    //     $VIPToken = $VIPToken + $Prize;
                    //     $sql = "UPDATE user SET VIPToken='$VIPToken' WHERE ID='$MyUID'";
                    //     $result1 = mysqli_query($conn, $sql) or die(mysqli_error($conn));
                    // }
                    increaseUserSpinWheelLevel($MyUID, $conn);
                    echo "OK";
                }
                else{
                    if($Total < $MaxLimit)
                    {
                        $Total = $Total + 1;
                        $sql = "UPDATE spin_cailmed SET Total='$Total' WHERE UserID='$MyUID'";
                        $result1 = mysqli_query($conn, $sql) or die(mysqli_error($conn));

                        $Prize;
                        $Type;
                        $sql = "SELECT * FROM spin WHERE ID = '$SpinID'";
                        $result = mysqli_query($conn,$sql) or die("Query Failed");
                        $resultCheck = mysqli_num_rows($result);  
                        if($resultCheck > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                $Prize = $row['Prize'];
                                $Type = $row['Type'];
                            }
                        }
        
                        if($Type == "Default")
                        {
                            $Token = $Token + $Prize;
                            $sql = "UPDATE users SET Token='$Token' WHERE ID='$MyUID'";
                            $result1 = mysqli_query($conn, $sql) or die(mysqli_error($conn));
                        }
                        // else{
                        //     $VIPToken = $VIPToken + $Prize;
                        //     $sql = "UPDATE user SET VIPToken='$VIPToken' WHERE ID='$MyUID'";
                        //     $result1 = mysqli_query($conn, $sql) or die(mysqli_error($conn));
                        // }
                
                        increaseUserSpinWheelLevel($MyUID, $conn);
                        echo "OK";
                    }
                    else{
                        echo "LIMITED";
                    }
                }
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


                $Prize;
                $Type;
                $sql = "SELECT * FROM spin WHERE ID = '$SpinID'";
                $result = mysqli_query($conn,$sql) or die("Query Failed");
                $resultCheck = mysqli_num_rows($result);  
                if($resultCheck > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $Prize = $row['Prize'];
                        $Type = $row['Type'];
                    }
                }

                if($Type == "Default")
                {
                    $Token = $Token + $Prize;
                    $sql = "UPDATE users SET Token='$Token' WHERE ID='$MyUID'";
                    $result1 = mysqli_query($conn, $sql) or die(mysqli_error($conn));
                }
                // else{
                //     $VIPToken = $VIPToken + $Prize;
                //     $sql = "UPDATE user SET VIPToken='$VIPToken' WHERE ID='$MyUID'";
                //     $result1 = mysqli_query($conn, $sql) or die(mysqli_error($conn));
                // }
                increaseUserSpinWheelLevel($MyUID, $conn);
	            echo "OK";
        }
    }   
}
mysqli_close($conn);
?>