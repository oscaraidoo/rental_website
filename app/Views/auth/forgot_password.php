<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — RentalWebsite</title>
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
            overflow:hidden;-webkit-font-smoothing:antialiased;
        }

        /* Full-screen background — same treatment as sign in / sign up */
        .bg{position:fixed;inset:0;z-index:0}
        .bg img{width:100%;height:100%;object-fit:cover}
        .bg-overlay{position:absolute;inset:0;background:rgba(0,0,0,.32)}

        .brand{position:fixed;top:26px;left:36px;z-index:10;
               font-size:17px;font-weight:800;color:#fff;
               letter-spacing:-.03em;text-decoration:none}
        .tagline{position:fixed;bottom:28px;left:36px;z-index:10;
                 font-size:13px;font-weight:500;color:rgba(255,255,255,.75);letter-spacing:-.01em}

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

        /* Key badge — brand-tinted like the envelope badge on verify email */
        .icon{
            width:64px;height:64px;margin:0 auto 22px;
            border-radius:18px;
            background:var(--light);
            border:1.5px solid var(--border);
            display:flex;align-items:center;justify-content:center;
        }
        .icon svg{display:block}

        h1{font-size:22px;font-weight:800;color:var(--darkest);letter-spacing:-.04em;margin-bottom:6px}
        .sub{font-size:13px;color:var(--muted);margin-bottom:26px;line-height:1.65}
        .sub a{color:var(--primary);font-weight:700;text-decoration:underline}

        label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;
              letter-spacing:.07em;color:var(--muted);margin-bottom:6px}

        input[type="email"]{
            width:100%;background:var(--light);border:1.5px solid var(--border);
            border-radius:8px;padding:12px 14px;font-size:14px;font-family:'Inter',sans-serif;
            color:var(--darkest);outline:none;transition:border-color .15s,box-shadow .15s;
            margin-bottom:16px;
        }
        input::placeholder{color:var(--sage);opacity:.6}
        input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(35,83,71,.1)}

        .btn{
            width:100%;background:var(--primary);color:#fff;border:none;border-radius:8px;
            padding:13px;font-size:15px;font-weight:700;font-family:'Inter',sans-serif;
            cursor:pointer;transition:background .15s;letter-spacing:-.01em;
        }
        .btn:hover{background:#163832}

        .hint{
            display:flex;gap:9px;align-items:flex-start;
            background:var(--light);border:1.5px solid var(--border);
            border-radius:8px;padding:11px 13px;
            font-size:12px;color:var(--muted);line-height:1.55;margin-bottom:20px;
        }
        .hint svg{flex-shrink:0;margin-top:1px}

        .alert{padding:11px 14px;border-radius:8px;font-size:13px;margin-bottom:18px;line-height:1.5}
        .alert-success{background:#f0fdf4;border:1.5px solid #bbf7d0;color:#166534}
        .alert-warning{background:#fffbeb;border:1.5px solid #fde68a;color:#92400e}
        .alert-danger {background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b}
        .alert-info   {background:var(--light);border:1.5px solid var(--border);color:var(--primary)}

        .back{display:block;text-align:center;margin-top:18px;font-size:13px;
              color:var(--muted);text-decoration:none;font-weight:500}
        .back:hover{color:var(--primary)}

        @media(max-width:480px){.card{padding:32px 22px;margin:16px}.brand,.tagline{display:none}}
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
    <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1600&q=80&auto=format&fit=crop" alt="">
    <div class="bg-overlay"></div>
</div>

<a href="<?= htmlspecialchars($appUrl) ?>" class="brand">RentalWebsite</a>
<p class="tagline">See it. Understand it. Then decide.</p>

<div class="card">
    <div class="icon">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <circle cx="8" cy="15" r="4.5" stroke="#235347" stroke-width="1.8"/>
            <path d="M11.5 11.5 19.5 3.5M16 7l2.5 2.5M13.5 9.5 16 12" stroke="#235347" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>
    <h1>Forgot password?</h1>
    <p class="sub">
        No problem — enter the email you signed up with and we&rsquo;ll send you a link to set a new one.
    </p>

    <?php foreach ($flash as $msg):
        $t = in_array($msg['type'], ['danger', 'warning', 'success', 'info']) ? $msg['type'] : 'info';
    ?>
    <div class="alert alert-<?= $t ?>"><?= htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endforeach; ?>

    <div class="hint">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <circle cx="12" cy="12" r="9.5" stroke="#235347" stroke-width="1.8"/>
            <path d="M12 8v5" stroke="#235347" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="12" cy="16.2" r="1.1" fill="#235347"/>
        </svg>
        <span>For security, the reset link works for <strong>1 hour</strong> and can only be used once. Also check your spam folder.</span>
    </div>

    <form method="POST" action="<?= htmlspecialchars($appUrl . '/auth/forgot-password') ?>">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'], ENT_QUOTES) ?>">

        <label for="email">Email address</label>
        <input type="email" id="email" name="email" placeholder="you@example.com"
               required autocomplete="email"
               value="<?= htmlspecialchars($_SESSION['form_old']['email'] ?? '', ENT_QUOTES) ?>">

        <button type="submit" class="btn">Send reset link</button>
    </form>

    <a href="<?= htmlspecialchars($appUrl . '/auth/login') ?>" class="back">&larr; Back to sign in</a>
</div>
<?php unset($_SESSION['form_old']); ?>
</body>
</html>
