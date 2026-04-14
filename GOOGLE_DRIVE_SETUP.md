# 🚀 Google Drive API Setup Guide

Follow these steps to generate your `service-account.json` file and enable automated file sorting in your Repository.

### 1. Enable Drive API
1. Go to the [Google Cloud Console](https://console.cloud.google.com/).
2. Select your project (or create a new one).
3. Search for **"Google Drive API"** in the top search bar.
4. Click **Enable**.

### 2. Create Service Account
1. Go to **APIs & Services > Credentials**.
2. Click **+ CREATE CREDENTIALS** and select **Service Account**.
3. Name it `rs-drive-service` and click **Create and Continue**.
4. (Optional) Grant it the "Editor" role on the project.
5. Click **Done**.

### 3. Generate JSON Key
1. Find your new service account in the list under **Service Accounts**.
2. Click on the email address of that account.
3. Go to the **Keys** tab.
4. Click **ADD KEY > Create new key**.
5. Select **JSON** and click **Create**.
6. A `.json` file will download to your computer. **Keep this private!**

### 4. Configure the Application
1. **Rename** the downloaded file to `service-account.json`.
2. **Move** it to your project's `config/` folder:
   `d:\App\laragon\www\rs\config\service-account.json`
3. **Share your target Drive Folder** with the service account email (it looks like `rs-drive-service@...iam.gserviceaccount.com`).
   - Right-click your Drive folder → **Share** → Paste the service account email → Set as **Editor**.

---
### 🎉 You're Done!
Once the file is in place, the application will automatically switch from local storage to Google Drive. No restarts are required.
