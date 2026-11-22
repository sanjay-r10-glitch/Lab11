# Lab 11 — Vault Terminal

A user validation web app styled as a retro mainframe access-control console.
The user submits a login name and password to the server. The server verifies them against the SQLite database; if they match, an **ACCESS GRANTED** profile record dump is returned, otherwise an **ACCESS DENIED** failure message is displayed.

**Stack:** PHP 8 (built-in server) + SQLite via PDO. No external libraries, no Composer.
**Theme:** "Vault Terminal" — phosphor green `#7CFC9A` on near-black `#0B0F0C` with amber `#FFB000` alerts, monospace typography, scanlines, and 1px borders.

---

## Run

```powershell
.\start.ps1
```

Then open <http://localhost:8113> in your browser.

*(Equivalent manual command: `php -d extension=pdo_sqlite -S localhost:8113` — on the winget PHP build pass `-d extension_dir=<php folder>\ext`, which `start.ps1` handles automatically).*

---

## Test Accounts

The SQLite database `data/users.db` is automatically created and seeded on first run:

| Login name | Password      | Role          | Full Name       |
|------------|---------------|---------------|-----------------|
| `admin`    | `Admin@123`   | Administrator | Asha Raman      |
| `student`  | `Student@123` | Student       | Rahul Menon     |
| `faculty`  | `Faculty@123` | Faculty       | Dr. Meera Iyer  |

- **Login names** are matched case-insensitively via `COLLATE NOCASE`.
- **Passwords** are case-sensitive and verified via `password_verify()`.
- To reset the datastore to defaults, delete the `data/` folder.

---

## File Structure

| File | Description |
|------|-------------|
| `index.php` | Terminal login session form and authentication failure re-render |
| `login.php` | Authentication controller: validates credentials, updates metrics, renders profile dump |
| `db.php` | PDO SQLite layer: creates `data/users.db`, seeds initial operators, validates hashes |
| `layout.php` | Mainframe page shell: system telemetry header, pipeline monitor, scanlines, footer |
| `assets/style.css` | Retro CRT phosphor green terminal styles, responsive layout, reduced-motion rules |
| `start.ps1` | PowerShell launch script configured for port 8113 |
| `.gitignore` | Excludes `data/` directory from version control |
| `README.md` | Lab documentation, setup instructions, and validation explanation |

---

## How Validation Works

1. **Submission:** The user submits `username` and `password` via POST from `index.php` to `login.php`.
2. **Input Check:** `login.php` checks if either field is blank; if so, responds with HTTP 400 and re-renders the terminal with a required-field error.
3. **Database Lookup:** `validate_user()` executes a **prepared statement** (`SELECT * FROM users WHERE username = ?`) against `data/users.db`.
4. **Hash Verification:** The entered password is cryptographic checked using `password_verify()` against the stored bcrypt hash.
5. **Success:**
   - Increments `login_count` and updates `last_login` timestamp in the database.
   - Returns the **ACCESS GRANTED** profile record dump displaying full name, role, email, username, total sign-in count, previous sign-in timestamp (or first-time welcome), and registration date.
6. **Failure:**
   - Responds with HTTP 401 and re-renders the login terminal with a generic **ACCESS DENIED** notice.
   - Never reveals whether the username or password was the incorrect field to prevent user enumeration attacks.
