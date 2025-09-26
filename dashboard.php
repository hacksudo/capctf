<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);
session_start();

$dbfile = __DIR__ . '/vulnlab.db';
$db = new PDO('sqlite:' . $dbfile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Validate session via cookie
$sid = $_COOKIE['LABSESSID'] ?? '';
if (!$sid) {
    header('Location: index.php');
    exit;
}
$stmt = $db->prepare("SELECT username FROM sessions WHERE sid = :sid");
$stmt->execute([':sid' => $sid]);
$sess = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$sess) {
    header('Location: index.php');
    exit;
}
$username = $sess['username'];

// Show attempts table entries
$ips = $db->query("SELECT * FROM attempts ORDER BY ip")->fetchAll(PDO::FETCH_ASSOC);

// Vulnerable IP lookup
$lookup_output = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ip'])) {
    $ip_input = $_POST['ip'];
    // INTENTIONAL vulnerability: unsanitized shell exec
    $lookup_output = shell_exec("ping -c 2 " . $ip_input . " 2>&1");
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard</title>
<style>
body{ background:#0b0b0b; color:#dcdcdc; font-family:monospace; padding:20px;}
.card{ background:#111; padding:20px; border-radius:8px; max-width:1000px; margin:10px auto;}
table{ width:100%; border-collapse:collapse; margin-top:12px;}
th,td{ border:1px solid #333; padding:8px; text-align:center;}
pre{ background:#0f0f0f; padding:12px; border-radius:6px; overflow:auto;}
.flag{ color:yellow; font-weight:bold; margin-bottom:10px;}
form.inline{ display:flex; gap:8px; align-items:center;}
input,button{ padding:8px; border-radius:4px; border:1px solid #333; background:#0f0f0f; color:#eee;}
button{ background:#2e8b57; border:none; cursor:pointer;}
</style>
</head>
<body>
<div class="card">
  <h1>Admin Control Panel</h1>
  <p>Welcome, <strong><?=htmlspecialchars($username)?></strong></p>

  <?php if ($username === 'admin'): ?>
    <div class="flag">FLAG{RateLimit&CaptchaBypass}</div>
  <?php endif; ?>

  <h3>IP Lookup (hacksudo.com)</h3>
  <form method="POST" class="inline">
    <input type="text" name="ip" placeholder="Enter IP or host (e.g., 8.8.8.8)">
    <button type="submit">Lookup</button>
  </form>

  <?php if ($lookup_output !== ''): ?>
    <h4>Lookup Output</h4>
    <pre><?= htmlspecialchars($lookup_output) ?></pre>
  <?php endif; ?>

  <h3>Login Attempts</h3>
  <table>
    <tr><th>IP</th><th>Fail Count</th><th>Blocked</th></tr>
    <?php foreach ($ips as $r): ?>
      <tr>
        <td><?=htmlspecialchars($r['ip'])?></td>
        <td><?=htmlspecialchars($r['fail_count'])?></td>
        <td><?= $r['blocked'] ? 'Yes' : 'No' ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p style="margin-top:12px;"><a href="index.php" style="color:#4caf50">Logout / Back to Login</a></p>
</div>
</body>
</html>
