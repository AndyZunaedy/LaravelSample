# Laravel Code Sample – UsersRepository & DailyClosingController

This repository contains Laravel code samples I personally wrote for internal system use, including user authentication and automated daily closing PDF generation.

## 🧩 Code Sample 1: `UsersRepository.php`
Location: `app/Http/Repository/UsersRepository.php`

Handles user login, session management, API token authentication, and related data retrieval.

### 🔑 Features:
- `GetUserLogin($username, $password)`  
  Authenticates user credentials with an external API and stores user data into session.
  
- `GetGroupMapping(Request $request)`  
  Retrieves mapping of user's group if the user has specific roles (e.g., KC or SAO).

- `GetUnit(Request $request)`  
  Gets the organizational unit of the logged-in user.

- `UploadS3($foto)`  
  Uploads employee photo to a remote S3-compatible API (simulated).

- `GetBrnetOpsDate(Request $request, $OurBranchID)`  
  Retrieves operational date information from an API endpoint.

- `GetUserMobile()`  
  Fetches mobile user data based on session info.

> This repository shows how I manage structured HTTP communication, handle authentication, and format user data into Laravel's session layer.

---

## 📁 Code Sample 2: `DailyClosingController`
Location: `app/Http/Controllers/DailyClosingController.php`

Handles the generation and packaging of multiple daily financial reports into PDF and ZIP files, including upload to external API.

### ⚙️ Main Method
```php
public function generateDailyClosing(Request $request)
