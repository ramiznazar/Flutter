# Flutter Frontend Integration Guide - Backend-Managed Mining

## 🚀 App startup / config — GET /api/admin/settings_manage

The Crutox app calls **GET** `https://admin.crutox.com/api/admin/settings_manage` at startup and validates the response.

**Required response:** HTTP **200**, body:

```json
{ "success": true, "data": { ... } }
```

- `success` must be boolean `true` (false or missing → app shows "Failed to load settings").
- `data` must be an object; all settings come from `data`. App maps it with `AppSettings.fromJson(data)`.

**Maintenance / update (backend keeps these so the app never blocks):**

| Key | Type | Meaning |
|-----|------|--------|
| `maintenance` | int | 1 = maintenance mode, 0 = normal. Backend always sends **0**. |
| `maintenance_message` | string | Shown when maintenance is 1. |
| `force_update` | int | 1 = force update, 0 = optional. Backend always sends **0**. |
| `update_version` | string | Must match the app version (e.g. `"1.1.9"`). App compares to `packageInfo.version`; when equal, no update sheet. **Do not send empty string** — the app formats it to `".0.0"` and still shows the sheet. Set `MOBILE_APP_VERSION` in `.env` or `config/app.php` to match `pubspec.yaml` version. |
| `update_message` | string | Shown on "Update available" screen. |
| `update_link` | string | URL for "Update Now". |

**Other keys in `data` (snake_case):**  
`id`, `pirvacy_policy_link` (app typo — backend must use this key), `term_n_condition_link`, `support_email`, `faq_link`, `white_paper_link`, `road_map_link`, `about_us_link`, `mining_speed`, `base_mining_rate`, `max_mining_speed`, `referrer_reward`, `referee_reward`, `max_referrals`, `bonus_reward`, `current_users`, `goal_users`, `daily_tasks_reset_time`, `common_box_*`, `rare_box_*`, `epic_box_*`, `legendary_box_*`, `kyc_mining_sessions`, `kyc_referrals_required`, `ad_waterfall_order`, `ad_waterfall_enabled`. Missing keys default to `''` or `0` in the app.

**Backend behavior:** `SettingsManageController::index()` always returns `maintenance: 0` and `force_update: 0`, and `update_version` from `config('app.mobile_app_version', '1.1.9')` so it matches the current app and the "Update available" sheet does not show. Set `MOBILE_APP_VERSION=1.1.9` (or your pubspec version) in `.env` and bump it when you release a new build. Use `?format=array` to get `[{ ... }]` instead of `{ "success": true, "data": {...} }`.

---

## 📋 Overview

The mining system has been updated to be **backend-managed**. The backend now calculates and stores all mining balances, including booster multipliers. The frontend should **poll** the backend for balance updates instead of calculating locally.

### Key Changes:
- ✅ **Backend calculates balance** (with boosters applied)
- ✅ **Frontend polls** `/api/mining_status` every 5-10 seconds
- ✅ **No local calculation** needed
- ✅ **No sync logic** required (`reason: "get"` with balance)
- ✅ **Booster multipliers** handled by backend

---

## 🔄 Migration Steps

### 1. Remove Old Logic

**Remove these from your Flutter code:**
- ❌ Real-time balance increment calculations
- ❌ Booster multiplier application in frontend
- ❌ Sync logic (`reason: "get"` with `balance` field)
- ❌ Local balance storage that gets synced
- ❌ Timer-based balance updates

**Keep these:**
- ✅ UI animations (for smooth display)
- ✅ Booster status check (for UI display only)
- ✅ Mining timer display (from API response)

---

## 📡 New API Endpoints

### 1. Get Mining Status (NEW - Primary Endpoint)

**Endpoint:** `GET /api/mining_status`

**Purpose:** Poll this endpoint every 5-10 seconds to get current mining balance and status.

**Request:**
```dart
// Example using http package
final response = await http.get(
  Uri.parse('https://admin.crutox.com/api/mining_status?email=$email'),
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
);
```

**Request Parameters:**
```json
{
  "email": "user@example.com"
}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "message": "in_progress",  // or "idle" or "completed"
  "server_time": "2026-01-21-18:30:00",
  "mining_end_time": "2026-01-22-06:30:00",  // Empty string if idle
  "total_team": "5",
  "coin": "100",  // Spending currency (coins)
  "balance": "123.4567890123",  // ⭐ Current mining balance (tokens) - USE THIS
  "starting_balance": "100.0000000000",  // Balance when mining started
  "token_per_sec": "0.0000115741",  // ⭐ Token per second (WITH booster applied)
  "mining_speed": 90.00,  // Effective mining speed
  "usdt": 0.0004,
  "total_mining_time_in_sec": 43200,
  "seconds_remaining": 34975,  // Time left in mining session
  "elapsed_seconds": 8225,  // Time elapsed in mining session
  "has_active_booster": true,  // ⭐ NEW: Booster status
  "booster_type": "3x",  // ⭐ NEW: Active booster type
  "booster_multiplier": 3.0,  // ⭐ NEW: Booster multiplier (already applied to token_per_sec)
  "booster_expires_at": "2026-01-21 20:30:00",  // ⭐ NEW: Booster expiry
  "booster_seconds_remaining": 7200  // ⭐ NEW: Booster time remaining
}
```

**Response (Idle - Not Mining):**
```json
{
  "success": true,
  "message": "idle",
  "server_time": "2026-01-21-18:30:00",
  "mining_end_time": "",
  "total_team": "5",
  "coin": "100",
  "balance": "123.4567890123",  // Current balance
  "starting_balance": "123.4567890123",
  "token_per_sec": "0.0000115741",
  "mining_speed": 90.00,
  "usdt": 0.0004,
  "total_mining_time_in_sec": 0,
  "seconds_remaining": 0,
  "elapsed_seconds": 0,
  "has_active_booster": false,
  "booster_type": null,
  "booster_multiplier": 1.0,
  "booster_expires_at": null,
  "booster_seconds_remaining": 0
}
```

**Response (Error - 404):**
```json
{
  "success": false,
  "message": "User not found or account not active"
}
```

---

### 2. Start Mining (UPDATED)

**Endpoint:** `POST /api/start_mining`

**Purpose:** Start a new mining session. Backend will store `mining_start_balance` automatically.

**Request:**
```dart
final response = await http.post(
  Uri.parse('https://admin.crutox.com/api/start_mining'),
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: jsonEncode({
    'email': 'user@example.com',
    'password': 'user_password',
    'coins': 10,  // Coins to spend for mining
    // ❌ REMOVE: 'balance' field - not needed anymore
    // ❌ REMOVE: 'reason' field - not needed for starting
  }),
);
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "user_password",
  "coins": 10
}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "message": "in_progress",
  "server_time": "2026-01-21-18:30:00",
  "mining_end_time": "2026-01-22-06:30:00",
  "total_team": "5",
  "coin": "90",  // Updated coin balance (after deduction)
  "balance": "123.4567890123",  // Starting balance
  "starting_balance": "123.4567890123",  // Same as balance (when starting)
  "token_per_sec": "0.0000115741",  // Token per second (with booster if active)
  "mining_speed": 90.00,
  "usdt": 0.0004,
  "total_mining_time_in_sec": 43200,
  "seconds_remaining": 43200,
  "elapsed_seconds": 0
}
```

**Note:** After starting mining, immediately start polling `/api/mining_status` to get updated balances.

---

### 3. Booster Status (UNCHANGED - Keep for UI)

**Endpoint:** `POST /api/booster_status`

**Purpose:** Check booster status for UI display. Balance calculation is handled by backend.

**Request:**
```dart
final response = await http.post(
  Uri.parse('https://admin.crutox.com/api/booster_status'),
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: jsonEncode({
    'email': 'user@example.com',
  }),
);
```

**Response:**
```json
{
  "success": true,
  "has_active_booster": true,
  "booster_type": "3x",
  "started_at": "2026-01-21 18:00:00",
  "expires_at": "2026-01-21 20:00:00",
  "seconds_remaining": 7200
}
```

**Note:** This is optional - you can get booster info from `/api/mining_status` instead.

---

## 🔄 Implementation Flow

### 1. App Startup / Resume

**When:** App starts, resumes from background, or user navigates to mining screen.

**Action:**
```dart
// Poll mining status immediately
await fetchMiningStatus();

// Start periodic polling (every 5-10 seconds)
startMiningStatusPolling();
```

---

### 2. During Active Mining

**When:** User is actively mining (mining screen is visible).

**Action:**
```dart
// Poll every 5-10 seconds
Timer.periodic(Duration(seconds: 5), (timer) async {
  await fetchMiningStatus();
  
  // Update UI with new balance
  updateUI();
});
```

**Stop polling when:**
- User navigates away from mining screen
- Mining status returns `"idle"` or `"completed"`
- App goes to background (optional - can continue polling)

---

### 3. Starting Mining

**When:** User clicks "Start Mining" button.

**Action:**
```dart
// 1. Call start_mining endpoint
final startResponse = await startMining(coins: 10);

if (startResponse['success'] == true) {
  // 2. Immediately poll mining_status to get latest balance
  await fetchMiningStatus();
  
  // 3. Start periodic polling
  startMiningStatusPolling();
  
  // 4. Update UI
  updateMiningUI();
}
```

---

### 4. When Mining Completes

**When:** `mining_status` returns `message: "completed"` or `seconds_remaining: 0`.

**Action:**
```dart
if (miningStatus['message'] == 'completed' || 
    miningStatus['seconds_remaining'] == 0) {
  // Stop polling
  stopMiningStatusPolling();
  
  // Show completion message
  showCompletionMessage();
  
  // Update UI to idle state
  setMiningState('idle');
}
```

---

## 💾 Data Storage

### What to Store Locally:

```dart
class MiningState {
  double balance;  // From API response - current balance
  String status;  // "idle", "in_progress", "completed"
  int secondsRemaining;
  int elapsedSeconds;
  double tokenPerSec;  // For display only
  bool hasActiveBooster;
  String? boosterType;
  double boosterMultiplier;
  int? boosterSecondsRemaining;
  DateTime? lastUpdated;
}
```

### What NOT to Store:

- ❌ Local balance calculations
- ❌ Booster multiplier calculations
- ❌ Timer-based balance increments
- ❌ Sync state

---

## 🎨 UI Updates

### Display Balance

```dart
// Simply display the balance from API
Text(
  'Balance: ${formatBalance(miningState.balance)}',
  style: TextStyle(fontSize: 24),
)

// Format function
String formatBalance(double balance) {
  return balance.toStringAsFixed(10).replaceAll(RegExp(r'0+$'), '');
}
```

### Display Mining Progress

```dart
// Use seconds_remaining and elapsed_seconds from API
LinearProgressIndicator(
  value: miningState.elapsedSeconds / 
         (miningState.elapsedSeconds + miningState.secondsRemaining),
)

Text(
  'Time remaining: ${formatTime(miningState.secondsRemaining)}',
)
```

### Display Booster Status

```dart
if (miningState.hasActiveBooster) {
  Container(
    child: Column(
      children: [
        Text('Active Booster: ${miningState.boosterType}'),
        Text('Expires in: ${formatTime(miningState.boosterSecondsRemaining ?? 0)}'),
      ],
    ),
  )
}
```

### Smooth Animations (Optional)

```dart
// For smooth UI updates, you can animate between old and new balance
AnimatedSwitcher(
  duration: Duration(milliseconds: 300),
  child: Text(
    'Balance: ${formatBalance(miningState.balance)}',
    key: ValueKey(miningState.balance),
  ),
)
```

---

## 📝 Code Example - Complete Implementation

### 1. Mining Service Class

```dart
class MiningService {
  final String baseUrl = 'https://admin.crutox.com/api';
  Timer? _pollingTimer;
  
  // Poll mining status
  Future<Map<String, dynamic>> fetchMiningStatus(String email) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/mining_status?email=$email'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      );
      
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('Failed to fetch mining status');
      }
    } catch (e) {
      print('Error fetching mining status: $e');
      rethrow;
    }
  }
  
  // Start mining
  Future<Map<String, dynamic>> startMining({
    required String email,
    required String password,
    required int coins,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/start_mining'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': email,
          'password': password,
          'coins': coins,
        }),
      );
      
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('Failed to start mining');
      }
    } catch (e) {
      print('Error starting mining: $e');
      rethrow;
    }
  }
  
  // Start periodic polling
  void startPolling(String email, Function(Map<String, dynamic>) onUpdate) {
    _pollingTimer?.cancel();
    
    _pollingTimer = Timer.periodic(Duration(seconds: 5), (timer) async {
      try {
        final status = await fetchMiningStatus(email);
        onUpdate(status);
      } catch (e) {
        print('Error in polling: $e');
      }
    });
  }
  
  // Stop polling
  void stopPolling() {
    _pollingTimer?.cancel();
    _pollingTimer = null;
  }
}
```

### 2. Mining State Management (Using Provider/Bloc/Cubit)

```dart
class MiningCubit extends Cubit<MiningState> {
  final MiningService _miningService = MiningService();
  
  MiningCubit() : super(MiningState.initial());
  
  // Initialize mining status
  Future<void> initializeMining(String email) async {
    try {
      final status = await _miningService.fetchMiningStatus(email);
      _updateStateFromResponse(status);
      
      // Start polling if mining is active
      if (status['message'] == 'in_progress') {
        _miningService.startPolling(email, (status) {
          _updateStateFromResponse(status);
        });
      }
    } catch (e) {
      emit(state.copyWith(error: e.toString()));
    }
  }
  
  // Start mining
  Future<void> startMining({
    required String email,
    required String password,
    required int coins,
  }) async {
    try {
      final response = await _miningService.startMining(
        email: email,
        password: password,
        coins: coins,
      );
      
      if (response['success'] == true) {
        // Immediately fetch latest status
        await initializeMining(email);
      } else {
        emit(state.copyWith(error: response['message'] ?? 'Failed to start mining'));
      }
    } catch (e) {
      emit(state.copyWith(error: e.toString()));
    }
  }
  
  // Update state from API response
  void _updateStateFromResponse(Map<String, dynamic> response) {
    if (response['success'] == true) {
      emit(state.copyWith(
        balance: double.tryParse(response['balance'] ?? '0') ?? 0.0,
        status: response['message'] ?? 'idle',
        secondsRemaining: response['seconds_remaining'] ?? 0,
        elapsedSeconds: response['elapsed_seconds'] ?? 0,
        tokenPerSec: double.tryParse(response['token_per_sec'] ?? '0') ?? 0.0,
        hasActiveBooster: response['has_active_booster'] ?? false,
        boosterType: response['booster_type'],
        boosterMultiplier: (response['booster_multiplier'] ?? 1.0).toDouble(),
        boosterSecondsRemaining: response['booster_seconds_remaining'],
        lastUpdated: DateTime.now(),
        error: null,
      ));
      
      // Stop polling if mining completed
      if (response['message'] == 'completed' || 
          response['seconds_remaining'] == 0) {
        _miningService.stopPolling();
      }
    }
  }
  
  @override
  Future<void> close() {
    _miningService.stopPolling();
    return super.close();
  }
}
```

### 3. Mining State Model

```dart
class MiningState {
  final double balance;
  final String status;  // "idle", "in_progress", "completed"
  final int secondsRemaining;
  final int elapsedSeconds;
  final double tokenPerSec;
  final bool hasActiveBooster;
  final String? boosterType;
  final double boosterMultiplier;
  final int? boosterSecondsRemaining;
  final DateTime? lastUpdated;
  final String? error;
  
  MiningState({
    required this.balance,
    required this.status,
    required this.secondsRemaining,
    required this.elapsedSeconds,
    required this.tokenPerSec,
    required this.hasActiveBooster,
    this.boosterType,
    required this.boosterMultiplier,
    this.boosterSecondsRemaining,
    this.lastUpdated,
    this.error,
  });
  
  factory MiningState.initial() {
    return MiningState(
      balance: 0.0,
      status: 'idle',
      secondsRemaining: 0,
      elapsedSeconds: 0,
      tokenPerSec: 0.0,
      hasActiveBooster: false,
      boosterMultiplier: 1.0,
    );
  }
  
  MiningState copyWith({
    double? balance,
    String? status,
    int? secondsRemaining,
    int? elapsedSeconds,
    double? tokenPerSec,
    bool? hasActiveBooster,
    String? boosterType,
    double? boosterMultiplier,
    int? boosterSecondsRemaining,
    DateTime? lastUpdated,
    String? error,
  }) {
    return MiningState(
      balance: balance ?? this.balance,
      status: status ?? this.status,
      secondsRemaining: secondsRemaining ?? this.secondsRemaining,
      elapsedSeconds: elapsedSeconds ?? this.elapsedSeconds,
      tokenPerSec: tokenPerSec ?? this.tokenPerSec,
      hasActiveBooster: hasActiveBooster ?? this.hasActiveBooster,
      boosterType: boosterType ?? this.boosterType,
      boosterMultiplier: boosterMultiplier ?? this.boosterMultiplier,
      boosterSecondsRemaining: boosterSecondsRemaining ?? this.boosterSecondsRemaining,
      lastUpdated: lastUpdated ?? this.lastUpdated,
      error: error ?? this.error,
    );
  }
}
```

---

## ⚠️ Important Notes

### 1. Balance Display
- **Always use `balance` from API response** - don't calculate locally
- Backend calculates balance with boosters already applied
- `token_per_sec` already includes booster multiplier

### 2. Polling Frequency
- **Recommended: 5-10 seconds** during active mining
- **Stop polling** when mining is idle or completed
- **Resume polling** when user returns to mining screen

### 3. Error Handling
- Handle network errors gracefully
- Show cached balance if available
- Retry failed requests with exponential backoff

### 4. Booster Display
- Booster info is included in `mining_status` response
- No need to call `/api/booster_status` separately (optional)
- Booster multiplier is **already applied** to `token_per_sec`

### 5. Admin-Given Coins
- Admin-given coins are **automatically reflected** in balance
- No sync needed - just poll `mining_status` to see updated balance

### 6. Daily Rewards / Mystery Box
- These still add tokens directly via their respective endpoints
- Balance will be updated in next `mining_status` poll
- Or poll immediately after claiming rewards

---

## 🔍 Testing Checklist

- [ ] Mining status polling works correctly
- [ ] Balance updates every 5-10 seconds during mining
- [ ] Booster multiplier is reflected in balance
- [ ] Mining completion is detected correctly
- [ ] Polling stops when mining is idle
- [ ] Polling resumes when returning to mining screen
- [ ] Admin-given coins appear in balance
- [ ] Daily rewards appear in balance
- [ ] Mystery box rewards appear in balance
- [ ] Network errors are handled gracefully
- [ ] UI updates smoothly with new balance

---

## 📞 Support

If you encounter any issues:
1. Check API responses match the expected format
2. Verify polling is working (check network tab)
3. Ensure backend scheduled job is running (`mining:update-balances`)
4. Check server logs for errors

---

## 🎯 Summary

**Old Approach (Remove):**
- ❌ Calculate balance locally
- ❌ Apply booster multipliers in frontend
- ❌ Sync balance with `reason: "get"`

**New Approach (Implement):**
- ✅ Poll `/api/mining_status` every 5-10 seconds
- ✅ Display balance from API response
- ✅ Backend handles all calculations
- ✅ Booster multipliers applied by backend

**Key Endpoint:**
- `GET /api/mining_status` - Poll this for balance updates

**Key Fields:**
- `balance` - Current mining balance (use this)
- `token_per_sec` - Already includes booster multiplier
- `has_active_booster` - Booster status
- `booster_multiplier` - Booster multiplier value

---

**Happy Coding! 🚀**
