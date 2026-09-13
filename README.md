# Zamzy FTP Integration & Deployment Setup

This workspace is now connected and configured to sync with the **Zamzy** remote FTP server.

---

## 1. Connection Details

- **Host**: `zamzy.in`
- **Port**: `21`
- **Username**: `zamzy@zamzy.in`
- **Remote FTP Root**: `/home/shacartc/zamzy.in/zamzy` (mapped to `/` in FTP)

---

## 2. Quick Commands

You can use either `sync` (via batch script) or `python ftp_sync.py`:

| Command | Action |
| :--- | :--- |
| `.\sync.bat test` or `python ftp_sync.py test` | Test FTP connectivity & list remote files |
| `.\sync.bat push` or `python ftp_sync.py push` | Upload all local project files to the FTP server |
| `.\sync.bat pull` or `python ftp_sync.py pull` | Download files from the FTP server to your local workspace |
| `.\sync.bat watch` or `python ftp_sync.py watch` | Watch local directory and auto-upload files whenever saved |

---

## 3. Automatic Upload on Save (VS Code)

A `.vscode/sftp.json` file is already created. If you use the popular **SFTP** extension in VS Code:
- Every time you save any file (Ctrl+S), it will automatically be uploaded to the FTP server.
- Credentials and sensitive files (`.env`, `.git`, `.vscode`, etc.) are ignored from uploads.

---

## 4. Important Note on cPanel Directory Mapping

During account creation in cPanel, the directory was configured as:
```
/home/shacartc/zamzy.in/zamzy
```

### What this means:
- Files uploaded to this FTP account are served at: **`https://zamzy.in/zamzy/`**
- If you want the files to appear directly on the main website **`https://zamzy.in/`**:
  1. Open **cPanel** > **FTP Accounts**.
  2. Locate `zamzy@zamzy.in`.
  3. Click **Configure** or update the Directory path from:
     `/home/shacartc/zamzy.in/zamzy`  
     to:
     `/home/shacartc/zamzy.in` (or your cPanel document root for zamzy.in).
  4. The local scripts and `.vscode/sftp.json` will continue to work without changes.
