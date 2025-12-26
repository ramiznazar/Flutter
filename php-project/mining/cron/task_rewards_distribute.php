<?php
/**
 * Task Rewards Distribution Cron Job
 * Automatically distributes rewards for one-time tasks after 1 hour
 * (One-time tasks give reward after 1 hour regardless of completion)
 * Should be run every 5-10 minutes via cron
 */
require '../config/dbh.inc.php';

// Find all one-time tasks where reward is available but not claimed
$sql = "SELECT tc.id, tc.user_id, tc.task_id, s.Token as reward
        FROM task_completions tc
        JOIN social_media_setting s ON tc.task_id = s.ID
        WHERE tc.task_type = 'onetime'
        AND tc.reward_claimed = 0
        AND tc.reward_available_at <= NOW()";
$result = $conn->query($sql);

$distributed = 0;
$errors = 0;

if ($result && $result->num_rows > 0) {
    $conn->begin_transaction();
    
    try {
        while ($row = $result->fetch_assoc()) {
            $completionId = $row['id'];
            $userId = $row['user_id'];
            $reward = (float)$row['reward'];
            
            // Mark as claimed
            $updateSql = "UPDATE task_completions 
                          SET reward_claimed = 1, reward_claimed_at = NOW() 
                          WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param('i', $completionId);
            $updateStmt->execute();
            
            // Add reward to user
            $rewardSql = "UPDATE users SET token = token + ? WHERE id = ?";
            $rewardStmt = $conn->prepare($rewardSql);
            $rewardStmt->bind_param('di', $reward, $userId);
            $rewardStmt->execute();
            
            $distributed++;
        }
        
        $conn->commit();
        echo "Distributed rewards for $distributed one-time tasks.\n";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error distributing rewards: " . $e->getMessage() . "\n";
        $errors = $result->num_rows;
    }
} else {
    echo "No rewards to distribute.\n";
}

$conn->close();
?>


