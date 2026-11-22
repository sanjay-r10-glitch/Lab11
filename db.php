<?php
/**
 * Sentinel Vault Terminal - Database Layer
 * Opens and seeds the SQLite datastore (data/users.db).
 */

const DB_PATH = __DIR__ . '/data/users.db';

function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $fresh = !file_exists(DB_PATH);
    if (!is_dir(dirname(DB_PATH))) {
        mkdir(dirname(DB_PATH), 0777, true);
    }

    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            username      TEXT    NOT NULL UNIQUE COLLATE NOCASE,
            password_hash TEXT    NOT NULL,
            full_name     TEXT    NOT NULL,
            email         TEXT    NOT NULL,
            role          TEXT    NOT NULL,
            created_at    TEXT    NOT NULL DEFAULT (datetime(\'now\', \'localtime\')),
            last_login    TEXT,
            login_count   INTEGER NOT NULL DEFAULT 0
        )'
    );

    if ($fresh) {
        seed($pdo);
    }
    return $pdo;
}

/** Inserts the default test accounts when initializing the datastore. */
function seed(PDO $pdo): void
{
    $users = [
        ['admin',   'Admin@123',   'Asha Raman',    'asha.raman@example.edu',   'Administrator'],
        ['student', 'Student@123', 'Rahul Menon',   'rahul.menon@example.edu',  'Student'],
        ['faculty', 'Faculty@123', 'Dr. Meera Iyer', 'meera.iyer@example.edu',  'Faculty'],
    ];

    $stmt = $pdo->prepare(
        'INSERT INTO users (username, password_hash, full_name, email, role)
         VALUES (?, ?, ?, ?, ?)'
    );
    foreach ($users as [$u, $p, $name, $email, $role]) {
        $stmt->execute([$u, password_hash($p, PASSWORD_DEFAULT), $name, $email, $role]);
    }
}

/**
 * Validates credentials against the database.
 * Returns the matched user record (with previous last_login and incremented login_count)
 * or null on authentication failure.
 */
function validate_user(string $username, string $password): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return null;
    }

    // Record login timestamp and increment counter in DB;
    // return previous last_login to the caller.
    db()->prepare(
        'UPDATE users
            SET last_login = datetime(\'now\', \'localtime\'),
                login_count = login_count + 1
          WHERE id = ?'
    )->execute([$user['id']]);

    $user['login_count']++;
    return $user;
}

/** Escape string for HTML output */
function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
