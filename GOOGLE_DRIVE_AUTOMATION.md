# ⚡ Google Drive Automation (The "Easy Path")

This script will watch your Gmail for emails from the app and automatically save and sort the files into your Drive folder. No API keys or Service Accounts required!

### 1. Open Google Apps Script
1. Go to [script.google.com](https://script.google.com/).
2. Click **+ New Project**.

### 2. Paste the Script
Delete everything in the editor and paste this code:

```javascript
/**
 * DRIVE AUTOMATION SCRIPT
 * Watches for [RS-SORT] emails and moves attachments to Drive
 */
function autoSortFiles() {
  // CONFIGURATION
  var PARENT_FOLDER_ID = "1RCXsjU8rmx_fGFqTjRmwp8uOFV14s2b4"; 
  
  // Search for unprocessed emails with the tag
  var threads = GmailApp.search("subject:[RS-SORT] is:unread");
  var parentFolder = DriveApp.getFolderById(PARENT_FOLDER_ID);
  
  for (var i = 0; i < threads.length; i++) {
    var messages = threads[i].getMessages();
    
    for (var j = 0; j < messages.length; j++) {
      var msg = messages[j];
      var subject = msg.getSubject();
      
      // Extract category from subject: [RS-SORT] Category - Title
      var parts = subject.match(/\[RS-SORT\] (.*?) -/);
      var category = parts ? parts[1].trim() : "Other";
      
      var attachments = msg.getAttachments();
      
      if (attachments.length > 0) {
        // Find or create subfolder
        var subfolders = parentFolder.getFoldersByName(category);
        var subfolder = subfolders.hasNext() ? subfolders.next() : parentFolder.createFolder(category);
        
        for (var k = 0; k < attachments.length; k++) {
          subfolder.createFile(attachments[k]);
        }
        
        // Mark as read so we don't process it again
        threads[i].markRead();
      }
    }
  }
}
```

### 3. Save & Give Permission
1. Click the 💾 **Save** icon and name it `RS Auto Sorter`.
2. Click the ▶️ **Run** button at the top.
3. A popup will appear asking for permissions. Click **Review Permissions**, select your account, and click **Allow**. (If you see "Google hasn't verified this app," click *Advanced* > *Go to RS Auto Sorter*).

### 4. Make it Automatic (The Trigger)
1. In the Apps Script sidebar, click the **Triggers** icon (⏰ clock).
2. Click **+ Add Trigger** (bottom right).
3. Set:
   - Choose which function to run: `autoSortFiles`
   - Select event source: `Time-driven`
   - Select type of time based trigger: `Minutes timer`
   - Select minute interval: `Every minute` (or every 5 minutes).
4. Click **Save**.

---
### 🎉 Done!
Your Drive will now scan for uploads every minute and sort them into the correct folders automatically.
