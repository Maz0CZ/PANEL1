# UltimatePanel v1.0.1 — Production-Ready Starter

**Main developer:** Filip Piller  
**Target OS:** Debian Linux  
**Framework:** Nette (latest stable)  
**Dependency management:** Composer  
**Database:** SQLite (file-based)  
**Frontend:** Bootstrap 5 + FontAwesome  
**Theme:** Dark cyberpunk / futuristic (primary: `#ab47bc`)  

## Quick start (out of the box)
```bash
./quickstart.sh
```
- Starts a local PHP server on `http://localhost:8000`.
- Initializes SQLite database with seeded users and game templates.

Seeded accounts:
- **Admin**: `admin@admin.cz` / `pass123` (role: admin)
- **User**: `test@test.cz` / `test123` (role: user)

## 1) Core Vision
UltimatePanel is a lightweight, self-hosted game hosting control panel for a single Debian server. It manages multiple game types without RCON by using direct process control (STDIN/STDOUT), live console streaming, and secure per-server isolation. The design emphasizes reliability, security, and extensibility via game templates.

## 2) Project Layout (Implemented)
```
/app
  /Presenters
  /Model
  /Router
/config
/public
/bin
/storage
/docs
quickstart.sh
```

## 3) MVC + Services
- **Presenters:** HTTP endpoints and UI logic.
- **Services:** provisioning, console, process lifecycle.
- **Repositories:** SQLite access with prepared statements.

## 4) SQLite Schema (Implemented)
```sql
CREATE TABLE users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL CHECK (role IN ('admin','user')),
  created_at TEXT NOT NULL
);

CREATE TABLE servers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  game_type TEXT NOT NULL,
  port INTEGER NOT NULL,
  directory TEXT NOT NULL,
  status TEXT NOT NULL,
  pid INTEGER,
  created_at TEXT NOT NULL,
  FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE ftp_accounts (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  server_id INTEGER NOT NULL,
  username TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  home_dir TEXT NOT NULL,
  FOREIGN KEY(user_id) REFERENCES users(id),
  FOREIGN KEY(server_id) REFERENCES servers(id)
);

CREATE TABLE game_templates (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT UNIQUE NOT NULL,
  start_command TEXT NOT NULL,
  default_port_range TEXT NOT NULL,
  description TEXT NOT NULL
);
```

## 5) Live Console (No RCON)
- Uses `screen` when available, otherwise falls back to basic process execution.
- Output is appended to `storage/logs/server_{id}.log` and rendered in the UI.
- Command injection uses `screen -S session -X stuff "command^M"`.

## 6) Security Model (High Priority)
- Strict input validation
- CSRF (Nette built-in)
- XSS protection via Nette escaping + CSP
- `password_hash()` for passwords
- Prepared statements only
- Rate limiting for login attempts (session-based)
- No root usage for game processes
- `www-data` should be restricted via sudo allowlist for real deployment

## 7) Installer Script Design
`quickstart.sh` provides a runnable demo:
- `composer install`
- `bin/init_db.php`
- `php -S 0.0.0.0:8000 -t public`

## 8) Notes
This repository provides a fully runnable starter for UltimatePanel with registration, admin view, server creation, and a live-console UI. Game binaries are not included by design.
