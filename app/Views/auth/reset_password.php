<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — RentalWebsite</title>
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

        /* Shield badge — brand-tinted like the other auth pages */
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

        label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;
              letter-spacing:.07em;color:var(--muted);margin-bottom:6px}

        .field{margin-bottom:14px}

        input[type="password"],input[type="text"]{
            width:100%;background:var(--light);border:1.5px solid var(--border);
            border-radius:8px;padding:12px 14px;font-size:14px;font-family:'Inter',sans-serif;
            color:var(--darkest);outline:none;transition:border-color .15s,box-shadow .15s;
        }
        input::placeholder{color:var(--sage);opacity:.6}
        input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(35,83,71,.1)}

        .pw-wrap{position:relative}
        .pw-wrap input{padding-right:54px}
        .pw-toggle{position:absolute;right:12px;top:50%;transform:translateY(-50%);
                   background:none;border:none;font-size:11px;font-weight:700;
                   font-family:'Inter',sans-serif;letter-spacing:.05em;text-transform:uppercase;
                   color:var(--sage);cursor:pointer;padding:0;transition:color .15s}
        .pw-toggle:hover{color:var(--primary)}

        /* Password rules checklist */
        .rules{list-style:none;margin:10px 0 18px;display:grid;gap:6px}
        .rules li{display:flex;align-items:center;gap:8px;font-size:12px;color:var(--muted)}
        .rules li::before{
            content:'';width:14px;height:14px;flex-shrink:0;border-radius:50%;
            border:1.5px solid var(--border);background:var(--light);
        }
        .rules li.ok{color:var(--primary);font-weight:600}
        .rules li.ok::before{
            content:'✓';display:flex;align-items:center;justify-content:center;
            font-size:9px;font-weight:800;line-height:1;
            background:var(--primary);border-color:var(--primary);color:#fff;
        }

        .btn{
            width:100%;background:var(--primary);color:#fff;border:none;border-radius:8px;
            padding:13px;font-size:15px;font-weight:700;font-family:'Inter',sans-serif;
            cursor:pointer;transition:background .15s;letter-spacing:-.01em;
        }
        .btn:hover{background:#163832}

        .alert{padding:11px 14px;border-radius:8px;font-size:13px;margin-bottom:18px;line-height:1.5}
        .alert-danger{background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b}
        .alert-danger ul{margin:5px 0 0;padding-left:16px}

        .back{display:block;text-align:center;margin-top:18px;font-size:13px;
              color:var(--muted);text-decoration:none;font-weight:500}
        .back:hover{color:var(--primary)}

        @media(max-width:480px){.card{padding:32px 22px;margin:16px}.brand,.tagline{display:none}}
    </style>
</head>
<body>
<?php
$appUrl = rtrim(\App\Helpers\Url::base(), '/');
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);
if (empty($_SESSION['_csrf_token'])) $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
?>

<div class="bg">
    <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1600&q=80&auto=format&fit=crop" alt="">
    <div class="bg-overlay"></div>
</div>

<a href="<?= htmlspecialchars($appUrl) ?>" class="brand">RentalWebsite</a>
<p class="tagline">See it. Understand it. Then decide.</p>

<div class="card">
    <div class="icon">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M12 3 4.5 5.8v5.4c0 4.6 3.2 8 7.5 9.6 4.3-1.6 7.5-5 7.5-9.6V5.8L12 3z" stroke="#235347" stroke-width="1.8" stroke-linejoin="round"/>
            <path d="m9 11.8 2.1 2.2L15.2 9.8" stroke="#235347" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>
    <h1>Set a new password</h1>
    <p class="sub">
        Choose a strong, unique password for your account. You&rsquo;ll use it next time you sign in.
    </p>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars($appUrl . '/auth/reset-password') ?>" novalidate>
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'], ENT_QUOTES) ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES) ?>">

        <div class="field">
            <label for="password">New password</label>
            <div class="pw-wrap">
                <input type="password" id="password" name="password"
                       placeholder="Create a new password"
                       required minlength="8" autocomplete="new-password">
                <button type="button" class="pw-toggle" onclick="togglePw('password',this)">Show</button>
            </div>
        </div>

        <div class="field">
            <label for="confirm_password">Confirm new password</label>
            <div class="pw-wrap">
                <input type="password" id="confirm_password" name="confirm_password"
                       placeholder="Repeat your new password"
                       required minlength="8" autocomplete="new-password">
                <button type="button" class="pw-toggle" onclick="togglePw('confirm_password',this)">Show</button>
            </div>
        </div>

        <ul class="rules" id="rules">
            <li data-rule="len">At least 8 characters</li>
            <li data-rule="upper">One uppercase letter</li>
            <li data-rule="num">One number</li>
            <li data-rule="match">Both passwords match</li>
        </ul>

        <button type="submit" class="btn">Reset password</button>
    </form>

    <a href="<?= htmlspecialchars($appUrl . '/auth/login') ?>" class="back">&larr; Back to sign in</a>
</div>

<script>
function togglePw(id,btn){var i=document.getElementById(id);if(!i)return;var h=i.type==='password';i.type=h?'text':'password';btn.textContent=h?'Hide':'Show';}

/* Live password-rules checklist (mirrors server-side validation in
   PasswordController::resetPassword — 8 chars, uppercase, number, match) */
(function(){
    var pw = document.getElementById('password'),
        cf = document.getElementById('confirm_password');
    if(!pw || !cf) return;
    function li(name){ return document.querySelector('.rules li[data-rule="'+name+'"]'); }
    function mark(el, ok){ if(el) el.classList.toggle('ok', ok); }
    function refresh(){
        var v = pw.value;
        mark(li('len'),   v.length >= 8);
        mark(li('upper'), /[A-Z]/.test(v));
        mark(li('num'),   /[0-9]/.test(v));
        mark(li('match'), v.length > 0 && v === cf.value);
    }
    pw.addEventListener('input', refresh);
    cf.addEventListener('input', refresh);
})();
</script>
</body>
</html>
