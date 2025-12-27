# KYC Submission API Documentation

Complete guide for KYC (Know Your Customer) submission with Didit.me ID Verification integration.

## Overview

The KYC system allows users to submit their identity documents for verification. The system:
1. Checks if user is eligible (completed required mining sessions and referrals)
2. Submits KYC documents to Didit.me for automatic verification
3. Stores submission in database with Didit verification results
4. Allows admin to review and update status in admin panel

---

## API Endpoints

### 1. Check KYC Eligibility

**Endpoint:** `POST /api/kyc_check_eligibility`

**Purpose:** Check if user meets requirements to submit KYC (mining sessions and referrals)

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "mining_sessions": 15,
    "mining_sessions_required": 14,
    "referrals": 10,
    "referrals_required": 10,
    "is_eligible": true,
    "can_submit": true,
    "kyc_status": null,
    "mining_progress": "15/14",
    "referrals_progress": "10/10"
  }
}
```

**Response Fields:**
- `is_eligible`: Boolean - User meets all requirements
- `can_submit`: Boolean - User can submit KYC (eligible AND no pending/approved submission)
- `kyc_status`: Current KYC status (`null`, `pending`, `approved`, `rejected`)
- `mining_sessions`: User's completed mining sessions
- `mining_sessions_required`: Required mining sessions (default: 14)
- `referrals`: User's total referrals
- `referrals_required`: Required referrals (default: 10)

---

### 2. Submit KYC

**Endpoint:** `POST /api/kyc_submit` or `POST /api/submit_kyc`

**Purpose:** Submit KYC documents for verification (automatically calls Didit.me API)

**Request Body:**
```json
{
  "email": "user@example.com",
  "full_name": "John Doe",
  "dob": "1990-01-15",
  "front_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ...",
  "back_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ..."
}
```

**Request Fields:**
- `email` (required): User's email address
- `full_name` (required): Full name as shown on ID document
- `dob` (required): Date of birth in `YYYY-MM-DD` format
- `front_image` (required): Base64 encoded image of ID front side (can include `data:image/jpeg;base64,` prefix)
- `back_image` (required): Base64 encoded image of ID back side (can include `data:image/jpeg;base64,` prefix)

**Request Example (cURL):**
```bash
curl -X POST "http://localhost:8000/api/kyc_submit" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "full_name": "John Doe",
    "dob": "1990-01-15",
    "front_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ...",
    "back_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ..."
  }'
```

**Response (Success - 200):**
```json
{
  "success": true,
  "message": "KYC submitted and verified successfully.",
  "data": {
    "kyc_id": 1,
    "status": "pending",
    "didit_request_id": "a1b2c3d4-e5f6-7890-1234-567890abcdef",
    "verification_status": "APPROVED"
  }
}
```

**Response (Not Eligible - 400):**
```json
{
  "success": false,
  "message": "Not eligible to submit KYC or already submitted"
}
```

**Response (Already Submitted - 400):**
```json
{
  "success": false,
  "message": "KYC submission already exists and is pending or approved"
}
```

**Response (Invalid Image - 400):**
```json
{
  "success": false,
  "message": "Invalid image format. Please provide valid base64 encoded images."
}
```

**Response Fields:**
- `kyc_id`: Database ID of the KYC submission
- `status`: Submission status (`pending`, `approved`, `rejected`)
- `didit_request_id`: Didit.me verification request ID
- `verification_status`: Didit.me verification status (`APPROVED`, `DECLINED`, etc.)

**Important Notes:**
- Images can be base64 strings or data URIs (e.g., `data:image/jpeg;base64,...`)
- Didit.me API is automatically called during submission
- Even if Didit verification fails, submission is saved with `pending` status for admin review
- Admin can override Didit status in admin panel

---

### 3. Get KYC Status

**Endpoint:** `POST /api/kyc_get_status`

**Purpose:** Get current KYC submission status for a user

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "status": "pending",
    "full_name": "John Doe",
    "submitted_at": "2025-12-27T10:30:00.000000Z",
    "admin_notes": "Under review"
  }
}
```

**Response (No Submission - 200):**
```json
{
  "success": true,
  "data": {
    "status": null,
    "message": "No KYC submission found"
  }
}
```

---

### 4. Get KYC Progress

**Endpoint:** `POST /api/get_kyc_progress`

**Purpose:** Same as `kyc_check_eligibility` (alias endpoint)

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Response:** Same as `/api/kyc_check_eligibility`

---

## Complete Flow Example

### Step 1: Check Eligibility

**Request:**
```http
POST http://localhost:8000/api/kyc_check_eligibility
Content-Type: application/json

{
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "mining_sessions": 15,
    "mining_sessions_required": 14,
    "referrals": 10,
    "referrals_required": 10,
    "is_eligible": true,
    "can_submit": true,
    "kyc_status": null
  }
}
```

**UI Action:** If `can_submit` is `true`, show "Submit KYC" button.

---

### Step 2: User Fills KYC Form

**UI Form Fields:**
- Full Name (text input)
- Date of Birth (date picker, format: YYYY-MM-DD)
- Front ID Image (camera/gallery picker)
- Back ID Image (camera/gallery picker)

**Image Processing:**
```javascript
// Convert image to base64
function imageToBase64(imageFile) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(imageFile);
    reader.onload = () => resolve(reader.result);
    reader.onerror = error => reject(error);
  });
}

// Example usage
const frontImageBase64 = await imageToBase64(frontImageFile);
const backImageBase64 = await imageToBase64(backImageFile);
```

---

### Step 3: Submit KYC

**Request:**
```http
POST http://localhost:8000/api/kyc_submit
Content-Type: application/json

{
  "email": "user@example.com",
  "full_name": "John Doe",
  "dob": "1990-01-15",
  "front_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ...",
  "back_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "KYC submitted and verified successfully.",
  "data": {
    "kyc_id": 1,
    "status": "pending",
    "didit_request_id": "a1b2c3d4-e5f6-7890-1234-567890abcdef",
    "verification_status": "APPROVED"
  }
}
```

**UI Actions:**
1. Show success message: "✅ KYC submitted successfully!"
2. Display status: "Status: Pending Admin Review"
3. If Didit status is `APPROVED`: "✅ Automatic verification passed"
4. If Didit status is `DECLINED`: "⚠️ Automatic verification declined. Awaiting manual review."
5. Disable form (user cannot resubmit until rejected)

---

### Step 4: Check Status (Periodically)

**Request:**
```http
POST http://localhost:8000/api/kyc_get_status
Content-Type: application/json

{
  "email": "user@example.com"
}
```

**Response (Approved):**
```json
{
  "success": true,
  "data": {
    "status": "approved",
    "full_name": "John Doe",
    "submitted_at": "2025-12-27T10:30:00.000000Z",
    "admin_notes": "KYC approved. Welcome!"
  }
}
```

**UI Action:** Show "✅ KYC Approved" with success message.

---

## Admin Panel Integration

### View KYC Submissions

**Route:** `http://localhost:8000/admin/kyc`

**Features:**
- View all KYC submissions in a table
- See Didit verification status for each submission
- Click "View/Edit" to see full details and images
- Update status: `pending`, `approved`, `rejected`
- Add admin notes
- View Didit verification data (request ID, status, full response)

### Update KYC Status

**Route:** `POST http://localhost:8000/admin/kyc/update-status`

**Form Data:**
- `kyc_id`: KYC submission ID
- `status`: `pending` | `approved` | `rejected`
- `admin_notes`: Optional notes for the user

**Admin Actions:**
1. Review Didit verification results
2. View front and back ID images
3. Change status (can override Didit status)
4. Add notes visible to user in status response

---

## Didit.me Integration

The system automatically integrates with [Didit.me ID Verification API](https://docs.didit.me/reference/id-verification-standalone-api).

### Configuration

**API Key:** `7wk_58gFnb27uqgApuMlEcpASwUurvX8IP6cKAZc4P4`  
**App ID:** `ea69c49c-e8f0-4c64-aa9c-6a3cfa636232`  
**API URL:** `https://verification.didit.me/v2/id-verification/`

### Features Enabled

- Document liveness detection
- Expiration date detection (auto-decline if expired)
- MRZ (Machine Readable Zone) validation (auto-decline if invalid)
- Inconsistent data detection (auto-decline if data doesn't match)

### Didit Response

The Didit verification response is stored in `didit_verification_data` field (JSON) and includes:
- `request_id`: Unique request ID
- `id_verification.status`: `APPROVED` or `DECLINED`
- Document extracted data (name, DOB, document number, etc.)
- Verification confidence scores
- Warnings and errors (if any)

---

## Error Handling

### Common Errors

1. **Not Eligible**
   - Error: `"Not eligible to submit KYC or already submitted"`
   - Solution: User must complete required mining sessions and referrals

2. **Already Submitted**
   - Error: `"KYC submission already exists and is pending or approved"`
   - Solution: User must wait for admin to approve/reject before resubmitting

3. **Invalid Image Format**
   - Error: `"Invalid image format. Please provide valid base64 encoded images."`
   - Solution: Ensure images are valid base64 encoded JPEG/PNG

4. **Invalid Date Format**
   - Error: `"Date must be YYYY-MM-DD format"`
   - Solution: Use format: `1990-01-15` (not `01/15/1990` or `15-01-1990`)

5. **User Not Found**
   - Error: `"User not found or account not active"`
   - Solution: Verify email is correct and account is active

---

## Postman Collection Examples

### Check Eligibility
```
POST {{base_url}}/api/kyc_check_eligibility
Content-Type: application/json

{
  "email": "user@example.com"
}
```

### Submit KYC
```
POST {{base_url}}/api/kyc_submit
Content-Type: application/json

{
  "email": "user@example.com",
  "full_name": "John Doe",
  "dob": "1990-01-15",
  "front_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ...",
  "back_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ..."
}
```

### Get Status
```
POST {{base_url}}/api/kyc_get_status
Content-Type: application/json

{
  "email": "user@example.com"
}
```

---

## Summary

**Complete Flow:**
1. **POST** `/api/kyc_check_eligibility` → Check if user can submit
2. User fills form (name, DOB, front/back images)
3. **POST** `/api/kyc_submit` → Submit KYC (Didit.me automatically called)
4. System saves submission with Didit verification results
5. Admin reviews in admin panel (`/admin/kyc`)
6. Admin updates status (approved/rejected)
7. **POST** `/api/kyc_get_status` → User checks status

**Key Points:**
- Didit.me verification happens automatically on submission
- Admin can override Didit status
- Users cannot resubmit until rejected
- Eligibility requires: 14 mining sessions + 10 referrals (configurable in settings)
- All Didit verification data is stored for admin review

