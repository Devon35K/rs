# 🎓 BSIT DEPARTMENT - ACADEMIC RESOURCE MANAGEMENT SYSTEM

> A web-based platform for managing and sharing academic resources in educational institutions — built with PHP MVC (no framework), MySQL, and vanilla CSS/JS.

---

## 📌 Table of Contents

1. [Project Overview](#-project-overview)
2. [Who Uses It](#-who-uses-it)
3. [Pages & Features](#-pages--features)
4. [Guest Public Page](#-guest-public-page)
5. [Project Structure](#-project-structure)
6. [Tech Stack](#-tech-stack)
7. [Setup Instructions](#-setup-instructions)
8. [Default Login](#-default-login-credentials)
9. [User Roles & Permissions](#-user-roles--permissions)
10. [File Upload Notes](#-file-upload-notes)
11. [Security Notes](#-security-notes)
12. [Adding Users](#-adding-users)
13. [Customization](#-customization)
14. [Design Reference](#-design-reference)

---

## 📖 Project Overview

**BSIT DEPARTMENT - ACADEMIC RESOURCE MANAGEMENT SYSTEM** is a digital academic resource management system designed for schools and universities. It serves as a centralized platform where faculty can upload lecture materials and memoranda, administrators can manage users and monitor activity, and students can access all shared resources — all in one organized, secure portal.

Think of it as a **digital library and bulletin board** for your institution, accessible from any device through a web browser.

---

## 👥 Who Uses It

| User Type | Description |
|-----------|-------------|
| **Guest** | Unauthenticated visitors who can publicly browse announcements, memoranda, and uploaded documents without logging in |
| **Student** | Authenticated learners who can view and download academic resources and read announcements targeted to them |
| **Faculty** | Teachers who can post announcements, add memoranda, and upload documents for students |
| **Admin** | System administrators who have full control over all content, user accounts, and can view visit analytics |

---

## 🌟 Pages & Features

### 🔐 Login Page
The entry point for authenticated users. Displays a full-screen campus background image with a centered login card on the left side. Accepts an employee or student email and password. Session-based authentication is used with no cookies beyond the PHP session. A **"View as Guest"** link is available at the bottom of the card for users who just want to browse public content.

### 📢 Announcements Page
The main dashboard after login. Shows a hero banner with the institution's background photo, followed by a responsive card grid of announcements. Each card can be expanded to reveal the full announcement body, the author's name, and the posting date. Faculty and admins can post new announcements through a modal form, setting a title, body text, target audience (Everyone, Faculty Only, or Students Only), and an optional cover image.

### 📋 Memorandum Page
A sortable and searchable data table listing all memoranda issued by the institution. Each row shows the Memo Number, Date Issued, Subject, Category, Type (Internal or External), and a link or file attachment button. The table supports per-page row count control and live text search filtering. Faculty and admins can add new memos through a modal form; admins can delete entries.

### 📁 Upload / Requirements Page
A card grid displaying all uploaded academic documents such as syllabi, modules, requirements, and forms. Each card shows the file type icon, document title, uploader name, upload date, and an audience badge. Students can click to download files. Faculty and admins can upload new files through a drag-and-drop modal form with an optional Google Drive link. Files are stored locally in the `public/uploads/` directory.

### 📊 Visit / Analytics Page *(Admin only)*
A dashboard visible only to administrators. Displays key statistics: total registered users, number of faculty, number of students, total announcements posted, total memos, total uploaded documents, and both total and today's visit counts. Also includes a full registered user table and a recent visit activity log showing who accessed the portal, from what IP address, and at what time.

---

## 🌐 Guest Public Page

The system includes a **public-facing guest page** that is fully accessible without logging in. This page is intended for walk-in visitors, prospective students, parents, or anyone who needs to view institutional content without creating an account.

### Purpose

The guest page removes the barrier of login for publicly relevant content. Instead of asking every visitor to register, the institution can openly publish announcements, memoranda, and downloadable files, while keeping sensitive or role-specific content behind authentication.

### How to Access

Navigate directly to:
```
http://localhost:8000/index.php?page=guest
```
Or click the **"View as Guest"** link found at the bottom of the login card on the login page.

The guest page uses a simplified version of the main navbar with a prominent **Login** button in the top right corner so visitors can sign in at any time. No session or account is required.

---

### What Guests Can See

The guest page is a single scrollable page divided into three clearly labeled sections:

---

#### Section 1 — Announcements

Displays all announcements where the audience is set to **"All"** in a responsive card grid. Each card shows:
- The announcement title
- The name of the faculty or admin who posted it
- The date it was posted
- An expand button to read the full announcement body

Announcements that are restricted to **Faculty Only** or **Students Only** are automatically hidden from the guest view and will not appear on this page.

---

#### Section 2 — Memorandum

Displays a **read-only version** of the memo table. All memoranda are publicly visible regardless of type. Each row in the table shows:
- Memo Number
- Date Issued
- Subject
- Category
- Type badge (Internal or External)
- A download or link button if a file or URL was attached

Guests cannot add, edit, or delete any memorandum. There is no action column or modal form available on the guest page.

---

#### Section 3 — Documents / Files

Displays all uploaded documents where the audience is set to **"All"** in a card grid layout. Each card shows:
- A file type icon indicating the format (PDF, Word, Excel, PowerPoint, Image, etc.)
- The document title
- The category label
- The name of the uploader and the upload date
- A **Download** button for direct file access
- A **Drive** button if a Google Drive link was attached

Documents restricted to **Faculty Only** or **Students Only** are hidden from the guest view. Only publicly shared files are shown.

---

### What Guests Cannot Do

- Post, edit, or delete announcements
- Add or remove memoranda
- Upload documents
- Access faculty-only or student-only content
- View the Visit Analytics dashboard
- Access any user account information

---

## 📁 Project Structure

```
rs/
├── config/
│   ├── database.php                    # PDO database connection
│   ├── google_config.php               # Google Auth settings
│   └── migrate.php                     # Migration script
├── Controller/                         # All application controllers
│   ├── AnnouncementController.php
│   ├── AuthController.php
│   ├── HomeController.php
│   ├── MemoController.php
│   ├── UploadController.php
│   ├── UserController.php              # Accounts management
│   └── VisitController.php
├── css/                                # Stylesheets
│   ├── app.css
│   └── style.css
├── icon/                               # Application icons and backgrounds
│   ├── backbird.png
│   └── background.png
├── js/                                 # Scripts
│   ├── app.js
│   └── main.js
├── Models/                             # Database models
│   ├── Announcement.php
│   ├── Document.php
│   ├── Memo.php
│   └── User.php
├── public/
│   └── uploads/                        # Automatically created
│       ├── documents/
│       ├── images/
│       └── memos/
├── views/                              # Presentation layers
│   ├── announcements/
│   ├── auth/
│   ├── home/
│   ├── layouts/
│   ├── memos/
│   ├── uploads/
│   ├── users/
│   └── visits/
├── .env                                # Local environment variables
├── .gitattributes
├── .htaccess                           # Apache mod_rewrite rules
├── database.sql                        # Original DB schema
├── index.php                           # Main application router
└── README.md
```

---

## 🔧 Tech Stack

| Layer | Technology |
|-------|-----------|
| Architecture | Custom PHP MVC (no external framework) |
| Backend Language | PHP 8.1+ |
| Database | MySQL 5.7+ or MariaDB 10.4+ |
| Authentication | PHP native sessions with role-based access control |
| Frontend | PHP template views, custom CSS with CSS variables |
| File Storage | Local server filesystem with optional Google Drive link |
| Web Server | Apache with `mod_rewrite` enabled, or PHP built-in server |
| Recommended IDE | Windsurf or VS Code |

---

## ⚙️ Setup & Installation Guide

### ⚠️ IMPORTANT: Not a Laravel Project
This application is built with a **Custom PHP MVC Architecture**. Since it does not use the Laravel framework:
- **DO NOT** use `php artisan` commands.
- **DO NOT** try to run `php artisan serve`.
- Follow the specific server instructions below.

---

### Step 1 — System Requirements
Ensure your environment meets these minimums:
- **PHP:** 8.1 or higher (with `pdo_mysql`, `curl`, and `openssl` extensions)
- **Database:** MySQL 5.7+ or MariaDB 10.4+
- **Composer:** Required for dependency management.
- **Web Server:** Apache (with `mod_rewrite`) or Nginx.

---

### Step 2 — Project Deployment
1. **Clone or Copy:** Place the project folder into your web root.
   - **Laragon:** `C:\laragon\www\rs`
   - **XAMPP:** `C:\xampp\htdocs\rs`
2. **Install Dependencies:** Open your terminal in the `rs/` folder and run:
   ```bash
   composer install
   ```

---

### Step 3 — Database Configuration
1. **Create Database:** Open phpMyAdmin (or your preferred SQL tool) and create a new database named `acadportal`.
2. **Import Schema:** Import the `database.sql` file found in the root directory.
3. **Update Config:** Open `config/database.php` and set your credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'acadportal');
   define('DB_USER', 'root');
   define('DB_PASS', 'your_password');
   ```

---

### Step 4 — Starting the Application

#### Option A: Using Laragon (Recommended)
1. Place the folder in `C:\laragon\www\rs`.
2. Start Laragon. It will automatically detect the folder and generate a Virtual Host.
3. Visit: **`http://rs.test`**

#### Option B: Using PHP Built-in Server
1. Open your terminal in the `rs/` folder.
2. Run the following command:
   ```bash
   php -S localhost:8000
   ```
3. Visit: **`http://localhost:8000`**

#### Option C: Using XAMPP / Apache
1. Place the folder in `C:\xampp\htdocs\rs`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Visit: **`http://localhost/rs/`**

---

### Step 5 — Google Drive Setup (Optional)
If you wish to enable the Google Drive Mirror Sync feature (Real-time Cloud Sync):
1. Rename `.env.example` to `.env` (if provided) and fill in your Google API credentials.
2. Refer to [GOOGLE_DRIVE_SETUP.md](file:///d:/App/laragon/www/rs/GOOGLE_DRIVE_SETUP.md) for a full walkthrough on creating your Client ID and Client Secret.

---

## 🔑 Default Login Credentials

| Field    | Value                   |
|----------|-------------------------|
| Email    | `admin@usep.edu.ph`     |
| Password | `password`              |
| Role     | Admin                   |

> ⚠️ Change the default admin password immediately after your first login in a production environment.

> 🔒 **Domain Restriction:** Only `@usep.edu.ph` email addresses are accepted for login and account creation. Attempts to use any other domain will be rejected by the server.

---

## 👥 User Roles & Permissions

| Permission                        | Guest | Student | Faculty | Admin |
|-----------------------------------|:-----:|:-------:|:-------:|:-----:|
| View public announcements         |   ✅   |    ✅    |    ✅    |   ✅   |
| View all memoranda                |   ✅   |    ✅    |    ✅    |   ✅   |
| Download public documents         |   ✅   |    ✅    |    ✅    |   ✅   |
| View faculty/student-only content |   ❌   |    ✅    |    ✅    |   ✅   |
| Post announcements                |   ❌   |    ❌    |    ✅    |   ✅   |
| Add memoranda                     |   ❌   |    ❌    |    ✅    |   ✅   |
| Upload documents                  |   ❌   |    ❌    |    ✅    |   ✅   |
| Delete any content                |   ❌   |    ❌    |    ❌    |   ✅   |
| View analytics and visit logs     |   ❌   |    ❌    |    ❌    |   ✅   |
| Manage user accounts              |   ❌   |    ❌    |    ❌    |   ✅   |

---

## 🗂️ File Upload Notes

- All uploaded files are stored under `public/uploads/` organized into subfolders by type
- Accepted file types include: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, GIF, and ZIP
- File names are randomized using `uniqid()` on upload to prevent naming conflicts and overwriting
- The default PHP file size limit is 2MB. To allow larger uploads, edit your `php.ini` file:

```ini
upload_max_filesize = 50M
post_max_size = 50M
```

- Alternatively, you can attach a Google Drive link to any document upload as a supplement or replacement for a local file

---

## 🔒 Security Notes

- All passwords are hashed with PHP's `password_hash()` function using the bcrypt algorithm
- All output rendered to the browser is escaped with `htmlspecialchars()` to prevent XSS attacks
- All database interactions use PDO prepared statements — no raw SQL string interpolation with user data
- Role-based access is checked inside every protected controller method before any action is performed
- File uploads are stored with randomized names and are separated from application logic files
- Sessions are entirely server-side; no sensitive user data is stored in cookies or local storage

---

## 🔧 Adding Users

> 🔒 All accounts must use `@usep.edu.ph` email addresses. Other domains are rejected at both the UI and server level.

The easiest way to create accounts is through the **Accounts** page in the admin panel (sidebar → Accounts → Add Account).

To manually create faculty or student accounts, insert directly into the `users` table:

```sql
INSERT INTO users (name, email, password, role) VALUES
('Maria Santos', 'maria@usep.edu.ph', '$2y$12$HASHED_PASSWORD_HERE', 'faculty'),
('Pedro Reyes',  'pedro@usep.edu.ph', '$2y$12$HASHED_PASSWORD_HERE', 'student');
```

To generate a valid bcrypt hash for any password, create a temporary file in `public/` called `hash.php`:

```php
<?php echo password_hash('yourpassword', PASSWORD_BCRYPT); ?>
```

Open it in your browser, copy the output hash, paste it into your SQL insert, then delete `hash.php`.

---

## 🎨 Customization

### Change the School Name and Logo

Edit `app/Views/layouts/main.php` and update the `.nav-title` text and the `<img>` logo element. Replace `public/css/logo-placeholder.svg` with your institution's actual logo file (PNG or SVG recommended).

### Change the Brand Colors

Open `public/css/app.css` and update the CSS custom properties at the very top of the file:

```css
:root {
    --crimson:      #8B0000;   /* Main brand color — navbar, buttons, accents */
    --crimson-dark: #6B0000;   /* Darker shade for hover and active states */
    --crimson-light:#C41230;   /* Lighter accent for gradients */
}
```

### Change the Hero Background Image

In `public/css/app.css`, find `.hero-section` and replace the Unsplash URL with your own campus photo:

```css
.hero-section {
    background: url('../uploads/your-campus-photo.jpg') center/cover no-repeat;
}
```

---

## 📸 Design Reference

The UI is based directly on the provided design screenshots:

| Page                      | Design Description                                                                                                              |
|---------------------------|---------------------------------------------------------------------------------------------------------------------------------|
| **Login**                 | Floating white card anchored to the left, full-screen campus photo background, crimson gradient navbar across the top           |
| **Announcements**         | Full-width hero banner with dark overlay and large uppercase title, scrollable card grid below with expand/collapse             |
| **Memorandum**            | Clean white sortable data table with crimson header row, live search bar, and per-page row selector                             |
| **Upload / Requirements** | Card grid with file type emoji icons, audience badge pills, and download/drive link buttons                                     |
| **Guest Page**            | Same three-section layout as above pages but without login — announcements, memos, and documents stacked vertically on one page |
| **Visit / Analytics**     | Row of colored stat cards followed by a full user table and a recent activity log table                                         |

---

## 📄 License

This project was developed for academic and institutional use. You are free to modify, extend, and adapt it for your school or university's specific needs.
