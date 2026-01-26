# Flutter Smooth Balance Animation Guide

## 🎯 Goal
Make the mining balance appear to increase smoothly in real-time, even though the backend only updates every 30 seconds. The frontend will increment the balance locally between API calls for a better user experience.

---

## 📡 API Response Fields

The `/api/mining_status` endpoint now returns:
- `balance` - Current balance from backend (updated every 30 seconds)
- `token_per_sec` - Tokens earned per second (use this for smooth increment)
- `balance_timestamp` - ISO timestamp when balance was calculated
- `is_mining_active` - Boolean flag if mining is active

**Example Response:**
```json
{
  "success": true,
  "message": "in_progress",
  "balance": "0.3462268519",
  "token_per_sec": "0.0000115741",
  "balance_timestamp": "2026-01-22T03:55:21.000000Z",
  "is_mining_active": true,
  "seconds_remaining": 13265,
  "elapsed_seconds": 29935
}
```

---

## 🎨 Implementation: Smooth Balance Animation

### Concept
1. **Store last API balance** and timestamp when received
2. **Use Timer** to increment balance every second (or fraction) based on `token_per_sec`
3. **When new API response arrives**, smoothly sync to actual balance
4. **Display animated balance** that increases smoothly

---

## 💻 Flutter Code Implementation

### 1. Mining State Model (Updated)

```dart
class MiningState {
  // From API
  double balance;  // Current balance from backend
  double tokenPerSec;  // Tokens per second (for animation)
  DateTime? balanceTimestamp;  // When balance was calculated
  bool isMiningActive;
  
  // For smooth animation
  double displayedBalance;  // Balance shown in UI (increments smoothly)
  DateTime? lastIncrementTime;  // Last time we incremented balance
  Timer? balanceIncrementTimer;  // Timer for smooth increments
  
  // Other fields...
  String status;
  int secondsRemaining;
  int elapsedSeconds;
  bool hasActiveBooster;
  String? boosterType;
  double boosterMultiplier;
  
  MiningState({
    required this.balance,
    required this.tokenPerSec,
    this.balanceTimestamp,
    required this.isMiningActive,
    required this.displayedBalance,
    this.lastIncrementTime,
    this.balanceIncrementTimer,
    // ... other fields
  });
  
  factory MiningState.initial() {
    return MiningState(
      balance: 0.0,
      tokenPerSec: 0.0,
      isMiningActive: false,
      displayedBalance: 0.0,
      status: 'idle',
      secondsRemaining: 0,
      elapsedSeconds: 0,
      hasActiveBooster: false,
      boosterMultiplier: 1.0,
    );
  }
  
  MiningState copyWith({
    double? balance,
    double? tokenPerSec,
    DateTime? balanceTimestamp,
    bool? isMiningActive,
    double? displayedBalance,
    DateTime? lastIncrementTime,
    Timer? balanceIncrementTimer,
    // ... other fields
  }) {
    return MiningState(
      balance: balance ?? this.balance,
      tokenPerSec: tokenPerSec ?? this.tokenPerSec,
      balanceTimestamp: balanceTimestamp ?? this.balanceTimestamp,
      isMiningActive: isMiningActive ?? this.isMiningActive,
      displayedBalance: displayedBalance ?? this.displayedBalance,
      lastIncrementTime: lastIncrementTime ?? this.lastIncrementTime,
      balanceIncrementTimer: balanceIncrementTimer ?? this.balanceIncrementTimer,
      // ... other fields
    );
  }
}
```

---

### 2. Mining Cubit/Bloc (Updated)

```dart
import 'dart:async';
import 'package:flutter_bloc/flutter_bloc.dart';

class MiningCubit extends Cubit<MiningState> {
  final MiningService _miningService = MiningService();
  Timer? _pollingTimer;
  
  MiningCubit() : super(MiningState.initial());
  
  // Start smooth balance increment timer
  void _startBalanceIncrementTimer() {
    // Stop existing timer
    _stopBalanceIncrementTimer();
    
    // Only start if mining is active
    if (!state.isMiningActive || state.tokenPerSec <= 0) {
      return;
    }
    
    // Increment balance every 100ms for smooth animation
    state.balanceIncrementTimer = Timer.periodic(
      Duration(milliseconds: 100), // Update every 100ms for smooth animation
      (timer) {
        if (!state.isMiningActive || state.tokenPerSec <= 0) {
          timer.cancel();
          return;
        }
        
        // Calculate increment: tokenPerSec * 0.1 (since we update every 100ms)
        final increment = state.tokenPerSec * 0.1;
        final newDisplayedBalance = state.displayedBalance + increment;
        
        // Update displayed balance
        emit(state.copyWith(
          displayedBalance: newDisplayedBalance,
          lastIncrementTime: DateTime.now(),
        ));
      },
    );
  }
  
  // Stop balance increment timer
  void _stopBalanceIncrementTimer() {
    state.balanceIncrementTimer?.cancel();
    emit(state.copyWith(balanceIncrementTimer: null));
  }
  
  // Fetch mining status from API
  Future<void> fetchMiningStatus(String email) async {
    try {
      final response = await _miningService.fetchMiningStatus(email);
      
      if (response['success'] == true) {
        final apiBalance = double.tryParse(response['balance'] ?? '0') ?? 0.0;
        final tokenPerSec = double.tryParse(response['token_per_sec'] ?? '0') ?? 0.0;
        final balanceTimestamp = response['balance_timestamp'] != null
            ? DateTime.parse(response['balance_timestamp'])
            : DateTime.now();
        final isMiningActive = response['is_mining_active'] ?? false;
        
        // Sync displayed balance with API balance
        // If displayed balance is significantly different, sync immediately
        final balanceDiff = (apiBalance - state.displayedBalance).abs();
        final displayedBalance = balanceDiff > 0.01 
            ? apiBalance  // Large difference - sync immediately
            : state.displayedBalance;  // Small difference - keep smooth increment
        
        // Update state
        emit(state.copyWith(
          balance: apiBalance,
          tokenPerSec: tokenPerSec,
          balanceTimestamp: balanceTimestamp,
          isMiningActive: isMiningActive,
          displayedBalance: displayedBalance,
          status: response['message'] ?? 'idle',
          secondsRemaining: response['seconds_remaining'] ?? 0,
          elapsedSeconds: response['elapsed_seconds'] ?? 0,
          hasActiveBooster: response['has_active_booster'] ?? false,
          boosterType: response['booster_type'],
          boosterMultiplier: (response['booster_multiplier'] ?? 1.0).toDouble(),
          lastUpdated: DateTime.now(),
        ));
        
        // Restart balance increment timer if mining is active
        if (isMiningActive && tokenPerSec > 0) {
          _startBalanceIncrementTimer();
        } else {
          _stopBalanceIncrementTimer();
        }
      }
    } catch (e) {
      print('Error fetching mining status: $e');
    }
  }
  
  // Start polling
  void startPolling(String email) {
    _pollingTimer?.cancel();
    
    // Poll every 5 seconds
    _pollingTimer = Timer.periodic(Duration(seconds: 5), (timer) async {
      await fetchMiningStatus(email);
    });
    
    // Fetch immediately
    fetchMiningStatus(email);
  }
  
  // Stop polling
  void stopPolling() {
    _pollingTimer?.cancel();
    _pollingTimer = null;
    _stopBalanceIncrementTimer();
  }
  
  @override
  Future<void> close() {
    stopPolling();
    return super.close();
  }
}
```

---

### 3. UI Widget - Animated Balance Display

```dart
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class AnimatedBalanceWidget extends StatelessWidget {
  final double balance;
  final double tokenPerSec;
  final bool isMiningActive;
  
  const AnimatedBalanceWidget({
    Key? key,
    required this.balance,
    required this.tokenPerSec,
    required this.isMiningActive,
  }) : super(key: key);
  
  @override
  Widget build(BuildContext context) {
    // Format balance with appropriate decimal places
    final formattedBalance = _formatBalance(balance);
    
    return Column(
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        // Main balance display with smooth animation
        AnimatedSwitcher(
          duration: Duration(milliseconds: 300),
          transitionBuilder: (Widget child, Animation<double> animation) {
            return FadeTransition(
              opacity: animation,
              child: child,
            );
          },
          child: Text(
            formattedBalance,
            key: ValueKey(balance), // Key changes when balance changes
            style: TextStyle(
              fontSize: 32,
              fontWeight: FontWeight.bold,
              color: Colors.green,
            ),
          ),
        ),
        
        // Mining speed indicator
        if (isMiningActive && tokenPerSec > 0)
          Padding(
            padding: EdgeInsets.only(top: 8),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.trending_up, size: 16, color: Colors.green),
                SizedBox(width: 4),
                Text(
                  '+${_formatBalance(tokenPerSec)}/sec',
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.green.shade700,
                  ),
                ),
              ],
            ),
          ),
      ],
    );
  }
  
  String _formatBalance(double balance) {
    // Format with up to 10 decimal places, remove trailing zeros
    final formatter = NumberFormat('#0.##########', 'en_US');
    return formatter.format(balance);
  }
}
```

---

### 4. Usage in Mining Screen

```dart
class MiningScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return BlocBuilder<MiningCubit, MiningState>(
      builder: (context, state) {
        return Scaffold(
          body: Column(
            children: [
              // Animated balance display
              AnimatedBalanceWidget(
                balance: state.displayedBalance, // Use displayedBalance, not balance
                tokenPerSec: state.tokenPerSec,
                isMiningActive: state.isMiningActive,
              ),
              
              // Mining progress
              if (state.isMiningActive)
                LinearProgressIndicator(
                  value: state.elapsedSeconds / 
                         (state.elapsedSeconds + state.secondsRemaining),
                ),
              
              // Time remaining
              if (state.isMiningActive)
                Text(
                  'Time remaining: ${_formatTime(state.secondsRemaining)}',
                ),
            ],
          ),
        );
      },
    );
  }
  
  String _formatTime(int seconds) {
    final hours = seconds ~/ 3600;
    final minutes = (seconds % 3600) ~/ 60;
    final secs = seconds % 60;
    return '${hours.toString().padLeft(2, '0')}:${minutes.toString().padLeft(2, '0')}:${secs.toString().padLeft(2, '0')}';
  }
}
```

---

### 5. Initialize Mining (App Startup)

```dart
// In your app initialization or mining screen initState
@override
void initState() {
  super.initState();
  
  // Start polling and smooth balance animation
  context.read<MiningCubit>().startPolling(userEmail);
}
```

---

## 🔄 How It Works

### Flow Diagram

```
1. API Poll (every 5 seconds)
   ↓
2. Receive balance from backend (e.g., 0.3462...)
   ↓
3. Sync displayedBalance to API balance (if large difference)
   ↓
4. Start Timer (every 100ms)
   ↓
5. Increment displayedBalance: balance += tokenPerSec * 0.1
   ↓
6. UI updates smoothly every 100ms
   ↓
7. Next API poll arrives → Sync if needed → Continue
```

### Example Timeline

```
Time 0s:  API returns balance = 0.3462, displayedBalance = 0.3462
Time 0.1s: displayedBalance = 0.3462 + (0.0000115741 * 0.1) = 0.34620115741
Time 0.2s: displayedBalance = 0.34620115741 + 0.00000115741 = 0.34620231482
Time 0.3s: displayedBalance = 0.34620231482 + 0.00000115741 = 0.34620347223
...
Time 5s:   API poll → balance = 0.3463 (backend updated)
          Sync displayedBalance to 0.3463 (small diff, keep incrementing)
Time 5.1s: displayedBalance = 0.3463 + 0.00000115741 = 0.34630115741
...
```

---

## ⚙️ Configuration Options

### Update Frequency

```dart
// For smoother animation (more CPU usage)
Timer.periodic(Duration(milliseconds: 50), ...)  // Update every 50ms

// For balanced performance (recommended)
Timer.periodic(Duration(milliseconds: 100), ...)  // Update every 100ms

// For less CPU usage
Timer.periodic(Duration(milliseconds: 200), ...)  // Update every 200ms
```

### Sync Threshold

```dart
// Sync immediately if difference > 0.01
final balanceDiff = (apiBalance - displayedBalance).abs();
if (balanceDiff > 0.01) {
  displayedBalance = apiBalance;  // Large difference - sync
}

// Or sync if difference > 1% of balance
if (balanceDiff > (apiBalance * 0.01)) {
  displayedBalance = apiBalance;  // 1% difference - sync
}
```

---

## 🎯 Key Points

1. **Use `displayedBalance` in UI** - This is the smoothly incrementing value
2. **Use `balance` from API** - This is the authoritative backend value
3. **Sync when needed** - If displayed balance drifts too far from API balance, sync
4. **Stop timer when idle** - Only increment when `isMiningActive == true`
5. **Handle app pause/resume** - Restart timer when app resumes

---

## 🐛 Troubleshooting

### Balance not incrementing smoothly
- Check if `isMiningActive` is true
- Verify `tokenPerSec > 0`
- Ensure timer is started after API response

### Balance drifts from API value
- Adjust sync threshold (currently 0.01)
- Increase API polling frequency
- Check if timer is running correctly

### Performance issues
- Increase timer interval (100ms → 200ms)
- Only update UI when balance changes significantly
- Use `AnimatedSwitcher` for smooth transitions

---

## 📝 Complete Example

```dart
// In your MiningCubit
void _updateBalanceSmoothly() {
  if (!state.isMiningActive || state.tokenPerSec <= 0) return;
  
  // Calculate time since last increment
  final now = DateTime.now();
  final lastTime = state.lastIncrementTime ?? now;
  final elapsed = now.difference(lastTime).inMilliseconds / 1000.0; // Convert to seconds
  
  // Calculate increment
  final increment = state.tokenPerSec * elapsed;
  final newBalance = state.displayedBalance + increment;
  
  emit(state.copyWith(
    displayedBalance: newBalance,
    lastIncrementTime: now,
  ));
}

// Start smooth increment
void _startSmoothIncrement() {
  _stopSmoothIncrement();
  
  if (!state.isMiningActive) return;
  
  state.balanceIncrementTimer = Timer.periodic(
    Duration(milliseconds: 100),
    (_) => _updateBalanceSmoothly(),
  );
}
```

---

## ✅ Summary

1. **Backend provides**: `balance`, `token_per_sec`, `balance_timestamp`
2. **Frontend stores**: `displayedBalance` (for smooth animation)
3. **Timer increments**: `displayedBalance += tokenPerSec * interval` every 100ms
4. **API syncs**: When new API response arrives, sync if difference is large
5. **UI displays**: `displayedBalance` (smoothly increasing) instead of `balance` (static)

This gives users a smooth, real-time feeling balance increase even though the backend only updates every 30 seconds!
