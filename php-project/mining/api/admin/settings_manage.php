<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require '../../config/dbh.inc.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// GET - Get settings
if ($method === 'GET') {
    $type = isset($_GET['type']) ? $_GET['type'] : 'all';
    
    $query = "SELECT * FROM settings LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        // If settings table doesn't exist or error, return defaults
        echo json_encode(['success' => true, 'data' => [
            'mining_speed' => 10,
            'base_mining_rate' => 5,
            'max_mining_speed' => 50,
            'referrer_reward' => 50,
            'referee_reward' => 25,
            'max_referrals' => 100,
            'bonus_reward' => 500,
            'current_users' => 99000,
            'goal_users' => 1000000
        ]]);
        exit;
    }
    
    if (mysqli_num_rows($result) > 0) {
        $settings = mysqli_fetch_assoc($result);
        echo json_encode(['success' => true, 'data' => $settings]);
    } else {
        // Return default settings
        echo json_encode(['success' => true, 'data' => [
            'mining_speed' => 10,
            'base_mining_rate' => 5,
            'max_mining_speed' => 50,
            'referrer_reward' => 50,
            'referee_reward' => 25,
            'max_referrals' => 100,
            'bonus_reward' => 500,
            'current_users' => 99000,
            'goal_users' => 1000000
        ]]);
    }
    exit;
}

// Helper function to check if column exists
function columnExists($conn, $table, $column) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM $table LIKE '$column'");
    return mysqli_num_rows($result) > 0;
}

// Helper function to add column if it doesn't exist
function addColumnIfNotExists($conn, $table, $column, $definition) {
    if (!columnExists($conn, $table, $column)) {
        mysqli_query($conn, "ALTER TABLE $table ADD COLUMN $column $definition");
    }
}

// POST/PUT - Update settings
if ($method === 'POST' || $method === 'PUT') {
    $settingsType = isset($input['settings_type']) ? $input['settings_type'] : 'all';
    
    // Check if settings row exists
    $checkQuery = "SELECT COUNT(*) as count FROM settings";
    $checkResult = mysqli_query($conn, $checkQuery);
    $checkRow = mysqli_fetch_assoc($checkResult);
    $settingsExist = $checkRow['count'] > 0;
    
    if ($settingsType === 'mining') {
        // Ensure columns exist
        addColumnIfNotExists($conn, 'settings', 'mining_speed', 'DECIMAL(10,2) DEFAULT 10.00');
        addColumnIfNotExists($conn, 'settings', 'base_mining_rate', 'DECIMAL(10,2) DEFAULT 5.00');
        addColumnIfNotExists($conn, 'settings', 'max_mining_speed', 'DECIMAL(10,2) DEFAULT 50.00');
        
        $miningSpeed = isset($input['mining_speed']) ? floatval($input['mining_speed']) : null;
        $baseRate = isset($input['base_rate']) ? floatval($input['base_rate']) : null;
        $maxSpeed = isset($input['max_speed']) ? floatval($input['max_speed']) : null;
        
        if ($settingsExist) {
            $updateFields = [];
            if ($miningSpeed !== null) $updateFields[] = "mining_speed = $miningSpeed";
            if ($baseRate !== null) $updateFields[] = "base_mining_rate = $baseRate";
            if ($maxSpeed !== null) $updateFields[] = "max_mining_speed = $maxSpeed";
            
            if (!empty($updateFields)) {
                $query = "UPDATE settings SET " . implode(', ', $updateFields);
                mysqli_query($conn, $query);
            }
        } else {
            $query = "INSERT INTO settings (mining_speed, base_mining_rate, max_mining_speed) 
                      VALUES ($miningSpeed, $baseRate, $maxSpeed)";
            mysqli_query($conn, $query);
        }
        
        echo json_encode(['success' => true, 'message' => 'Mining settings updated successfully.']);
        
    } else if ($settingsType === 'referral') {
        // Ensure columns exist
        addColumnIfNotExists($conn, 'settings', 'referrer_reward', 'INT DEFAULT 50');
        addColumnIfNotExists($conn, 'settings', 'referee_reward', 'INT DEFAULT 25');
        addColumnIfNotExists($conn, 'settings', 'max_referrals', 'INT DEFAULT 100');
        addColumnIfNotExists($conn, 'settings', 'bonus_reward', 'INT DEFAULT 500');
        
        $referrerReward = isset($input['referrer_reward']) ? intval($input['referrer_reward']) : null;
        $refereeReward = isset($input['referee_reward']) ? intval($input['referee_reward']) : null;
        $maxReferrals = isset($input['max_referrals']) ? intval($input['max_referrals']) : null;
        $bonusReward = isset($input['bonus_reward']) ? intval($input['bonus_reward']) : null;
        
        if ($settingsExist) {
            $updateFields = [];
            if ($referrerReward !== null) $updateFields[] = "referrer_reward = $referrerReward";
            if ($refereeReward !== null) $updateFields[] = "referee_reward = $refereeReward";
            if ($maxReferrals !== null) $updateFields[] = "max_referrals = $maxReferrals";
            if ($bonusReward !== null) $updateFields[] = "bonus_reward = $bonusReward";
            
            if (!empty($updateFields)) {
                $query = "UPDATE settings SET " . implode(', ', $updateFields);
                mysqli_query($conn, $query);
            }
        } else {
            $query = "INSERT INTO settings (referrer_reward, referee_reward, max_referrals, bonus_reward) 
                      VALUES ($referrerReward, $refereeReward, $maxReferrals, $bonusReward)";
            mysqli_query($conn, $query);
        }
        
        echo json_encode(['success' => true, 'message' => 'Referral settings updated successfully.']);
        
    } else if ($settingsType === 'user_count') {
        // Ensure columns exist
        addColumnIfNotExists($conn, 'settings', 'current_users', 'INT DEFAULT 99000');
        addColumnIfNotExists($conn, 'settings', 'goal_users', 'INT DEFAULT 1000000');
        
        $currentUsers = isset($input['current_users']) ? intval($input['current_users']) : null;
        $goalUsers = isset($input['goal_users']) ? intval($input['goal_users']) : null;
        
        if ($settingsExist) {
            $updateFields = [];
            if ($currentUsers !== null) $updateFields[] = "current_users = $currentUsers";
            if ($goalUsers !== null) $updateFields[] = "goal_users = $goalUsers";
            
            if (!empty($updateFields)) {
                $query = "UPDATE settings SET " . implode(', ', $updateFields);
                mysqli_query($conn, $query);
            }
        } else {
            $query = "INSERT INTO settings (current_users, goal_users) 
                      VALUES ($currentUsers, $goalUsers)";
            mysqli_query($conn, $query);
        }
        
        echo json_encode(['success' => true, 'message' => 'User count updated successfully.']);
        
    } else if ($settingsType === 'mystery_box') {
        $boxType = isset($input['box_type']) ? $input['box_type'] : '';
        $cooldown = isset($input['cooldown']) ? intval($input['cooldown']) : null;
        $adsRequired = isset($input['ads_required']) ? intval($input['ads_required']) : null;
        $minCoins = isset($input['min_coins']) ? floatval($input['min_coins']) : null;
        $maxCoins = isset($input['max_coins']) ? floatval($input['max_coins']) : null;
        
        if (empty($boxType)) {
            echo json_encode(['success' => false, 'message' => 'Box type is required.']);
            exit;
        }
        
        // Ensure columns exist
        $fieldPrefix = $boxType . '_box_';
        $defaultCooldown = ($boxType === 'common') ? 0 : (($boxType === 'rare') ? 5 : (($boxType === 'epic') ? 10 : 30));
        $defaultAds = ($boxType === 'common') ? 1 : (($boxType === 'rare') ? 3 : (($boxType === 'epic') ? 6 : 10));
        
        addColumnIfNotExists($conn, 'settings', $fieldPrefix . 'cooldown', "INT DEFAULT $defaultCooldown");
        addColumnIfNotExists($conn, 'settings', $fieldPrefix . 'ads', "INT DEFAULT $defaultAds");
        addColumnIfNotExists($conn, 'settings', $fieldPrefix . 'min_coins', 'DECIMAL(10,2) DEFAULT 1.00');
        addColumnIfNotExists($conn, 'settings', $fieldPrefix . 'max_coins', 'DECIMAL(10,2) DEFAULT 5.00');
        
        if ($settingsExist) {
            $updateFields = [];
            if ($cooldown !== null) $updateFields[] = $fieldPrefix . "cooldown = $cooldown";
            if ($adsRequired !== null) $updateFields[] = $fieldPrefix . "ads = $adsRequired";
            if ($minCoins !== null) $updateFields[] = $fieldPrefix . "min_coins = $minCoins";
            if ($maxCoins !== null) $updateFields[] = $fieldPrefix . "max_coins = $maxCoins";
            
            if (!empty($updateFields)) {
                $query = "UPDATE settings SET " . implode(', ', $updateFields);
                mysqli_query($conn, $query);
            }
        } else {
            // Create new settings row with mystery box data
            $query = "INSERT INTO settings ({$fieldPrefix}cooldown, {$fieldPrefix}ads, {$fieldPrefix}min_coins, {$fieldPrefix}max_coins) 
                      VALUES ($cooldown, $adsRequired, $minCoins, $maxCoins)";
            mysqli_query($conn, $query);
        }
        
        echo json_encode(['success' => true, 'message' => ucfirst($boxType) . ' mystery box settings updated successfully.']);
        
    } else if ($settingsType === 'kyc') {
        // Ensure columns exist
        addColumnIfNotExists($conn, 'settings', 'kyc_mining_sessions', 'INT DEFAULT 14');
        addColumnIfNotExists($conn, 'settings', 'kyc_referrals_required', 'INT DEFAULT 10');
        
        $miningSessions = isset($input['kyc_mining_sessions']) ? intval($input['kyc_mining_sessions']) : null;
        $referralsRequired = isset($input['kyc_referrals_required']) ? intval($input['kyc_referrals_required']) : null;
        
        if ($settingsExist) {
            $updateFields = [];
            if ($miningSessions !== null) $updateFields[] = "kyc_mining_sessions = $miningSessions";
            if ($referralsRequired !== null) $updateFields[] = "kyc_referrals_required = $referralsRequired";
            
            if (!empty($updateFields)) {
                $query = "UPDATE settings SET " . implode(', ', $updateFields);
                mysqli_query($conn, $query);
            }
        } else {
            $query = "INSERT INTO settings (kyc_mining_sessions, kyc_referrals_required) 
                      VALUES ($miningSessions, $referralsRequired)";
            mysqli_query($conn, $query);
        }
        
        echo json_encode(['success' => true, 'message' => 'KYC settings updated successfully.']);
        
    } else if ($settingsType === 'ad_waterfall') {
        // Ensure columns exist
        addColumnIfNotExists($conn, 'settings', 'ad_waterfall_order', 'TEXT NULL');
        addColumnIfNotExists($conn, 'settings', 'ad_waterfall_enabled', 'TINYINT(1) DEFAULT 1');
        
        $waterfallOrder = isset($input['ad_waterfall_order']) ? json_encode($input['ad_waterfall_order']) : null;
        $waterfallEnabled = isset($input['ad_waterfall_enabled']) ? intval($input['ad_waterfall_enabled']) : null;
        
        if ($settingsExist) {
            $updateFields = [];
            if ($waterfallOrder !== null) {
                $waterfallOrderEscaped = mysqli_real_escape_string($conn, $waterfallOrder);
                $updateFields[] = "ad_waterfall_order = '$waterfallOrderEscaped'";
            }
            if ($waterfallEnabled !== null) $updateFields[] = "ad_waterfall_enabled = $waterfallEnabled";
            
            if (!empty($updateFields)) {
                $query = "UPDATE settings SET " . implode(', ', $updateFields);
                mysqli_query($conn, $query);
            }
        } else {
            $waterfallOrderEscaped = mysqli_real_escape_string($conn, $waterfallOrder ?: '["admob","meta","unity","applovin"]');
            $query = "INSERT INTO settings (ad_waterfall_order, ad_waterfall_enabled) 
                      VALUES ('$waterfallOrderEscaped', " . ($waterfallEnabled !== null ? $waterfallEnabled : 1) . ")";
            mysqli_query($conn, $query);
        }
        
        echo json_encode(['success' => true, 'message' => 'Ad waterfall settings updated successfully.']);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid settings type.']);
    }
    exit;
}

mysqli_close($conn);
?>


