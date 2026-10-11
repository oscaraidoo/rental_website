<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — RentalWebsite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

        :root{
            --darkest:#051F20;--dark:#0B2B26;--primary:#235347;
            --sage:#8EB69B;--mint:#DAF1DE;--white:#ffffff;
            --border:#c8e2cf;--light:#f0f9f2;--muted:#5a7a6e;
        }

        body{
            font-family:'Inter',system-ui,sans-serif;
            min-height:100vh;display:flex;align-items:center;
            justify-content:center;position:relative;
            overflow-x:hidden;padding:80px 16px 40px;-webkit-font-smoothing:antialiased;
        }

        /* Full-screen background — pure photo, minimal dark scrim only for text legibility */
        .bg{position:fixed;inset:0;z-index:0}
        .bg img{width:100%;height:100%;object-fit:cover;}
        .bg-overlay{position:absolute;inset:0;background:rgba(0,0,0,.32)}

        .brand{position:fixed;top:26px;left:36px;z-index:10;
               font-size:17px;font-weight:800;color:#fff;
               letter-spacing:-.03em;text-decoration:none}
        .tagline{position:fixed;bottom:28px;left:36px;z-index:10;
                 font-size:13px;font-weight:500;color:rgba(255,255,255,.75);letter-spacing:-.01em}

        /* Back-to-home button — page level, outside the card */
        .home-btn{position:fixed;top:22px;right:36px;z-index:10;
                  display:inline-flex;align-items:center;gap:8px;
                  background:rgba(255,255,255,.14);
                  border:1.5px solid rgba(255,255,255,.35);
                  color:#fff;font-size:14px;font-weight:600;
                  font-family:'Inter',sans-serif;letter-spacing:-.01em;
                  min-height:44px;padding:10px 18px;border-radius:999px;cursor:pointer;
                  text-decoration:none;backdrop-filter:blur(8px);
                  -webkit-backdrop-filter:blur(8px);
                  transition:background .15s,border-color .15s}
        .home-btn:hover{background:rgba(255,255,255,.26);border-color:rgba(255,255,255,.6)}
        .home-btn svg{flex-shrink:0}

        /* Card */
        .card{
            position:relative;z-index:10;
            background:var(--white);
            border-radius:20px;
            padding:44px 48px;
            width:100%;max-width:420px;margin:24px;
            box-shadow:0 28px 80px rgba(5,31,32,.4);
            border:1.5px solid var(--border);
        }

        h1{font-size:22px;font-weight:800;color:var(--darkest);letter-spacing:-.04em;margin-bottom:6px}
        .sub{font-size:13px;color:var(--muted);margin-bottom:28px}
        .sub a{color:var(--primary);font-weight:700;text-decoration:underline;
               display:inline-block;padding:10px 4px;margin:-10px -4px}

        label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;
              letter-spacing:.07em;color:var(--muted);margin-bottom:6px}

        input[type="email"],input[type="password"],input[type="text"]{
            width:100%;background:var(--light);border:1.5px solid var(--border);
            border-radius:8px;padding:12px 14px;font-size:16px;font-family:'Inter',sans-serif;
            min-height:48px;color:var(--darkest);outline:none;transition:border-color .15s,box-shadow .15s;
        }
        input::placeholder{color:var(--muted);opacity:.85}
        input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(35,83,71,.1)}

        .field{margin-bottom:16px}
        .field-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px}
        .field-row label{margin:0}
        .forgot{font-size:13px;color:var(--muted);text-decoration:none;font-weight:500;
                display:inline-block;padding:10px 4px;margin:-10px -4px;min-height:34px}
        .forgot:hover{color:var(--primary)}

        .pw-wrap{position:relative}
        .pw-wrap input{padding-right:54px}
        .pw-toggle{position:absolute;right:4px;top:50%;transform:translateY(-50%);
                   background:none;border:none;font-size:12px;font-weight:700;
                   font-family:'Inter',sans-serif;letter-spacing:.05em;text-transform:uppercase;
                   color:var(--muted);cursor:pointer;padding:12px;min-width:44px;min-height:44px;
                   transition:color .15s}
        .pw-toggle:hover{color:var(--primary)}

        .remember{display:flex;align-items:center;gap:9px;margin-bottom:22px}
        .remember input[type="checkbox"]{
            width:16px;height:16px;border:1.5px solid var(--border);border-radius:4px;
            background:var(--light);cursor:pointer;appearance:none;-webkit-appearance:none;
            position:relative;flex-shrink:0;transition:border-color .15s,background .15s;
            accent-color:var(--primary);
        }
        .remember input:checked{background:var(--primary);border-color:var(--primary)}
        .remember input:checked::after{
            content:'';position:absolute;left:3px;top:1px;width:8px;height:5px;
            border-left:2px solid #fff;border-bottom:2px solid #fff;transform:rotate(-45deg);
        }
        .remember label{font-size:13px;color:var(--muted);text-transform:none;
                         letter-spacing:0;margin:0;cursor:pointer}

        .btn{
            width:100%;background:var(--primary);color:#fff;border:none;border-radius:8px;
            padding:13px;font-size:15px;font-weight:700;font-family:'Inter',sans-serif;
            cursor:pointer;transition:background .15s;letter-spacing:-.01em;
        }
        .btn:hover{background:#163832}

        /* Google button */
        .or-divider{display:flex;align-items:center;gap:12px;margin:18px 0;color:var(--sage);
                    font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.08em}
        .or-divider::before,.or-divider::after{content:'';flex:1;height:1.5px;background:var(--border)}
        .g-btn{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;
               background:#fff;color:#1f2937;border:1.5px solid var(--border);border-radius:8px;
               padding:12px;font-size:14px;font-weight:600;font-family:'Inter',sans-serif;
               text-decoration:none;transition:border-color .15s,box-shadow .15s}
        .g-btn:hover{border-color:var(--primary);box-shadow:0 1px 4px rgba(5,31,32,.08)}
        .g-btn svg{flex-shrink:0}

        .alert{padding:11px 14px;border-radius:8px;font-size:13px;margin-bottom:18px;line-height:1.5}
        .alert-danger {background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b}
        .alert-warning{background:#fffbeb;border:1.5px solid #fde68a;color:#92400e}
        .alert-success{background:#f0fdf4;border:1.5px solid #bbf7d0;color:#166534}
        .alert-info   {background:var(--light);border:1.5px solid var(--border);color:var(--primary)}

        .note{font-size:12px;color:var(--muted);text-align:center;margin-top:16px;line-height:1.6}
        .note a{color:var(--primary);text-decoration:underline;display:inline-block;padding:8px 4px;margin:-8px -4px}

        @media(max-width:480px){.card{padding:32px 22px;margin:16px}.brand,.tagline{display:none}.home-btn{top:16px;right:16px}}
    </style>
</head>
<body>
<?php
$appUrl = rtrim(\App\Helpers\Url::base(), '/');
$flash  = $_SESSION['_flash'] ?? [];
unset($_SESSION['_flash']);
if (empty($_SESSION['_csrf_token'])) $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
?>

<div class="bg">
    <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1600&q=80&auto=format&fit=crop" alt="">
    <div class="bg-overlay"></div>
</div>

<a href="<?= htmlspecialchars($appUrl) ?>" class="brand">RentalWebsite</a>
<p class="tagline">See it. Understand it. Then decide.</p>

<a href="<?= htmlspecialchars($appUrl) ?>" class="home-btn">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
    Back to home
</a>

<div class="card">
    <h1>Welcome back</h1>
    <p class="sub">
        No account? <a href="<?= htmlspecialchars($appUrl . '/auth/register') ?>">Create one free</a>
    </p>

    <?php foreach ($flash as $msg):
        $t = in_array($msg['type'],['danger','warning','success','info']) ? $msg['type'] : 'info';
    ?>
    <div class="alert alert-<?= $t ?>"><?= htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endforeach; ?>

    <form method="POST" action="<?= htmlspecialchars($appUrl . '/auth/login') ?>" novalidate>
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'], ENT_QUOTES) ?>">

        <div class="field">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" placeholder="you@example.com"
                   required autocomplete="email"
                   value="<?= htmlspecialchars($_SESSION['form_old']['email'] ?? '', ENT_QUOTES) ?>">
        </div>

        <div class="field">
            <div class="field-row">
                <label for="password">Password</label>
                <a href="<?= htmlspecialchars($appUrl . '/auth/forgot-password') ?>" class="forgot">Forgot?</a>
            </div>
            <div class="pw-wrap">
                <input type="password" id="password" name="password"
                       placeholder="Your password" required autocomplete="current-password">
                <button type="button" class="pw-toggle" onclick="togglePw('password',this)">Show</button>
            </div>
        </div>

        <div class="remember">
            <input type="checkbox" id="rememberMe" name="remember_me" value="1">
            <label for="rememberMe">Stay signed in for 30 days</label>
        </div>

        <button type="submit" class="btn">Sign in</button>
    </form>

    <?php if (\App\Helpers\GoogleOAuth::isEnabled()): ?>
    <div class="or-divider"><span>or</span></div>
    <a href="<?= htmlspecialchars($appUrl . '/auth/google') ?>" class="g-btn">
        <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4" fill-rule="evenodd"/><path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853" fill-rule="evenodd"/><path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05" fill-rule="evenodd"/><path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335" fill-rule="evenodd"/></svg>
        Continue with Google
    </a>
    <?php endif; ?>

    <p class="note">
        By signing in you agree to our
        <a href="<?= htmlspecialchars($appUrl . '/page/terms') ?>">Terms</a> &amp;
        <a href="<?= htmlspecialchars($appUrl . '/page/privacy') ?>">Privacy Policy</a>.
    </p>
</div>

<script>
function togglePw(id,btn){var i=document.getElementById(id);if(!i)return;var h=i.type==='password';i.type=h?'text':'password';btn.textContent=h?'Hide':'Show';}
</script>
<?php unset($_SESSION['form_old']); ?>
</body>
</html>
