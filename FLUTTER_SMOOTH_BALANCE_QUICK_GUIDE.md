# Flutter Smooth Balance Animation - Quick Implementation Guide

## 🎯 What You Need to Do

Make the balance **smoothly increment** between API calls (every 5 seconds) using the `token_per_sec` value from the API.

---

## 📡 API Response (Updated)

The `/api/mining_status` endpoint now returns:
```json
{
  "balance": "0.3462268519",           // Current balance (updates every 30s)
  "token_per_sec": "0.0000115741",     // ⭐ Use this for smooth increment
  "balance_timestamp": "2026-01-22T03:55:21.000000Z",
  "is_mining_active": true              // Helper flag
}
```

---

## 💻 Quick Implementation

### Step 1: Add Timer for Smooth Increment

```dart
class MiningCubit extends Cubit<MiningState> {
  Timer? _balanceIncrementTimer;
  
  // Start smooth balance increment
  void _startSmoothIncrement() {
    _balanceIncrementTimer?.cancel();
    
    if (!state.isMiningActive || state.tokenPerSec <= 0) return;
    
    // Increment balance every 100ms for smooth animation
    _balanceIncrementTimer = Timer.periodic(
      Duration(milliseconds: 100),
      (timer) {
        if (!state.isMiningActive) {
          timer.cancel();
          return;
        }
        
        // Increment: tokenPerSec * 0.1 (100ms = 0.1 seconds)
        final increment = state.tokenPerSec * 0.1;
        final newBalance = state.displayedBalance + increment;
        
        emit(state.copyWith(displayedBalance: newBalance));
      },
    );
  }
  
  // Stop increment timer
  void _stopSmoothIncrement() {
    _balanceIncrementTimer?.cancel();
  }
}
```

### Step 2: Update State Model

```dart
class MiningState {
  double balance;           // From API (authoritative)
  double displayedBalance;  // ⭐ Use this in UI (smoothly increments)
  double tokenPerSec;       // From API (for increment calculation)
  bool isMiningActive;      // From API
  
  // When API response arrives:
  void updateFromApi(Map<String, dynamic> response) {
    final apiBalance = double.parse(response['balance']);
    final tokenPerSec = double.parse(response['token_per_sec']);
    final isActive = response['is_mining_active'] ?? false;
    
    // Sync displayedBalance if difference is large
    if ((apiBalance - displayedBalance).abs() > 0.01) {
      displayedBalance = apiBalance;  // Sync to API value
    }
    
    // Update other fields...
    this.balance = apiBalance;
    this.tokenPerSec = tokenPerSec;
    this.isMiningActive = isActive;
    
    // Restart smooth increment if mining is active
    if (isActive && tokenPerSec > 0) {
      _startSmoothIncrement();
    } else {
      _stopSmoothIncrement();
    }
  }
}
```

### Step 3: Use displayedBalance in UI

```dart
// ❌ DON'T use this (static, only updates every 30s):
Text('${state.balance}')

// ✅ USE this (smoothly increments):
Text('${state.displayedBalance.toStringAsFixed(10)}')
```

### Step 4: Format Balance Display

```dart
String formatBalance(double balance) {
  // Remove trailing zeros for cleaner display
  return balance.toStringAsFixed(10)
      .replaceAll(RegExp(r'0+$'), '')
      .replaceAll(RegExp(r'\.$'), '');
}

// Usage:
Text(
  formatBalance(state.displayedBalance),
  style: TextStyle(fontSize: 32, fontWeight: FontWeight.bold),
)
```

---

## 🔄 Complete Flow

```
1. API Poll (every 5s)
   → Get balance = 0.3462, tokenPerSec = 0.0000115741
   
2. Sync displayedBalance
   → displayedBalance = 0.3462 (if large diff)
   
3. Start Timer (every 100ms)
   → displayedBalance += 0.0000115741 * 0.1
   → displayedBalance += 0.00000115741
   
4. UI Updates Smoothly
   → 0.3462 → 0.346201 → 0.346202 → 0.346203...
   
5. Next API Poll (5s later)
   → Get balance = 0.3463
   → Sync if needed → Continue incrementing
```

---

## 📝 Minimal Code Example

```dart
// In your MiningCubit
Timer? _incrementTimer;

void handleApiResponse(Map<String, dynamic> response) {
  final apiBalance = double.parse(response['balance']);
  final tokenPerSec = double.parse(response['token_per_sec']);
  final isActive = response['is_mining_active'] ?? false;
  
  // Sync displayed balance
  if ((apiBalance - state.displayedBalance).abs() > 0.01) {
    emit(state.copyWith(displayedBalance: apiBalance));
  }
  
  // Update state
  emit(state.copyWith(
    balance: apiBalance,
    tokenPerSec: tokenPerSec,
    isMiningActive: isActive,
  ));
  
  // Start/stop smooth increment
  if (isActive && tokenPerSec > 0) {
    _incrementTimer?.cancel();
    _incrementTimer = Timer.periodic(
      Duration(milliseconds: 100),
      (_) {
        final increment = state.tokenPerSec * 0.1;
        emit(state.copyWith(
          displayedBalance: state.displayedBalance + increment,
        ));
      },
    );
  } else {
    _incrementTimer?.cancel();
  }
}
```

---

## ✅ Key Points

1. **Use `displayedBalance` in UI** - This increments smoothly
2. **Use `token_per_sec` from API** - This is the increment rate
3. **Update every 100ms** - Smooth animation without heavy CPU usage
4. **Sync when API arrives** - Keep displayedBalance close to API balance
5. **Stop timer when idle** - Only increment when `is_mining_active == true`

---

## 🎨 UI Widget Example

```dart
Widget buildBalanceDisplay(MiningState state) {
  return AnimatedSwitcher(
    duration: Duration(milliseconds: 200),
    child: Text(
      formatBalance(state.displayedBalance), // ⭐ Use displayedBalance
      key: ValueKey(state.displayedBalance),
      style: TextStyle(
        fontSize: 32,
        fontWeight: FontWeight.bold,
        color: Colors.green,
      ),
    ),
  );
}
```

---

That's it! Your balance will now smoothly increment between API calls, giving users a real-time experience even though the backend only updates every 30 seconds.
