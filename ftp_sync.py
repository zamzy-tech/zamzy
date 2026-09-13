import os
import sys
import json
import time
import ftplib
from pathlib import Path

CONFIG_FILE = ".ftp_config.json"

def load_config():
    if os.path.exists(CONFIG_FILE):
        with open(CONFIG_FILE, "r", encoding="utf-8") as f:
            data = json.load(f)
            return {
                "FTP_HOST": data.get("host", "zamzy.in"),
                "FTP_PORT": int(data.get("port", 21)),
                "FTP_USER": data.get("user", "zamzy@zamzy.in"),
                "FTP_PASSWORD": data.get("password", ""),
                "FTP_REMOTE_DIR": data.get("remote_dir", "/").rstrip("/")
            }
    # Fallback to .env if needed
    config = {
        "FTP_HOST": "zamzy.in",
        "FTP_PORT": 21,
        "FTP_USER": "zamzy@zamzy.in",
        "FTP_PASSWORD": "",
        "FTP_REMOTE_DIR": "/"
    }
    if os.path.exists(".env"):
        with open(".env", "r", encoding="utf-8") as f:
            for line in f:
                line = line.strip()
                if line and not line.startswith("#") and "=" in line:
                    k, v = line.split("=", 1)
                    if k.strip() in config:
                        config[k.strip()] = int(v.strip()) if k.strip() == "FTP_PORT" else v.strip()
    return config

CONFIG = load_config()
FTP_HOST = CONFIG["FTP_HOST"]
FTP_PORT = CONFIG["FTP_PORT"]
FTP_USER = CONFIG["FTP_USER"]
FTP_PASSWORD = CONFIG["FTP_PASSWORD"]
FTP_REMOTE_DIR = CONFIG["FTP_REMOTE_DIR"]

IGNORE_NAMES = {
    ".git",
    ".vscode",
    ".ftp_config.json",
    ".ftp.env",
    "ftp_sync.py",
    "sync.bat",
    "__pycache__",
    "node_modules",
    ".DS_Store",
    "Thumbs.db"
}

def is_ignored(path_str):
    parts = Path(path_str).parts
    return any(p in IGNORE_NAMES for p in parts)

def get_ftp_connection():
    ftp = ftplib.FTP()
    ftp.connect(FTP_HOST, FTP_PORT, timeout=20)
    ftp.login(FTP_USER, FTP_PASSWORD)
    if FTP_REMOTE_DIR and FTP_REMOTE_DIR != "/":
        ftp.cwd(FTP_REMOTE_DIR)
    return ftp

def ensure_remote_dir(ftp, remote_dir_path):
    parts = remote_dir_path.strip("/").split("/")
    current = "/"
    for part in parts:
        if not part:
            continue
        target = f"{current}/{part}".replace("//", "/")
        try:
            ftp.cwd(target)
        except ftplib.error_perm:
            ftp.mkd(target)
            ftp.cwd(target)
        current = target

def cmd_test():
    print(f"Connecting to FTP server: {FTP_HOST}:{FTP_PORT} as {FTP_USER}...")
    try:
        ftp = get_ftp_connection()
        print("Connected successfully!")
        print("Remote Current Directory:", ftp.pwd())
        print("\nRemote Directory Listing:")
        ftp.retrlines("LIST")
        ftp.quit()
        print("\nFTP Connection Test PASSED!")
    except Exception as e:
        print(f"\nConnection FAILED: {e}")
        sys.exit(1)

def upload_file(ftp, local_path, remote_path):
    remote_dir = os.path.dirname(remote_path).replace("\\", "/")
    if remote_dir:
        ensure_remote_dir(ftp, remote_dir)
        ftp.cwd(remote_dir)
    else:
        ftp.cwd("/")

    filename = os.path.basename(remote_path)
    with open(local_path, "rb") as f:
        ftp.storbinary(f"STOR {filename}", f)
    print(f"  [UPLOADED] {local_path} -> {remote_path}")

def cmd_push():
    print("Connecting to FTP for push...")
    ftp = get_ftp_connection()
    root_dir = Path(".")
    count = 0

    for file_path in root_dir.rglob("*"):
        if file_path.is_file():
            rel_path = str(file_path.relative_to(root_dir))
            if is_ignored(rel_path):
                continue
            remote_path = "/" + rel_path.replace("\\", "/")
            try:
                upload_file(ftp, str(file_path), remote_path)
                count += 1
            except Exception as e:
                print(f"  [ERROR] Failed to upload {rel_path}: {e}")

    ftp.quit()
    print(f"\nPush complete! Uploaded {count} files.")

def cmd_pull():
    print("Connecting to FTP for pull...")
    ftp = get_ftp_connection()
    files = []

    def parse_dir(path):
        ftp.cwd(path)
        items = []
        ftp.dir(items.append)
        for item in items:
            parts = item.split(None, 8)
            if len(parts) < 9:
                continue
            name = parts[8]
            if name in (".", "..", ".ftpquota"):
                continue
            is_dir = parts[0].startswith("d")
            remote_item_path = (path.rstrip("/") + "/" + name)
            if is_dir:
                parse_dir(remote_item_path)
            else:
                files.append(remote_item_path)

    parse_dir("/")
    print(f"Found {len(files)} files on remote server. Downloading...")

    downloaded = 0
    for remote_path in files:
        rel_path = remote_path.lstrip("/")
        if is_ignored(rel_path):
            continue
        local_path = Path(rel_path)
        local_path.parent.mkdir(parents=True, exist_ok=True)
        try:
            with open(local_path, "wb") as f:
                ftp.retrbinary(f"RETR {remote_path}", f.write)
            print(f"  [DOWNLOADED] {remote_path} -> {local_path}")
            downloaded += 1
        except Exception as e:
            print(f"  [ERROR] Downloading {remote_path}: {e}")

    ftp.quit()
    print(f"\nPull complete! Downloaded {downloaded} files.")

def cmd_watch():
    print("Starting FTP File Watcher...")
    print("Any saved changes will be automatically uploaded to FTP.")
    print("Press Ctrl+C to stop.\n")

    file_mtimes = {}
    root_dir = Path(".")

    for file_path in root_dir.rglob("*"):
        if file_path.is_file() and not is_ignored(str(file_path.relative_to(root_dir))):
            try:
                file_mtimes[str(file_path)] = file_path.stat().st_mtime
            except OSError:
                pass

    print(f"Tracking {len(file_mtimes)} initial files. Watching for changes...\n")

    ftp = None
    while True:
        try:
            time.sleep(1.5)
            current_files = {}
            for file_path in root_dir.rglob("*"):
                if file_path.is_file():
                    rel_path = str(file_path.relative_to(root_dir))
                    if not is_ignored(rel_path):
                        try:
                            current_files[str(file_path)] = file_path.stat().st_mtime
                        except OSError:
                            pass

            changes = []
            for path_str, mtime in current_files.items():
                if path_str not in file_mtimes or mtime > file_mtimes[path_str]:
                    changes.append(path_str)
                    file_mtimes[path_str] = mtime

            if changes:
                try:
                    if not ftp:
                        ftp = get_ftp_connection()
                    for chg in changes:
                        rel = str(Path(chg).relative_to(root_dir))
                        remote = "/" + rel.replace("\\", "/")
                        upload_file(ftp, chg, remote)
                except Exception as ex:
                    print(f"  [ERROR] Upload failed: {ex}. Reconnecting next cycle...")
                    ftp = None

        except KeyboardInterrupt:
            print("\nWatcher stopped.")
            if ftp:
                try:
                    ftp.quit()
                except:
                    pass
            break

def main():
    action = sys.argv[1] if len(sys.argv) > 1 else "test"
    if action == "test":
        cmd_test()
    elif action == "push":
        cmd_push()
    elif action == "pull":
        cmd_pull()
    elif action == "watch":
        cmd_watch()
    else:
        print(f"Unknown action: {action}")
        print("Usage: python ftp_sync.py [test | push | pull | watch]")

if __name__ == "__main__":
    main()
