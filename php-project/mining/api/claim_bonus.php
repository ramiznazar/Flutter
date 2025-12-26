<?php
// get the email and password from the request body
$email = $_POST['email'];
$password = $_POST['password'];

// validate the email and password
if (empty($email) || empty($password)) {
  $response = array('success' => false, 'message' => 'Email and password are required');
  echo json_encode($response);
  exit;
}

//  Connection file
require '../config/dbh.inc.php';

// check if the account is active
$sql = "SELECT id FROM users WHERE email = '$email' AND password = '$password' AND account_status = 'active'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
  $response = array('success' => false, 'message' => 'Invalid email or password, or account is not active');
  echo json_encode($response);
  exit;
}

// get the user's id
$row = $result->fetch_assoc();
$user_id = $row['id'];

// get the current server time
$current_time = date('Y-m-d H:i:s');

// remove any expired records from the token_bonus_history table
$sql = "DELETE FROM token_bonus_history WHERE expire_at <= '$current_time'";
$conn->query($sql);

// query the token_bonus_history table for records with the user's id
$sql = "SELECT id, from_user_id, amount, expire_at FROM token_bonus_history WHERE to_user_id = '$user_id'";
$result = $conn->query($sql);

// update the user's token balance and delete the record from the token_bonus_history table
while ($row = $result->fetch_assoc()) {
  $from_user_id = $row['from_user_id'];
  $amount = $row['amount'];

  // update the user's token balance
  $sql2 = "UPDATE users SET token = token + '$amount' WHERE id = '$user_id'";
  $conn->query($sql2);

  // delete the record from the token_bonus_history table
  $record_id = $row['id'];
  $sql3 = "DELETE FROM token_bonus_history WHERE id = '$record_id'";
  $conn->query($sql3);
}

// construct the response object
$response = array('success' => true, 'message' => 'Tokens transferred successfully');

// return the response object as JSON
echo json_encode($response);

// close the database connection
$conn->close();
?>
