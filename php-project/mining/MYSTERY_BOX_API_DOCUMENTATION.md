# Mystery Box API Documentation

This document provides all API endpoints for Mystery Box functionality, including click tracking and admin management.

---

## Frontend API Endpoints

### 1. Track Mystery Box Click
**Endpoint:** `POST /api/mystery_box_click.php`

Tracks when a user clicks on a mystery box (before watching ads). This helps track user engagement.

**Request Body:**
```json
{
  "email": "user@example.com",
  "box_type": "rare"
}
```

**Parameters:**
- `email` (required): User's email address
- `box_type` (required): Type of mystery box - must be one of: `common`, `rare`, `epic`, `legendary`

**Response (Success):**
```json
{
  "success": true,
  "message": "Mystery box click tracked successfully",
  "box_type": "rare",
  "clicks": 5,
  "clicked_at": "2024-01-15 10:30:00"
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "User not found or account not active"
}
```

**Full URL Example:**
```
http://your-domain.com/backend/crutox/mining/api/mystery_box_click.php
```

---

### 2. Watch Ad for Mystery Box
**Endpoint:** `POST /api/mystery_box_watch_ad.php`

Records when a user watches an ad for a mystery box. Enforces cooldown periods between ads.

**Request Body:**
```json
{
  "email": "user@example.com",
  "box_type": "rare"
}
```

**Parameters:**
- `email` (required): User's email address
- `box_type` (required): Type of mystery box - must be one of: `common`, `rare`, `epic`, `legendary`

**Response (Success):**
```json
{
  "success": true,
  "message": "Ad watched successfully",
  "ads_watched": 2,
  "ads_required": 3,
  "can_open_box": false,
  "cooldown_until": "2024-01-15 10:35:00",
  "cooldown_minutes": 5
}
```

**Response (Error - Cooldown Active):**
```json
{
  "success": false,
  "message": "Cooldown active. Please wait.",
  "seconds_remaining": 180,
  "cooldown_until": "2024-01-15 10:35:00"
}
```

**Full URL Example:**
```
http://your-domain.com/backend/crutox/mining/api/mystery_box_watch_ad.php
```

---

### 3. Open Mystery Box
**Endpoint:** `POST /api/mystery_box_open.php`

Opens a mystery box and gives a random reward. Only works after all required ads are watched.

**Request Body:**
```json
{
  "email": "user@example.com",
  "box_type": "rare"
}
```

**Parameters:**
- `email` (required): User's email address
- `box_type` (required): Type of mystery box - must be one of: `common`, `rare`, `epic`, `legendary`

**Response (Success):**
```json
{
  "success": true,
  "message": "Box opened successfully",
  "reward": 8.5,
  "new_balance": 108.5,
  "box_type": "rare"
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Not all ads watched yet"
}
```

**Full URL Example:**
```
http://your-domain.com/backend/crutox/mining/api/mystery_box_open.php
```

---

## Admin API Endpoints

### 4. Get User's Mystery Box Data
**Endpoint:** `GET /api/admin/mystery_box_reset.php`

Retrieves mystery box data (clicks, ads watched, progress) for a specific user.

**Query Parameters:**
- `user_identifier` (required): User's email, username, or ID

**Request Example:**
```
GET /api/admin/mystery_box_reset.php?user_identifier=user@example.com
```

**Response (Success):**
```json
{
  "success": true,
  "user_id": 123,
  "user_email": "user@example.com",
  "user_username": "username",
  "mystery_box_data": [
    {
      "box_type": "rare",
      "clicks": 5,
      "ads_watched": 2,
      "ads_required": 3,
      "box_opened": false,
      "reward_coins": null,
      "last_clicked_at": "2024-01-15 10:30:00",
      "last_ad_watched_at": "2024-01-15 10:25:00",
      "cooldown_until": "2024-01-15 10:30:00",
      "opened_at": null,
      "created_at": "2024-01-15 10:20:00"
    }
  ]
}
```

**Full URL Example:**
```
http://your-domain.com/backend/crutox/mining/api/admin/mystery_box_reset.php?user_identifier=user@example.com
```

---

### 5. Reset User's Mystery Box Data
**Endpoint:** `POST /api/admin/mystery_box_reset.php`

Resets mystery box data (clicks, ads watched, progress) for a specific user. Can reset all box types or a specific box type.

**Request Body:**
```json
{
  "user_identifier": "user@example.com",
  "box_type": "all"
}
```

**Parameters:**
- `user_identifier` (required): User's email, username, or ID
- `box_type` (optional): Box type to reset - must be one of: `common`, `rare`, `epic`, `legendary`, or `all` (default: `all`)

**Response (Success - All Types):**
```json
{
  "success": true,
  "message": "All mystery box data reset successfully for user.",
  "user_id": 123,
  "user_email": "user@example.com",
  "user_username": "username",
  "affected_records": 4,
  "reset_type": "all"
}
```

**Response (Success - Specific Type):**
```json
{
  "success": true,
  "message": "Mystery box data for 'rare' reset successfully for user.",
  "user_id": 123,
  "user_email": "user@example.com",
  "user_username": "username",
  "box_type": "rare",
  "affected_records": 1,
  "reset_type": "specific"
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "User not found or account is not active."
}
```

**Full URL Example:**
```
http://your-domain.com/backend/crutox/mining/api/admin/mystery_box_reset.php
```

---

## Admin Panel Pages

### Users Management Page
**URL:** `/admin/users.php`

This page displays:
- All users with their coin balances
- Active boosters for each user
- **Mystery Box Data for each user:**
  - Box type (common, rare, epic, legendary)
  - Number of clicks
  - Ads watched vs ads required
  - Box opened status
  - Reward coins (if opened)
  - Last clicked timestamp
  - Last ad watched timestamp

**Reset Functionality:**
- Form to reset mystery box data for any user
- Can reset all box types or specific box type
- Accepts user email, username, or ID

**Full URL Example:**
```
http://your-domain.com/backend/crutox/mining/admin/users.php
```

---

## Frontend Integration Examples

### JavaScript/Flutter Example - Track Click

```javascript
// Track mystery box click
async function trackMysteryBoxClick(email, boxType) {
  const response = await fetch('http://your-domain.com/backend/crutox/mining/api/mystery_box_click.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      email: email,
      box_type: boxType // 'common', 'rare', 'epic', or 'legendary'
    })
  });
  
  const data = await response.json();
  return data;
}
```

### JavaScript/Flutter Example - Watch Ad

```javascript
// Watch ad for mystery box
async function watchMysteryBoxAd(email, boxType) {
  const response = await fetch('http://your-domain.com/backend/crutox/mining/api/mystery_box_watch_ad.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      email: email,
      box_type: boxType
    })
  });
  
  const data = await response.json();
  return data;
}
```

### JavaScript/Flutter Example - Open Box

```javascript
// Open mystery box
async function openMysteryBox(email, boxType) {
  const response = await fetch('http://your-domain.com/backend/crutox/mining/api/mystery_box_open.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      email: email,
      box_type: boxType
    })
  });
  
  const data = await response.json();
  return data;
}
```

### JavaScript/Flutter Example - Get User Mystery Box Data (Admin)

```javascript
// Get user's mystery box data (Admin API)
async function getUserMysteryBoxData(userIdentifier) {
  const response = await fetch(`http://your-domain.com/backend/crutox/mining/api/admin/mystery_box_reset.php?user_identifier=${encodeURIComponent(userIdentifier)}`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
    }
  });
  
  const data = await response.json();
  return data;
}
```

### JavaScript/Flutter Example - Reset Mystery Box Data (Admin)

```javascript
// Reset user's mystery box data (Admin API)
async function resetMysteryBoxData(userIdentifier, boxType = 'all') {
  const response = await fetch('http://your-domain.com/backend/crutox/mining/api/admin/mystery_box_reset.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      user_identifier: userIdentifier, // email, username, or ID
      box_type: boxType // 'all', 'common', 'rare', 'epic', or 'legendary'
    })
  });
  
  const data = await response.json();
  return data;
}
```

---

## Important Notes

1. **Email Parameter:** All user-facing APIs accept `email` as the user identifier. No password required - authentication is done via email only.

2. **Admin APIs:** Admin APIs accept `user_identifier` which can be:
   - User's email address
   - Username
   - User ID

3. **Box Types:** Valid box types are:
   - `common`
   - `rare`
   - `epic`
   - `legendary`

4. **CORS:** All APIs have CORS enabled for cross-origin requests.

5. **Error Handling:** Always check the `success` field in the response before processing data.

6. **Base URL:** Replace `http://your-domain.com/backend/crutox/mining` with your actual domain and path.

---

## Database Schema

The mystery box data is stored in the `mystery_box_claims` table with the following key columns:
- `user_id`: User ID
- `box_type`: Type of mystery box
- `clicks`: Number of times the box was clicked
- `last_clicked_at`: Timestamp of last click
- `ads_watched`: Number of ads watched
- `ads_required`: Number of ads required to open
- `box_opened`: Whether the box has been opened
- `reward_coins`: Coins rewarded when box was opened
- `last_ad_watched_at`: Timestamp of last ad watched
- `cooldown_until`: Cooldown expiration timestamp
- `opened_at`: Timestamp when box was opened
- `created_at`: Record creation timestamp

---

## Testing

To test the APIs, you can use tools like:
- Postman
- cURL
- JavaScript fetch API
- Flutter http package

Make sure to:
1. Use valid user credentials (email and password)
2. Use valid box types
3. Check response for success/error messages
4. Handle cooldown periods appropriately

---

**Last Updated:** 2024-01-15

