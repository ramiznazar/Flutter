# KYC API Integration Guide for Flutter

This guide provides information on how to integrate KYC (Know Your Customer) functionality into your Flutter application using the backend API endpoints.

---

## 📦 Required Packages

Add these dependencies to your `pubspec.yaml`:

```yaml
dependencies:
  http: ^1.1.0
  image_picker: ^1.0.4
  image_cropper: ^5.0.1  # Optional, for image cropping
  path_provider: ^2.1.1  # Optional, for file handling
  permission_handler: ^11.0.1  # For camera/storage permissions
```

Install packages:
```bash
flutter pub get
```

---

## 🔗 API Endpoints

### Base URL
```
http://192.168.43.19:8000/mining/api
```
*Note: Update this IP address based on your server's current IP*

---

## 📋 Endpoint 1: Get KYC Progress

**Endpoint:** `POST /get_kyc_progress.php`

**Purpose:** Check user's progress towards KYC eligibility (mining sessions and referrals completed)

### Request

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

Future<Map<String, dynamic>> getKycProgress(String email) async {
  final url = Uri.parse('http://192.168.43.19:8000/mining/api/get_kyc_progress.php');
  
  final response = await http.post(
    url,
    headers: {
      'Content-Type': 'application/json',
    },
    body: jsonEncode({
      'email': email,
    }),
  );
  
  if (response.statusCode == 200) {
    return jsonDecode(response.body);
  } else {
    throw Exception('Failed to load KYC progress');
  }
}
```

### Request Body
```json
{
  "email": "user@example.com"
}
```

### Response (Success)
```json
{
  "success": true,
  "message": "KYC progress retrieved successfully",
  "data": {
    "mining_sessions": 9,
    "mining_sessions_required": 14,
    "mining_sessions_remaining": 5,
    "mining_progress": "9/14",
    "referrals": 5,
    "referrals_required": 10,
    "referrals_remaining": 5,
    "referrals_progress": "5/10",
    "is_eligible": false,
    "can_submit": false,
    "kyc_status": null
  }
}
```

### Response (User Not Found)
```json
{
  "success": false,
  "message": "User not found or account not active"
}
```

---

## 📋 Endpoint 2: Submit KYC Documents

**Endpoint:** `POST /submit_kyc.php`

**Purpose:** Submit KYC documents (ID card front/back images, full name, DOB) for verification using Didit API

### Image Preparation

You need to convert images to base64 format before sending:

```dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';

// Function to convert image file to base64
Future<String> imageToBase64(File imageFile) async {
  List<int> imageBytes = await imageFile.readAsBytes();
  String base64Image = base64Encode(imageBytes);
  return base64Image;
}

// Alternative: Convert XFile (from image_picker) to base64
Future<String> xFileToBase64(XFile imageFile) async {
  List<int> imageBytes = await imageFile.readAsBytes();
  String base64Image = base64Encode(imageBytes);
  return base64Image;
}
```

### Request Function

```dart
Future<Map<String, dynamic>> submitKyc({
  required String email,
  required String fullName,
  required String dob, // Format: YYYY-MM-DD
  required String frontImageBase64,
  required String backImageBase64,
}) async {
  final url = Uri.parse('http://192.168.43.19:8000/mining/api/submit_kyc.php');
  
  final response = await http.post(
    url,
    headers: {
      'Content-Type': 'application/json',
    },
    body: jsonEncode({
      'email': email,
      'full_name': fullName,
      'dob': dob, // Must be in YYYY-MM-DD format
      'front_image': frontImageBase64,
      'back_image': backImageBase64,
    }),
  );
  
  if (response.statusCode == 200) {
    return jsonDecode(response.body);
  } else {
    throw Exception('Failed to submit KYC');
  }
}
```

### Complete Example Flow

```dart
import 'package:image_picker/image_picker.dart';
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;

class KycService {
  final String baseUrl = 'http://192.168.43.19:8000/mining/api';
  final ImagePicker _picker = ImagePicker();
  
  // Pick front image
  Future<XFile?> pickFrontImage() async {
    final XFile? image = await _picker.pickImage(
      source: ImageSource.camera, // or ImageSource.gallery
      imageQuality: 85, // Reduce quality to reduce file size
      maxWidth: 1920,
      maxHeight: 1080,
    );
    return image;
  }
  
  // Pick back image
  Future<XFile?> pickBackImage() async {
    final XFile? image = await _picker.pickImage(
      source: ImageSource.camera,
      imageQuality: 85,
      maxWidth: 1920,
      maxHeight: 1080,
    );
    return image;
  }
  
  // Convert XFile to base64
  Future<String> imageToBase64(XFile imageFile) async {
    final bytes = await imageFile.readAsBytes();
    return base64Encode(bytes);
  }
  
  // Get KYC Progress
  Future<Map<String, dynamic>> getProgress(String email) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/get_kyc_progress.php'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'email': email}),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }
  
  // Submit KYC
  Future<Map<String, dynamic>> submitKyc({
    required String email,
    required String fullName,
    required DateTime dateOfBirth,
    required XFile frontImage,
    required XFile backImage,
  }) async {
    try {
      // Convert images to base64
      final frontBase64 = await imageToBase64(frontImage);
      final backBase64 = await imageToBase64(backImage);
      
      // Format date as YYYY-MM-DD
      final dobString = '${dateOfBirth.year}-${dateOfBirth.month.toString().padLeft(2, '0')}-${dateOfBirth.day.toString().padLeft(2, '0')}';
      
      final response = await http.post(
        Uri.parse('$baseUrl/submit_kyc.php'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'email': email,
          'full_name': fullName,
          'dob': dobString,
          'front_image': frontBase64,
          'back_image': backBase64,
        }),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }
}
```

### Request Body
```json
{
  "email": "user@example.com",
  "full_name": "John Doe",
  "dob": "1990-01-15",
  "front_image": "base64_encoded_image_string",
  "back_image": "base64_encoded_image_string"
}
```

### Response (Success)
```json
{
  "success": true,
  "message": "KYC submitted successfully. Document verified by Didit.",
  "data": {
    "didit_request_id": "a1b2c3d4-e5f6-7890-1234-567890abcdef",
    "verification_status": "Approved",
    "kyc_status": "pending"
  }
}
```

### Response (Task Not Completed)
```json
{
  "success": false,
  "message": "You have not completed the required tasks. Mining Sessions: 9/14, Referrals: 5/10"
}
```

### Response (Already Submitted)
```json
{
  "success": false,
  "message": "KYC already submitted and is pending"
}
```

### Response (Invalid Image)
```json
{
  "success": false,
  "message": "Invalid image format. Please provide valid base64 encoded images."
}
```

---

## 📋 Endpoint 3: Get KYC Status (Optional)

**Endpoint:** `POST /kyc_get_status.php`

**Purpose:** Get the current status of user's KYC submission

### Request

```dart
Future<Map<String, dynamic>> getKycStatus({
  required String email,
  required String password, // This endpoint requires password
}) async {
  final url = Uri.parse('http://192.168.43.19:8000/mining/api/kyc_get_status.php');
  
  final response = await http.post(
    url,
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({
      'email': email,
      'password': password,
    }),
  );
  
  return jsonDecode(response.body);
}
```

### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "full_name": "John Doe",
    "dob": "1990-01-15",
    "front_image": "image_url_or_base64",
    "back_image": "image_url_or_base64",
    "status": "pending",
    "admin_notes": null,
    "created_at": "2024-01-15 10:30:00",
    "updated_at": null
  }
}
```

---

## 🔄 Complete Flow Example

```dart
// 1. Check KYC Progress
final progress = await kycService.getProgress(userEmail);
if (progress['success'] == true) {
  final data = progress['data'];
  print('Mining Sessions: ${data['mining_progress']}');
  print('Referrals: ${data['referrals_progress']}');
  print('Can Submit: ${data['can_submit']}');
  
  if (data['can_submit'] == true) {
    // 2. Pick Images
    final frontImage = await kycService.pickFrontImage();
    final backImage = await kycService.pickBackImage();
    
    if (frontImage != null && backImage != null) {
      // 3. Submit KYC
      final result = await kycService.submitKyc(
        email: userEmail,
        fullName: 'John Doe',
        dateOfBirth: DateTime(1990, 1, 15),
        frontImage: frontImage,
        backImage: backImage,
      );
      
      if (result['success'] == true) {
        print('KYC Submitted: ${result['message']}');
        print('Didit Status: ${result['data']['verification_status']}');
      } else {
        print('Error: ${result['message']}');
      }
    }
  }
}
```

---

## ⚠️ Important Notes

### Image Requirements
- **Format:** JPEG, PNG, WebP, TIFF, PDF
- **Maximum Size:** 5MB per image
- **Encoding:** Must be base64 encoded when sending to API
- **Quality:** Recommend compressing images (quality 75-85) before encoding

### Date Format
- Date of Birth must be in `YYYY-MM-DD` format
- Example: `1990-01-15`

### Error Handling
Always check the `success` field in the response:
```dart
if (response['success'] == true) {
  // Handle success
} else {
  // Handle error
  print('Error: ${response['message']}');
}
```

### Permissions (Android)
Add to `android/app/src/main/AndroidManifest.xml`:
```xml
<uses-permission android:name="android.permission.CAMERA"/>
<uses-permission android:name="android.permission.READ_EXTERNAL_STORAGE"/>
<uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE"/>
```

### Permissions (iOS)
Add to `ios/Runner/Info.plist`:
```xml
<key>NSCameraUsageDescription</key>
<string>We need access to camera to capture ID documents</string>
<key>NSPhotoLibraryUsageDescription</key>
<string>We need access to photo library to select ID documents</string>
```

---

## 🧪 Testing

### Test Get Progress
```dart
final result = await kycService.getProgress('test@example.com');
print(result);
```

### Test Submit KYC
```dart
// Use test images
final result = await kycService.submitKyc(
  email: 'test@example.com',
  fullName: 'Test User',
  dateOfBirth: DateTime(1990, 1, 1),
  frontImage: frontImageFile,
  backImage: backImageFile,
);
print(result);
```

---

## 📝 Response Status Codes

- **200:** Success
- **400:** Bad Request (missing/invalid data)
- **404:** User not found
- **500:** Server error

---

## 🔐 Security Notes

- The `submit_kyc.php` endpoint uses email-only authentication (no password required)
- Images are verified using Didit API automatically
- User email is used as `vendor_data` in Didit API to match verification with user account
- All verification results are stored in the database for admin review

---

## 📞 Support

For issues or questions, check:
- API endpoint responses for error messages
- Server logs for detailed error information
- Didit API documentation for verification status details

