<?php
require_once __DIR__ . '/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/db.php'; // provides $pdo

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare('SELECT username, password_hash FROM hr_credentials WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $row = $stmt->fetch();

        if ($row && password_verify($password, $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['hr_authenticated'] = true;
            $_SESSION['hr_username'] = $row['username'];
            header('Location: index.php');
            exit;
        }

        $error = 'Incorrect username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>E-PaySlip | Login</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          paper:   '#FAF7F0',
          ink:     '#1F2A44',
          inksoft: '#5B6478',
          brass:   '#A9793F',
          brasslt: '#E4D3B4',
          line:    '#E4DFD1',
        },
        fontFamily: {
          display: ['Fraunces', 'serif'],
          body:    ['Inter', 'sans-serif'],
          mono:    ['"IBM Plex Mono"', 'monospace'],
        },
      },
    },
  };
</script>
<style>
  body {
    background-color: #FAF7F0;
    background-image: repeating-linear-gradient(
      to bottom,
      transparent 0px, transparent 27px, #EFE9D8 28px
    );
  }
</style>
</head>
<body class="min-h-screen font-body text-ink flex items-center justify-center px-4">

<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <p class="font-mono text-xs tracking-[0.3em] text-brass uppercase mb-2">HR Desk · Offline System</p>
        <h1 class="font-display text-3xl font-semibold text-ink">E-PaySlip</h1>
    </div>

    <form method="POST" class="bg-white border border-line rounded-xl shadow-sm p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-inksoft mb-1">Username</label>
            <input type="text" name="username" required autofocus
                   class="w-full border border-line rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brass/50">
        </div>
        <div>
            <label class="block text-sm font-medium text-inksoft mb-1">Password</label>
            <input type="password" name="password" required
                   class="w-full border border-line rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brass/50">
        </div>

        <?php if ($error): ?>
            <p class="text-rose-600 text-sm"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <button type="submit"
                class="w-full bg-ink hover:bg-ink/90 text-white font-semibold py-2.5 rounded-lg">
            Log In
        </button>
    </form>
</div>

</body>
</html>
