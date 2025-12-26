<?php

function checkRecord($userId, $conn)
{
    // Get user from table "user_levels" table and check if user does not exist than create a new record.
    // Columns are user_id, mining_session, spin_wheel, current_level, achieved_at.
    // Get id of first item from table "levels" and store it in user_levels column current_level.

    $sql = "SELECT * FROM `level` LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 0) {
        echo json_encode(array('success' => false, 'message' => 'Level settings not found'));

        // Close database connection
        mysqli_close($conn);

        exit;
    }

    $level_settings = mysqli_fetch_assoc($result);
    $level_id = $level_settings['id'];

    mysqli_num_rows(mysqli_query($conn, "SELECT * FROM user_levels WHERE user_id = $userId")) || mysqli_query($conn, "INSERT INTO user_levels (user_id, mining_session, spin_wheel, current_level, achieved_at) VALUES ($userId, 0, 0, $level_id, NOW())");
}

function increaseUserMiningLevel($userId, $conn)
{
    $userId = (int)$userId; // Sanitize user input to prevent SQL injection

    $query = "UPDATE user_levels SET mining_session = mining_session + 1 WHERE user_id = $userId";

    try {
        $conn->query($query);
        // You can add additional logic here if needed
        return true; // Success
    } catch (Exception $e) {
        // Handle the exception as needed
        error_log("Error updating mining session level: " . $e->getMessage());
        return false; // Failed
    }
}

function increaseUserSpinWheelLevel($userId, $conn)
{
    $userId = (int)$userId; // Sanitize user input to prevent SQL injection

    $query = "UPDATE user_levels SET spin_wheel = spin_wheel + 1 WHERE user_id = $userId";

    try {
        $conn->query($query);
        // You can add additional logic here if needed
        return true; // Success
    } catch (Exception $e) {
        // Handle the exception as needed
        error_log("Error updating spin wheel level: " . $e->getMessage());
        return false; // Failed
    }
}

function getUserLevel($userId, $conn)
{
    $userId = (int)$userId; // Sanitize user input to prevent SQL injection

    // Fetch user's mining_session, spin_wheel, total_invite, join_date from 'user_levels' and 'users'
    $query = "SELECT u.mining_session, u.spin_wheel, u.current_level, us.total_invite, us.join_date 
              FROM user_levels u
              JOIN users us ON u.user_id = us.id
              WHERE u.user_id = $userId";
    $result = $conn->query($query);

    if ($result === false) {
        // Handle the query error as needed
        error_log("Error fetching user levels: " . $conn->error);
        return false; // Failed
    }

    $userLevels = $result->fetch_assoc();

    // Fetch level criteria from 'level' table
    $query = "SELECT * FROM level";
    $result = $conn->query($query);

    if ($result === false) {
        // Handle the query error as needed
        error_log("Error fetching level criteria: " . $conn->error);
        return false; // Failed
    }

    $levels = $result->fetch_all(MYSQLI_ASSOC);

    // Find the highest level the user has achieved
    $highestLevelId = $userLevels['current_level'];

    // Determine the user's level based on criteria
    foreach ($levels as $level) {
        if (
            $userLevels['mining_session'] >= $level['mining_sessions'] &&
            $userLevels['spin_wheel'] >= $level['spin_wheel'] &&
            $userLevels['total_invite'] >= $level['total_invite'] &&
            strtotime($userLevels['join_date']) <= strtotime('-' . $level['user_account_old'] . ' days')
        ) {
            $levelId = $level['id'];

            if ($levelId > $highestLevelId) {
                // Update 'current_level' in 'user_levels' table
                $updateQuery = "UPDATE user_levels SET current_level = $levelId WHERE user_id = $userId";
                $conn->query($updateQuery);

                // Give one-time 'perk_crutox_reward' to user in 'token' column
                $reward = $level['perk_crutox_reward'];
                $rewardQuery = "UPDATE users SET token = token + $reward WHERE id = $userId";
                $conn->query($rewardQuery);

                // Update the highest level achieved
                $highestLevelId = $levelId;
            }
        }
    }

    // Prepare the perks data for the highest achieved level
    $highestLevel = $levels[$highestLevelId - 1];
    $perks = array(
        "crutox_per_time" => $highestLevel['perk_crutox_per_time'],
        "mining_time" => $highestLevel['perk_mining_time'],
        "crutox_reward" => $highestLevel['perk_crutox_reward'],
        "other_access" => $highestLevel['perk_other_access']
    );

    // Prepare the perks and criteria data for the next level
    $nextLevelId = $highestLevelId + 1;
    $nextLevel = isset($levels[$nextLevelId - 1]) ? $levels[$nextLevelId - 1] : null;
    $nextLevelPerks = $nextLevel ? array(
        "crutox_per_time" => $nextLevel['perk_crutox_per_time'],
        "mining_time" => $nextLevel['perk_mining_time'],
        "crutox_reward" => $nextLevel['perk_crutox_reward'],
        "other_access" => $nextLevel['perk_other_access']
    ) : null;
    $nextLevelCriteria = $nextLevel ? array(
        "mining_sessions" => $nextLevel['mining_sessions'],
        "spin_wheel" => $nextLevel['spin_wheel'],
        "total_invite" => $nextLevel['total_invite'],
        "user_account_old" => $nextLevel['user_account_old']
    ) : null;

    // Return the result as JSON
    return json_encode(array(
        "current_level" => array(
            "level" => $highestLevel['lvl_name'],
            "level_id" => $highestLevelId,
            "perks" => $perks,
            "stats" => array(
                "total_mined_sessions" => $userLevels['mining_session'],
                "total_spun_wheels" => $userLevels['spin_wheel'],
                "old_account_days" => floor((time() - strtotime($userLevels['join_date'])) / (60 * 60 * 24)),
                "total_invites" => $userLevels['total_invite']
            )
        ),
        "next_level" => array(
            "level" => $nextLevel ? $nextLevel['lvl_name'] : "Max Level Reached",
            "level_id" => $nextLevelId,
            "perks" => $nextLevelPerks,
            "criteria" => $nextLevelCriteria
        )
    ));
}

function getBadges($userId, $conn)
{
    $userId = (int)$userId; // Sanitize user input to prevent SQL injection

    // Fetch user's data from 'user_levels' and 'users'
    $query = "SELECT u.mining_session, u.spin_wheel, us.total_invite, us.token, us.join_date 
              FROM user_levels u
              JOIN users us ON u.user_id = us.id
              WHERE u.user_id = $userId";
    $result = $conn->query($query);

    if ($result === false) {
        // Handle the query error as needed
        error_log("Error fetching user data for badges: " . $conn->error);
        return false; // Failed
    }

    $userData = $result->fetch_assoc();

    // Fetch badge criteria from 'badges' table
    $query = "SELECT * FROM badges";
    $result = $conn->query($query);

    if ($result === false) {
        // Handle the query error as needed
        error_log("Error fetching badge criteria: " . $conn->error);
        return false; // Failed
    }

    $badges = $result->fetch_all(MYSQLI_ASSOC);

    // Fetch total social media tasks from 'social_media_setting'
    $query = "SELECT COUNT(id) AS total_tasks FROM social_media_setting";
    $result = $conn->query($query);

    if ($result === false) {
        // Handle the query error as needed
        error_log("Error fetching total social media tasks: " . $conn->error);
        return false; // Failed
    }

    $totalSocialMediaTasks = $result->fetch_assoc()['total_tasks'];

    // Fetch the count of completed social media tasks for the user from 'social_media_tokens'
    $query = "SELECT COUNT(DISTINCT social_media_id) AS completed_tasks FROM social_media_tokens WHERE user_id = $userId";
    $result = $conn->query($query);

    if ($result === false) {
        // Handle the query error as needed
        error_log("Error fetching completed social media tasks: " . $conn->error);
        return false; // Failed
    }

    $completedSocialMediaTasks = $result->fetch_assoc()['completed_tasks'];

    // Determine which badges the user has earned and their progress
    $earnedBadges = array();

    // Check for the 'Account Created' badge separately
    $accountCreatedBadge = [
        'title' => 'Newbie Explorer: Once User Creates Account',
        'earned' => isset($userData['join_date']) ? true : false,
        'progress' => null,
        'total' => null,
        'badges_icon' => null
    ];

    $earnedBadges[] = $accountCreatedBadge;

    foreach ($badges as $badge) {
        // $badgeName = str_replace(' ', ' ', $badge['badge_name']); // Replace spaces with underscores
        $badgeName = $badge['badge_name']; // Replace spaces with underscores

        // Skip the 'Account Created' badge, as it's already processed
        if ($badgeName === 'Newbie Explorer: Once User Creates Account') {
            $earnedBadges[0]['badges_icon'] = $badge['badges_icon'];
            continue;
        }

        $earnedBadges[] = [
            'title' => $badgeName,
            'earned' => false,
            'progress' => null,
            'total' => null,
            'badges_icon' => null
        ];

        // Add the badge icon to the list of badges
        $earnedBadges[count($earnedBadges) - 1]['badges_icon'] = $badge['badges_icon'];

        // Check if the badge has a requirement and if so, compare with user data
        if ($badge['mining_sessions_required'] !== null) {
            $earnedBadges[count($earnedBadges) - 1]['progress'] = $userData['mining_session'];
            $earnedBadges[count($earnedBadges) - 1]['total'] = $badge['mining_sessions_required'];
            if ($userData['mining_session'] >= $badge['mining_sessions_required']) {
                $earnedBadges[count($earnedBadges) - 1]['earned'] = true;
            }
        } elseif ($badge['spin_wheel_required'] !== null) {
            $earnedBadges[count($earnedBadges) - 1]['progress'] = $userData['spin_wheel'];
            $earnedBadges[count($earnedBadges) - 1]['total'] = $badge['spin_wheel_required'];
            if ($userData['spin_wheel'] >= $badge['spin_wheel_required']) {
                $earnedBadges[count($earnedBadges) - 1]['earned'] = true;
            }
        } elseif ($badge['invite_friends_required'] !== null) {
            $earnedBadges[count($earnedBadges) - 1]['progress'] = $userData['total_invite'];
            $earnedBadges[count($earnedBadges) - 1]['total'] = $badge['invite_friends_required'];
            if ($userData['total_invite'] >= $badge['invite_friends_required']) {
                $earnedBadges[count($earnedBadges) - 1]['earned'] = true;
            }
        } elseif ($badge['crutox_in_wallet_required'] !== null) {
            $earnedBadges[count($earnedBadges) - 1]['progress'] = $userData['token'];
            $earnedBadges[count($earnedBadges) - 1]['total'] = $badge['crutox_in_wallet_required'];
            if ($userData['token'] >= $badge['crutox_in_wallet_required']) {
                $earnedBadges[count($earnedBadges) - 1]['earned'] = true;
            }
        } elseif ($badge['social_media_task_completed'] !== null) {
            $earnedBadges[count($earnedBadges) - 1]['progress'] = $completedSocialMediaTasks;
            $earnedBadges[count($earnedBadges) - 1]['total'] = $totalSocialMediaTasks;

            if ($completedSocialMediaTasks == $totalSocialMediaTasks) {
                $earnedBadges[count($earnedBadges) - 1]['earned'] = true;
            }
        }
    }

    // Return the result as JSON
    return json_encode($earnedBadges);
}

function getUserPerks($userId, $conn) {
    $currentLevel = $perkCrutoxPerTime = $perkMiningTime = null;

    // Prepare the SQL query
    $sql = "SELECT ul.current_level, l.perk_crutox_per_time, l.perk_mining_time 
            FROM user_levels ul
            INNER JOIN level l ON ul.current_level = l.id
            WHERE ul.user_id = ?";

    // Prepare statement
    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bind_param("i", $userId);

    // Execute statement
    $stmt->execute();

    // Bind result variables
    $stmt->bind_result($currentLevel, $perkCrutoxPerTime, $perkMiningTime);

    // Fetch values
    $stmt->fetch();

    // Close statement
    $stmt->close();

    // Return the perks
    return array(
        'current_level' => $currentLevel,
        'perk_crutox_per_time' => $perkCrutoxPerTime,
        'perk_mining_time' => $perkMiningTime
    );
}

?>