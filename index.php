<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);
session_start();

$dbfile = __DIR__ . '/vulnlab.db';
$db = new PDO('sqlite:' . $dbfile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get client IP (vulnerable: trusts X-Forwarded-For)
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

// Fetch or create attempt record
$stmt = $db->prepare("SELECT * FROM attempts WHERE ip = :ip");
$stmt->execute([':ip' => $ip]);
$ipdata = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$ipdata) {
    $db->prepare("INSERT INTO attempts (ip,fail_count,blocked) VALUES (:ip,0,0)")->execute([':ip' => $ip]);
    $ipdata = ['ip' => $ip, 'fail_count' => 0, 'blocked' => 0];
}

// If blocked
if ($ipdata['blocked']) {
    header('Content-Type: text/plain', true, 403);
    die("Your IP ($ip) is blocked due to too many failed attempts.");
}

// Generate CAPTCHA only if not set (so user can submit)
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha_a'] = rand(1,9);
    $_SESSION['captcha_b'] = rand(1,9);
    $_SESSION['captcha'] = $_SESSION['captcha_a'] + $_SESSION['captcha_b'];
}
$a = $_SESSION['captcha_a'];
$b = $_SESSION['captcha_b'];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $captcha_input = array_key_exists('captcha', $_POST) ? $_POST['captcha'] : null;

    $failed = false;

    // CAPTCHA logic: correct OR bypass with '0', missing field -> bypass (lab)
    if ($captcha_input !== null) {
        if (!is_numeric($captcha_input)) {
            $failed = true;
        } else {
            if ((int)$captcha_input !== (int)$_SESSION['captcha'] && $captcha_input !== '0') {
                $failed = true;
            }
        }
    }

    // Check credentials
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :u AND password = :p");
    $stmt->execute([':u' => $username, ':p' => $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) $failed = true;

    if ($failed) {
        $fail = $ipdata['fail_count'] + 1;
        $blocked = ($fail >= 10) ? 1 : 0;
        $db->prepare("UPDATE attempts SET fail_count = :f, blocked = :b WHERE ip = :ip")
           ->execute([':f' => $fail, ':b' => $blocked, ':ip' => $ip]);
        $error = "Invalid username/password or CAPTCHA.";
        // refresh ipdata for next use
        $stmt = $db->prepare("SELECT * FROM attempts WHERE ip = :ip");
        $stmt->execute([':ip' => $ip]);
        $ipdata = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // success -> reset attempts, create session
        $db->prepare("UPDATE attempts SET fail_count = 0, blocked = 0 WHERE ip = :ip")
           ->execute([':ip' => $ip]);

        unset($_SESSION['captcha'], $_SESSION['captcha_a'], $_SESSION['captcha_b']);

        $sid = bin2hex(random_bytes(16));
        setcookie('LABSESSID', $sid, time()+3600, '/');

        $db->prepare("INSERT INTO sessions (sid, username) VALUES (:sid, :username)")
           ->execute([':sid' => $sid, ':username' => $username]);

        header('Location: dashboard.php');
        exit;
    }
}
//This is a comment- dict.txt //
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login (Vuln | dict.txt)</title>
<style>
body { background:#111; color:#eaeaea; font-family:Arial, Helvetica, sans-serif; display:flex; align-items:center; justify-content:center; height:100vh; }
.card { background:#222; padding:24px; border-radius:8px; width:360px; box-shadow:0 8px 30px rgba(0,0,0,0.6); }
input { width:100%; padding:10px; margin:8px 0; border-radius:4px; border:1px solid #333; background:#0f0f0f; color:#eee; }
button { width:100%; padding:12px; border:none; border-radius:4px; background:#4caf50; color:#fff; cursor:pointer; }
.captcha { display:flex; gap:10px; align-items:center; margin-bottom:10px; }
.captcha .num { background:#333; padding:6px 10px; border-radius:4px; font-weight:bold; }
.error { color:#ff6b6b; margin-bottom:8px; text-align:center; }
.small { font-size:12px; color:#999; margin-top:8px; }
</style>
</head>
<body>
<div class="card">
  <h2 style="margin-top:0; text-align:center;">Admin Login</h2>
  <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST" action="index.php">
    <input name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <div class="captcha">
      <div class="num"><?= htmlspecialchars($a . ' + ' . $b) ?></div>
      <input name="captcha" placeholder="Answer" autocomplete="off">
    </div>
    <button type="submit">Login</button>
  </form>
  <div class="small">Visit<b></b> / <b>hacksudo.com</b>. Login : <code>0</code> .</div>
</div>
</body>
</html>
