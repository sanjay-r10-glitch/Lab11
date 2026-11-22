<?php
/**
 * Sentinel Vault Terminal - Layout Component
 * Renders the terminal frame, system telemetry, CRT scanlines, and footer.
 *
 * $state parameter: 'idle' | 'success' | 'failure'
 */

function page_start(string $title, string $state = 'idle'): void
{
    $stateLabels = [
        'idle'    => ['STATUS' => 'STANDBY',       'BADGE' => '[READY / STANDBY]'],
        'success' => ['STATUS' => 'AUTHENTICATED', 'BADGE' => '[ACCESS GRANTED]'],
        'failure' => ['STATUS' => 'ACCESS DENIED', 'BADGE' => '[ACCESS DENIED]'],
    ];
    $st = $stateLabels[$state] ?? $stateLabels['idle'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> // SENTINEL VAULT TERMINAL</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' fill='%230B0F0C'/><rect x='2' y='2' width='28' height='28' fill='none' stroke='%237CFC9A' stroke-width='2'/><text x='5' y='22' fill='%237CFC9A' font-family='monospace' font-weight='bold' font-size='16'>&gt;_</text></svg>">
</head>
<body class="state-<?= e($state) ?>">
<div class="scanlines" aria-hidden="true"></div>

<div class="terminal-shell">
  <div class="terminal-container">
    <!-- Mainframe Telemetry Bar -->
    <header class="terminal-topbar" aria-label="System status telemetry">
      <div class="topbar-left">
        <span class="system-id">SENTINEL-OS v4.1</span>
        <span class="node-id">NODE: 0x7F-SEC</span>
        <span class="port-id">PORT: 8113</span>
      </div>
      <div class="topbar-right">
        <span class="sec-status">SYSTEM_STATUS: <span class="status-indicator <?= e($state) ?>"><?= e($st['STATUS']) ?></span></span>
      </div>
    </header>

    <!-- Request Flow Sequence / Pipeline -->
    <nav class="terminal-pipeline" aria-label="Verification Pipeline">
      <div class="pipeline-step step-active">
        <span class="step-num">[01]</span> POST_REQUEST
      </div>
      <span class="pipeline-arrow" aria-hidden="true">-&gt;</span>
      <div class="pipeline-step step-active">
        <span class="step-num">[02]</span> SERVER_INPUT_CHECK
      </div>
      <span class="pipeline-arrow" aria-hidden="true">-&gt;</span>
      <div class="pipeline-step <?= in_array($state, ['success', 'failure']) ? 'step-active' : '' ?>">
        <span class="step-num">[03]</span> SQLITE_PREPARED_QUERY
      </div>
      <span class="pipeline-arrow" aria-hidden="true">-&gt;</span>
      <div class="pipeline-step <?= in_array($state, ['success', 'failure']) ? 'step-active' : '' ?>">
        <span class="step-num">[04]</span> BCRYPT_VERIFY
      </div>
      <span class="pipeline-arrow" aria-hidden="true">-&gt;</span>
      <div class="pipeline-step step-result <?= $state === 'success' ? 'step-success' : ($state === 'failure' ? 'step-failure' : '') ?>">
        <?= e($st['BADGE']) ?>
      </div>
    </nav>

    <!-- Interactive Terminal Screen -->
    <main class="terminal-main">
<?php
}

function page_end(): void
{
?>
    </main>

    <!-- Terminal System Footer -->
    <footer class="terminal-footer">
      <div class="footer-meta">
        <span>SECURITY: RESTRICTED</span>
        <span>CIPHER: BCRYPT (PASSWORDS PROTECTED)</span>
        <span>DATASTORE: SQLITE3</span>
      </div>
      <div class="footer-cmd">
        <span>CONSOLE_SESSION // ACTIVE</span>
      </div>
    </footer>
  </div>
</div>
</body>
</html>
<?php
}
