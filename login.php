<?php
/**
 * Sentinel Vault Terminal - Authentication Controller
 * Handles POST credentials, validates against SQLite database,
 * and renders success dump or failure denial.
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/layout.php';

// Redirect GET requests back to terminal console login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = trim((string)($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');

// Empty fields validation (HTTP 400)
if ($username === '' || $password === '') {
    http_response_code(400);
    $error = 'Please enter both your login name and your password.';
    require __DIR__ . '/index.php';
    exit;
}

// Check credentials against the SQLite datastore
$user = validate_user($username, $password);

// Generic failure message for incorrect name or password (HTTP 401)
if ($user === null) {
    http_response_code(401);
    $error = 'The login name or password you entered does not match our records. Please try again.';
    require __DIR__ . '/index.php';
    exit;
}

/* ==========================================================================
   Successful Login: Render Operator Profile Record Dump
   ========================================================================== */
$fmt = fn(?string $dt) => $dt ? date('j M Y, g:i A', strtotime($dt)) : null;

page_start('ACCESS GRANTED', 'success');
?>
<div class="terminal-alert terminal-alert-success" role="status">
  <div class="alert-header">
    <span class="alert-badge">[***] ACCESS GRANTED</span>
    <span class="alert-code">HTTP 200 // OPERATOR CLEARANCE VERIFIED</span>
  </div>
  <p class="alert-msg">Identity confirmed against users database. Active terminal session granted for <?= e($user['full_name']) ?>.</p>
</div>

<section class="terminal-dump-container" aria-labelledby="dump-heading">
  <div class="dump-title-bar">
    <strong id="dump-heading">=== OPERATOR RECORD DUMP ===</strong>
    <span>RECORD_ID: #<?= (int)$user['id'] ?></span>
  </div>

  <dl class="dump-grid">
    <div class="dump-item">
      <dt class="dump-key">OPERATOR NAME&gt;</dt>
      <dd class="dump-value"><?= e($user['full_name']) ?></dd>
    </div>
    <div class="dump-item">
      <dt class="dump-key">LOGIN IDENTIFIER&gt;</dt>
      <dd class="dump-value"><?= e($user['username']) ?></dd>
    </div>
    <div class="dump-item">
      <dt class="dump-key">SECURITY ROLE&gt;</dt>
      <dd class="dump-value"><span class="role-badge"><?= e($user['role']) ?></span></dd>
    </div>
    <div class="dump-item">
      <dt class="dump-key">CONTACT EMAIL&gt;</dt>
      <dd class="dump-value"><?= e($user['email']) ?></dd>
    </div>
    <div class="dump-item">
      <dt class="dump-key">TOTAL SIGN-INS&gt;</dt>
      <dd class="dump-value"><?= (int)$user['login_count'] ?></dd>
    </div>
    <div class="dump-item">
      <dt class="dump-key">PREVIOUS SIGN-IN&gt;</dt>
      <dd class="dump-value"><?= e($fmt($user['last_login']) ?? 'First time — welcome!') ?></dd>
    </div>
    <div class="dump-item">
      <dt class="dump-key">MEMBER SINCE&gt;</dt>
      <dd class="dump-value"><?= e(date('j M Y', strtotime($user['created_at']))) ?></dd>
    </div>
    <div class="dump-item">
      <dt class="dump-key">SESSION STATUS&gt;</dt>
      <dd class="dump-value">AUTHENTICATED // LIVE_CONSOLE</dd>
    </div>
  </dl>

  <div class="dump-title-bar">
    <span>=== END OF RECORD DUMP ===</span>
    <span>CHECKSUM: VERIFIED</span>
  </div>
</section>

<div class="terminal-actions">
  <a class="terminal-btn-submit terminal-btn-signout" href="index.php">
    [ DISCONNECT / TERMINATE SESSION ]
  </a>
</div>

<?php page_end();
