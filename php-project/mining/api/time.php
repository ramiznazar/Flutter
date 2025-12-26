<?php
echo date('Y-m-d H:i:s')."<br>";

// $IPaddress = '103.148.92.179';


$ip_json = file_get_contents('http://ipinfo.io/json');
$ip_info = json_decode($ip_json, true);
$user_ip = $ip_info['ip'];
echo "Your public IP address is: " . $user_ip;

$mm = ip_details($user_ip);

function ip_details($IPaddress) 
    {
        $json       = file_get_contents("http://ipinfo.io/{$IPaddress}");
        $details    = json_decode($json);
        return $details;
    }

    // $IPaddress  =   $_SERVER['REMOTE_ADDR'];

    // $details    =   ip_details("$IPaddress");

    // //echo $details->city;   #Tamilnadu  
    // echo $details->country;  
    //echo $details->org;      
    //echo $details->hostname; 

    echo "<br>";
    echo "Your country code is: " . $mm->country; 
    echo "<br>";

?>