# Daily Tasks API Documentation

This document explains the complete flow for users to complete daily tasks and claim rewards.

## Overview

The daily tasks system works in 3 main steps:
1. **Get Available Tasks** - Fetch list of available daily tasks
2. **Start Task** - User clicks on a task to start it (timer begins)
3. **Claim Reward** - After timer expires, user claims coins reward

---

## API Endpoints

### 1. Get Available Daily Tasks

**Endpoint:** `GET /api/admin/tasks_manage?type=daily`

**Method:** `GET`

**Query Parameters:**
- `type` (required): `daily` | `onetime` | `all`

**Request Example:**
```http
GET {{base_url}}/api/admin/tasks_manage?type=daily
```

**Response (Success - 200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Twitter",
      "reward": "2",
      "redirect_link": "https://twitter.com/CrutoxApp",
      "icon": "https://img.icons8.com/color/48/114450/twitter-circled"
    },
    {
      "id": 2,
      "name": "Instagram",
      "reward": "2",
      "redirect_link": "https://instagram.com/crutox",
      "icon": "https://img.icons8.com/color/48/000000/instagram-new--v1.png"
    },
    {
      "id": 3,
      "name": "Telegram",
      "reward": "2",
      "redirect_link": "https://t.me/crutox",
      "icon": "https://img.icons8.com/color/48/oWiuH0jFiU0R/telegram-app"
    }
  ],
  "reset_time": "2025-12-27 00:00:00"
}
```

**Response Fields:**
- `success`: Boolean indicating if request was successful
- `data`: Array of available daily tasks
  - `id`: Task ID (use this for task_start and task_claim_reward)
  - `name`: Task name (e.g., "Twitter", "Instagram")
  - `reward`: Coin reward amount (string)
  - `redirect_link`: URL to redirect user when clicking task
  - `icon`: Icon URL for the task
- `reset_time`: Daily reset time (when tasks reset for next day)

---

### 2. Start a Task (User Clicks on Task)

**Endpoint:** `POST /api/task_start`

**Method:** `POST`

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "task_id": 1,
  "task_type": "daily"
}
```

**Request Fields:**
- `email` (required): User's email address
- `task_id` (required): Task ID from the tasks list
- `task_type` (required): `"daily"` or `"onetime"`

**Request Example (cURL):**
```bash
curl -X POST "http://localhost:8000/api/task_start" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "task_id": 1,
    "task_type": "daily"
  }'
```

**Response (Success - 200):**
```json
{
  "success": true,
  "message": "Task started successfully",
  "reward_available_at": "2025-12-27 15:05:00",
  "seconds_remaining": 300,
  "task_type": "daily",
  "reward": 2.0
}
```

**Response (Task Already Started - 200):**
```json
{
  "success": true,
  "message": "Task already started. Reward is available.",
  "reward_available": true,
  "reward_available_at": "2025-12-27 15:05:00"
}
```

**Response (Task In Progress - 200):**
```json
{
  "success": true,
  "message": "Task already in progress.",
  "reward_available": false,
  "reward_available_at": "2025-12-27 15:05:00",
  "seconds_remaining": 150
}
```

**Response (Already Completed Today - 400):**
```json
{
  "success": false,
  "message": "Daily task already completed today"
}
```

**Response Fields:**
- `success`: Boolean indicating if request was successful
- `message`: Human-readable message
- `reward_available_at`: DateTime when reward becomes available (format: `YYYY-MM-DD HH:mm:ss`)
- `seconds_remaining`: Number of seconds until reward is available
- `reward_available`: Boolean indicating if reward is ready to claim
- `reward`: Coin reward amount (float)
- `task_type`: Type of task (`daily` or `onetime`)

**Important Notes:**
- **Daily tasks**: Timer is **5 minutes** (300 seconds)
- **One-time tasks**: Timer is **1 hour** (3600 seconds)
- If task is already started, you'll get the status instead of creating a new one
- Daily tasks can only be completed once per day (resets at `reset_time`)

---

### 3. Claim Task Reward (After Timer Expires)

**Endpoint:** `POST /api/task_claim_reward`

**Method:** `POST`

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "task_id": 1,
  "task_type": "daily"
}
```

**Request Fields:**
- `email` (required): User's email address
- `task_id` (required): Task ID (same as used in task_start)
- `task_type` (required): `"daily"` or `"onetime"`

**Request Example (cURL):**
```bash
curl -X POST "http://localhost:8000/api/task_claim_reward" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "task_id": 1,
    "task_type": "daily"
  }'
```

**Response (Success - 200):**
```json
{
  "success": true,
  "message": "Reward claimed successfully",
  "reward": 2.0,
  "new_balance": 52.5
}
```

**Response (Timer Still Running - 400):**
```json
{
  "success": false,
  "message": "Reward not yet available. Timer still running.",
  "seconds_remaining": 45,
  "reward_available_at": "2025-12-27 15:05:00"
}
```

**Response (No Active Task - 404):**
```json
{
  "success": false,
  "message": "No active task found. Please start the task first."
}
```

**Response (Task Not Found - 404):**
```json
{
  "success": false,
  "message": "Task not found"
}
```

**Response (User Not Found - 404):**
```json
{
  "success": false,
  "message": "User not found or account not active"
}
```

**Response Fields:**
- `success`: Boolean indicating if request was successful
- `message`: Human-readable message
- `reward`: Coin reward amount that was claimed (float)
- `new_balance`: User's updated token balance after claiming (float)
- `seconds_remaining`: Number of seconds remaining if timer is still running
- `reward_available_at`: DateTime when reward becomes available

**Important Notes:**
- **Backend enforces timer**: You cannot claim before the timer expires
- Coins are automatically added to user's `token` balance
- Task is marked as `reward_claimed = 1` and cannot be claimed again
- For daily tasks, user must wait for next day's reset to do it again

---

## Complete Flow Example

### Step 1: User Opens Daily Tasks Screen

**Request:**
```http
GET http://localhost:8000/api/admin/tasks_manage?type=daily
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Twitter",
      "reward": "2",
      "redirect_link": "https://twitter.com/CrutoxApp",
      "icon": "https://img.icons8.com/color/48/114450/twitter-circled"
    }
  ],
  "reset_time": "2025-12-27 00:00:00"
}
```

**UI Action:** Display tasks in a list with name, icon, and reward amount.

---

### Step 2: User Clicks on "Twitter" Task

**Request:**
```http
POST http://localhost:8000/api/task_start
Content-Type: application/json

{
  "email": "user@example.com",
  "task_id": 1,
  "task_type": "daily"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Task started successfully",
  "reward_available_at": "2025-12-27 15:05:00",
  "seconds_remaining": 300,
  "task_type": "daily",
  "reward": 2.0
}
```

**UI Actions:**
1. Open `redirect_link` (e.g., `https://twitter.com/CrutoxApp`) in browser/app
2. Show countdown timer with `seconds_remaining` (300 seconds = 5 minutes)
3. Disable "Start" button, enable "Claim Reward" button (but keep it disabled until timer expires)
4. Display countdown: "Claim available in: 4:59"

---

### Step 3: User Waits for Timer to Expire

**UI Actions:**
- Update countdown timer every second
- When `seconds_remaining` reaches 0, enable "Claim Reward" button
- Show message: "Reward available! Click to claim"

---

### Step 4: User Clicks "Claim Reward" Button

**Request:**
```http
POST http://localhost:8000/api/task_claim_reward
Content-Type: application/json

{
  "email": "user@example.com",
  "task_id": 1,
  "task_type": "daily"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Reward claimed successfully",
  "reward": 2.0,
  "new_balance": 52.5
}
```

**UI Actions:**
1. Show success message: "🎉 +2.0 coins claimed!"
2. Update user's coin balance display to `new_balance` (52.5)
3. Mark task as "Completed" with checkmark
4. Disable "Claim Reward" button (task cannot be claimed again today)
5. Optionally: Show animation of coins being added to balance

---

## Error Handling

### Common Error Scenarios

1. **Task Not Started Before Claiming**
   - Error: `"No active task found. Please start the task first."`
   - Solution: Call `task_start` first before `task_claim_reward`

2. **Timer Not Expired**
   - Error: `"Reward not yet available. Timer still running."`
   - Solution: Wait for timer to expire, or check `seconds_remaining` in response

3. **Daily Task Already Completed**
   - Error: `"Daily task already completed today"`
   - Solution: User must wait until `reset_time` (usually next day)

4. **User Not Found**
   - Error: `"User not found or account not active"`
   - Solution: Verify email is correct and account is active

5. **Task Not Found**
   - Error: `"Task not found"`
   - Solution: Verify `task_id` exists in the tasks list

---

## Postman Collection Examples

### Get Daily Tasks
```
GET {{base_url}}/api/admin/tasks_manage?type=daily
```

### Start Daily Task
```
POST {{base_url}}/api/task_start
Content-Type: application/json

{
  "email": "user@example.com",
  "task_id": 1,
  "task_type": "daily"
}
```

### Claim Reward
```
POST {{base_url}}/api/task_claim_reward
Content-Type: application/json

{
  "email": "user@example.com",
  "task_id": 1,
  "task_type": "daily"
}
```

---

## Additional Endpoint: Check Task Status

**Endpoint:** `POST /api/task_track`

**Purpose:** Track user interaction with tasks (optional, for analytics)

**Request:**
```json
{
  "email": "user@example.com",
  "task_id": 1,
  "task_type": "daily",
  "action": "viewed" // or "started", "completed", "claimed"
}
```

---

## Summary

**Complete Flow:**
1. **GET** `/api/admin/tasks_manage?type=daily` → Get available tasks
2. **POST** `/api/task_start` → User clicks task, timer starts (5 min for daily)
3. Wait for timer to expire (check `reward_available_at` or countdown)
4. **POST** `/api/task_claim_reward` → User claims coins, balance updates

**Key Points:**
- Daily tasks: 5-minute timer
- One-time tasks: 1-hour timer
- Daily tasks reset at `reset_time` (usually midnight)
- Backend enforces timer - cannot claim early
- Coins automatically added to user's `token` balance
- Button states: "Start" → "Waiting..." → "Claim Reward" → "Completed"

