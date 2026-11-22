<?php
/**
 * Sentinel Vault Terminal - Mainframe Access Console
 * Login form interface and authentication failure re-render.
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/layout.php';

$error    = $error    ?? null;
$username = $username ?? '';

page_start($error ? 'ACCESS DENIED' : 'TERMINAL AUTH', $error ? 'failure' : 'idle');
?>
<section class="terminal-banner-box" aria-labelledby="banner-heading">
  <div class="banner-meta">
    <span>MAINFRAME SECURITY CONSOLE</span>
    <span>CLEARANCE LEVEL 4+ REQUIRED</span>
  </div>
  <h1 id="banner-heading" class="terminal-heading">SENTINEL ACCESS CONTROL v4.1 <span class="cursor-block" aria-hidden="true"></span></h1>
  <p class="terminal-subheading">Enter registered operator credentials to unlock mainframe privileges.</p>

  <div class="terminal-sysdiag" aria-label="System Diagnostics">
    <div class="sysdiag-row">
      <span>&gt; SUBSYSTEM SEC_CORE</span>
      <span class="diag-ok">[READY]</span>
    </div>
    <div class="sysdiag-row">
      <span>&gt; SQLITE DATASTORE (data/users.db)</span>
      <span class="diag-ok">[MOUNTED]</span>
    </div>
    <div class="sysdiag-row">
      <span>&gt; CRYPTO ALGORITHM (BCRYPT / NOCASE)</span>
      <span class="diag-ok">[ARMED]</span>
    </div>
  </div>
</section>

<?php if ($error): ?>
<div class="terminal-alert terminal-alert-denied" role="alert" id="login-error">
  <div class="alert-header">
    <span class="alert-badge">[!] ACCESS DENIED</span>
    <span class="alert-code">HTTP <?= http_response_code() ?> // SECURITY_AUTH_FAIL</span>
  </div>
  <p class="alert-msg"><?= e($error) ?></p>
</div>
<?php endif; ?>

<form class="terminal-form" action="login.php" method="post" novalidate data-login-form>
  <p class="form-instruction">&gt;&gt; INITIATE OPERATOR CREDENTIAL VERIFICATION SEQUENCE:</p>

  <div class="terminal-field">
    <label for="username" class="terminal-prompt">LOGIN NAME&gt;</label>
    <div class="terminal-input-wrap">
      <span class="terminal-chevron" aria-hidden="true">&gt;&gt;</span>
      <input id="username" name="username" type="text" class="terminal-input"
             autocomplete="username" autocapitalize="none" spellcheck="false"
             value="<?= e($username) ?>" required maxlength="64"
             placeholder="ENTER LOGIN NAME"
             <?= $error ? 'aria-describedby="login-error" aria-invalid="true"' : '' ?>
             <?= $username === '' ? 'autofocus' : '' ?>>
    </div>
    <p class="field-hint" id="username-hint" hidden>[!] ERROR: Please enter your login name.</p>
  </div>

  <div class="terminal-field">
    <label for="password" class="terminal-prompt">PASSWORD&gt;</label>
    <div class="terminal-input-wrap">
      <span class="terminal-chevron" aria-hidden="true">&gt;&gt;</span>
      <input id="password" name="password" type="password" class="terminal-input"
             autocomplete="current-password"
             required maxlength="128"
             placeholder="ENTER PASSKEY"
             <?= $error ? 'aria-describedby="login-error" aria-invalid="true"' : '' ?>
             <?= $username !== '' ? 'autofocus' : '' ?>>
      <button type="button" class="terminal-btn-inline" aria-label="Show password" aria-pressed="false" data-reveal>[SHOW]</button>
    </div>
    <p class="field-hint" id="password-hint" hidden>[!] ERROR: Please enter your password.</p>
    <p class="caps-warning" id="caps-hint" hidden>[WARN] CAPS LOCK IS ON: Passwords are case-sensitive.</p>
  </div>

  <div class="terminal-actions">
    <button type="submit" class="terminal-btn-submit" data-submit>
      <span class="btn-label">[ EXECUTE AUTHENTICATION ]</span>
      <span class="cursor-block" aria-hidden="true"></span>
    </button>
  </div>
</form>

<section class="terminal-directory" aria-labelledby="directory-title">
  <div class="directory-header" id="directory-title">
    <span>SYSTEM USER DIRECTORY // SEEDED TEST ACCOUNTS</span>
  </div>
  <div class="directory-table-wrap">
    <table class="directory-table">
      <thead>
        <tr>
          <th scope="col">LOGIN NAME</th>
          <th scope="col">PASSWORD</th>
          <th scope="col">OPERATOR NAME</th>
          <th scope="col">ROLE</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><code>admin</code></td>
          <td><code>Admin@123</code></td>
          <td>Asha Raman</td>
          <td>Administrator</td>
        </tr>
        <tr>
          <td><code>student</code></td>
          <td><code>Student@123</code></td>
          <td>Rahul Menon</td>
          <td>Student</td>
        </tr>
        <tr>
          <td><code>faculty</code></td>
          <td><code>Faculty@123</code></td>
          <td>Dr. Meera Iyer</td>
          <td>Faculty</td>
        </tr>
      </tbody>
    </table>
  </div>
  <p class="directory-note">NOTE: Login name lookup is COLLATE NOCASE (case-insensitive). Passwords verified via password_verify() against bcrypt hashes stored in SQLite datastore.</p>
</section>

<script>
(() => {
  const form = document.querySelector('[data-login-form]');
  if (!form) return;
  const pw = form.password;
  const user = form.username;

  // Toggle password visibility
  const reveal = form.querySelector('[data-reveal]');
  if (reveal && pw) {
    reveal.addEventListener('click', () => {
      const show = pw.type === 'password';
      pw.type = show ? 'text' : 'password';
      reveal.setAttribute('aria-pressed', show);
      reveal.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      reveal.textContent = show ? '[HIDE]' : '[SHOW]';
      pw.focus();
    });
  }

  // Caps Lock detection
  const caps = document.getElementById('caps-hint');
  if (caps && pw) {
    pw.addEventListener('keyup', ev => {
      caps.hidden = !ev.getModifierState?.('CapsLock');
    });
    pw.addEventListener('blur', () => {
      caps.hidden = true;
    });
  }

  // Pre-submit validation
  const checkField = input => {
    const hint = document.getElementById(input.id + '-hint');
    const empty = input.value.trim() === '';
    if (hint) hint.hidden = !empty;
    if (empty) {
      input.setAttribute('aria-invalid', 'true');
    } else if (!document.getElementById('login-error')) {
      input.removeAttribute('aria-invalid');
    }
    return !empty;
  };

  [user, pw].forEach(i => {
    if (!i) return;
    i.addEventListener('input', () => {
      const hint = document.getElementById(i.id + '-hint');
      if (hint && !hint.hidden) checkField(i);
    });
  });

  form.addEventListener('submit', ev => {
    const okUser = checkField(user);
    const okPw = checkField(pw);
    if (!okUser || !okPw) {
      ev.preventDefault();
      (okUser ? pw : user).focus();
      return;
    }
    const btn = form.querySelector('[data-submit]');
    if (btn) {
      const lbl = btn.querySelector('.btn-label');
      if (lbl) lbl.textContent = '[ VERIFYING HASH... ]';
      btn.style.pointerEvents = 'none';
      btn.style.opacity = '0.85';
    }
  });
})();
</script>
<?php page_end();
