# KYC Submission - Quick Reference

## Route to Submit KYC

**POST** `http://localhost:8000/api/kyc_submit`

## Request Body

```json
{
  "email": "user@example.com",
  "full_name": "John Doe",
  "dob": "1990-01-15",
  "front_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ...",
  "back_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ..."
}
```

### Field Descriptions

- `email` (required): User's email address
- `full_name` (required): Full name as shown on ID document
- `dob` (required): Date of birth in `YYYY-MM-DD` format (e.g., `1990-01-15`)
- `front_image` (required): Base64 encoded image of ID front side (can include `data:image/jpeg;base64,` prefix)
- `back_image` (required): Base64 encoded image of ID back side (can include `data:image/jpeg;base64,` prefix)

## Sample Response

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

## Important Notes

1. **Eligibility Check**: User must first call `/api/kyc_check_eligibility` to ensure they meet requirements (14 mining sessions + 10 referrals)

2. **Image Format**: 
   - Accepts base64 strings with or without `data:image/jpeg;base64,` prefix
   - Supports JPEG and PNG formats

3. **Didit Integration**: 
   - Automatically calls Didit.me API for verification
   - Stores verification results in database
   - Admin can review and override status in admin panel

4. **Admin Panel**: 
   - Route: `http://localhost:8000/admin/kyc`
   - View all submissions, Didit status, and update approval status

## Related Endpoints

- **Check Eligibility**: `POST /api/kyc_check_eligibility`
- **Get Status**: `POST /api/kyc_get_status`
- **Get Progress**: `POST /api/get_kyc_progress`

## Admin Panel Route

**View/Manage KYC**: `http://localhost:8000/admin/kyc`

