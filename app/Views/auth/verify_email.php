<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email — RentalWebsite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --darkest:#051F20;--primary:#235347;--sage:#8EB69B;
            --mint:#DAF1DE;--white:#ffffff;--border:#c8e2cf;
            --light:#f0f9f2;--muted:#5a7a6e;
        }
        body{
            font-family:'Inter',system-ui,sans-serif;min-height:100vh;
            display:flex;align-items:center;justify-content:center;
            position:relative;overflow:hidden;-webkit-font-smoothing:antialiased;
        }
        .bg{position:fixed;inset:0;z-index:0}
        .bg img{width:100%;height:100%;object-fit:cover}
        .bg-overlay{position:absolute;inset:0;background:rgba(0,0,0,.32)}
        .brand{position:fixed;top:26px;left:36px;z-index:10;font-size:17px;font-weight:800;color:#fff;letter-spacing:-.03em;text-decoration:none}
        .tagline{position:fixed;bottom:28px;left:36px;z-index:10;font-size:13px;color:rgba(255,255,255,.75)}

        /* Card */
        .card{
            position:relative;z-index:10;background:var(--white);
            border-radius:20px;padding:44px 48px;
            width:100%;max-width:440px;margin:24px;
            box-shadow:0 28px 80px rgba(5,31,32,.4);
            border:1.5px solid var(--border);text-align:center;
        }

        /* Icon */
        .otp-icon{
            width:64px;height:64px;margin:0 auto 20px;border-radius:18px;
            background:var(--light);border:1.5px solid var(--border);
            display:flex;align-items:center;justify-content:center;
        }

        h1{font-size:22px;font-weight:800;color:var(--darkest);letter-spacing:-.04em;margin-bottom:8px}
        .sub{font-size:13px;color:var(--muted);margin-bottom:28px;line-height:1.65}
        .sub .email{
            display:inline-block;padding:1px 8px;border-radius:6px;
            background:var(--light);border:1px solid var(--border);
            color:var(--primary);font-weight:700;overflow-wrap:anywhere;
        }

        /* OTP digit boxes */
        .otp-boxes{
            display:flex;justify-content:center;gap:10px;margin-bottom:8px;
        }
        .otp-box{
            width:52px;height:60px;
            border:2px solid var(--border);border-radius:12px;
            font-size:26px;font-weight:800;font-family:'Inter',monospace;
            color:var(--darkest);text-align:center;background:var(--light);
            outline:none;transition:border-color .15s,box-shadow .15s;
            -webkit-appearance:none;
        }
        .otp-box:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(35,83,71,.1);background:#fff}
        .otp-box.filled{border-color:var(--primary);background:#fff}
        .otp-hint{font-size:11px;color:var(--muted);margin-bottom:24px}

        /* Timer */
        .otp-timer{
            font-size:12px;color:var(--muted);margin-bottom:20px;
        }
        .otp-timer strong{color:var(--primary)}
        .otp-timer.expired strong{color:#dc2626}

        /* Submit button */
        .btn{
            width:100%;background:var(--primary);color:#fff;border:none;border-radius:8px;
            padding:13px;font-size:15px;font-weight:700;font-family:'Inter',sans-serif;
            cursor:pointer;transition:background .15s,opacity .15s;letter-spacing:-.01em;
        }
        .btn:hover{background:#163832}
        .btn:disabled{opacity:.5;cursor:not-allowed}

        /* Resend */
        .resend-row{margin-top:16px;font-size:13px;color:var(--muted)}
        .resend-row button{
            background:none;border:none;color:var(--primary);font-weight:700;
            cursor:pointer;font-size:13px;font-family:'Inter',sans-serif;
            text-decoration:underline;padding:0;
        }
        .resend-row button:disabled{opacity:.4;cursor:not-allowed;text-decoration:none}

        /* Resend email form (hidden by default) */
        .resend-form{display:none;margin-top:14px;text-align:left}
        .resend-form.open{display:block}
        .resend-form input[type="email"]{
            width:100%;background:var(--light);border:1.5px solid var(--border);
            border-radius:8px;padding:11px 13px;font-size:14px;
            font-family:'Inter',sans-serif;color:var(--darkest);
            outline:none;margin-bottom:8px;transition:border-color .15s;
        }
        .resend-form input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(35,83,71,.1)}
        .resend-form .btn-ghost{
            width:100%;background:var(--light);color:var(--primary);
            border:1.5px solid var(--border);border-radius:8px;
            padding:11px;font-size:14px;font-weight:700;
            font-family:'Inter',sans-serif;cursor:pointer;transition:background .15s;
        }
        .resend-form .btn-ghost:hover{background:var(--mint)}

        .alert{padding:11px 14px;border-radius:8px;font-size:13px;margin-bottom:18px;line-height:1.5;text-align:left}
        .alert-success{background:#f0fdf4;border:1.5px solid #bbf7d0;color:#166534}
        .alert-warning{background:#fffbeb;border:1.5px solid #fde68a;color:#92400e}
        .alert-danger {background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b}
        .alert-info   {background:var(--light);border:1.5px solid var(--border);color:var(--primary)}

        .back-link{margin-top:18px;font-size:13px}
        .back-link a{color:var(--muted);text-decoration:none;font-weight:500}
        .back-link a:hover{color:var(--primary)}

        @media(max-width:480px){
            .card{padding:32px 20px;margin:16px}
            .brand,.tagline{display:none}
            .otp-box{width:42px;height:52px;font-size:22px;gap:6px}
            .otp-boxes{gap:6px}
        }
    </style>
</head>
<body>
<?php
$appUrl    = rtrim(\App\Helpers\Url::base(), '/');
$flash     = $_SESSION['_flash'] ?? [];
unset($_SESSION['_flash']);
if (empty($_SESSION['_csrf_token'])) $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
$hintEmail = $_SESSION['verify_email_hint'] ?? '';
unset($_SESSION['verify_email_hint']);
?>

<div class="bg">
    <img src="https://images.unsplash.com/photo-1560185127-6ed189bf02f4?w=1600&q=80&auto=format&fit=crop" alt="">
    <div class="bg-overlay"></div>
</div>

<a href="<?= htmlspecialchars($appUrl) ?>" class="brand">RentalWebsite</a>
<p class="tagline">See it. Understand it. Then decide.</p>

<div class="card">

    <!-- Icon -->
    <div class="otp-icon">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#235347" stroke-width="1.8" stroke-linecap="round">
            <rect x="2.5" y="5" width="19" height="14" rx="3"/>
            <path d="M3.5 7.5 12 13l8.5-5.5"/>
        </svg>
    </div>

    <h1>Enter your code</h1>
    <p class="sub">
        We sent a 6-digit code to
        <?php if ($hintEmail): ?>
        <span class="email"><?= htmlspecialchars($hintEmail, ENT_QUOTES, 'UTF-8') ?></span>.
        <?php else: ?>
        your email address.
        <?php endif; ?>
        It expires in <strong>10 minutes</strong>.
    </p>

    <!-- Flash messages -->
    <?php foreach ($flash as $msg):
        $t = in_array($msg['type'], ['danger','warning','success','info']) ? $msg['type'] : 'info';
    ?>
    <div class="alert alert-<?= $t ?>"><?= htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endforeach; ?>

    <!-- OTP form -->
    <form method="POST" action="<?= htmlspecialchars($appUrl . '/auth/verify-email') ?>" id="otpForm">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'], ENT_QUOTES) ?>">

        <div class="otp-boxes" id="otpBoxes">
            <?php for ($i = 1; $i <= 6; $i++): ?>
            <input type="text" name="otp<?= $i ?>" id="otp<?= $i ?>"
                   class="otp-box" maxlength="1" inputmode="numeric"
                   pattern="[0-9]" autocomplete="<?= $i === 1 ? 'one-time-code' : 'off' ?>"
                   aria-label="Digit <?= $i ?>">
            <?php endfor; ?>
        </div>
        <p class="otp-hint">Enter the 6-digit code from your email</p>

        <!-- Countdown timer -->
        <div class="otp-timer" id="otpTimer">
            Code expires in <strong id="timerVal">10:00</strong>
        </div>

        <button type="submit" class="btn" id="otpSubmit" disabled>Verify email</button>
    </form>

    <!-- Resend section -->
    <div class="resend-row">
        Didn't receive it?
        <button type="button" id="resendToggle" onclick="document.getElementById('resendForm').classList.toggle('open')">
            Resend code
        </button>
    </div>

    <div class="resend-form" id="resendForm">
        <form method="POST" action="<?= htmlspecialchars($appUrl . '/auth/resend-verification') ?>">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'], ENT_QUOTES) ?>">
            <input type="email" name="email"
                   value="<?= htmlspecialchars($hintEmail, ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="your@email.com" required autocomplete="email">
            <button type="submit" class="btn-ghost">Send new code</button>
        </form>
    </div>

    <div class="back-link">
        <a href="<?= htmlspecialchars($appUrl . '/auth/login') ?>">← Back to sign in</a>
    </div>

</div>

<script>
(function () {
    // ── Auto-focus and move between boxes ─────────────────
    var boxes = Array.from(document.querySelectorAll('.otp-box'));
    var submit = document.getElementById('otpSubmit');

    boxes[0].focus();

    function updateSubmit() {
        var all = boxes.every(function (b) { return b.value.match(/\d/); });
        submit.disabled = !all;
        if (all) submit.focus();
    }

    boxes.forEach(function (box, idx) {
        box.addEventListener('input', function (e) {
            // Allow paste of full code into first box
            var val = this.value.replace(/\D/g, '');
            if (val.length > 1) {
                // Distribute pasted digits
                var chars = val.slice(0, 6).split('');
                chars.forEach(function (c, i) {
                    if (boxes[idx + i]) {
                        boxes[idx + i].value = c;
                        boxes[idx + i].classList.add('filled');
                    }
                });
                var next = boxes[Math.min(idx + chars.length, 5)];
                if (next) next.focus();
                updateSubmit();
                return;
            }
            this.value = val;
            if (val) {
                this.classList.add('filled');
                if (idx < 5) boxes[idx + 1].focus();
            } else {
                this.classList.remove('filled');
            }
            updateSubmit();
        });

        box.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && idx > 0) {
                boxes[idx - 1].value = '';
                boxes[idx - 1].classList.remove('filled');
                boxes[idx - 1].focus();
                updateSubmit();
            }
            // Allow only digits and control keys
            if (e.key.length === 1 && !/\d/.test(e.key) && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
            }
        });

        box.addEventListener('focus', function () { this.select(); });
    });

    // Handle native autocomplete OTP paste into first field
    document.getElementById('otp1').addEventListener('change', function () {
        var val = this.value.replace(/\D/g, '');
        if (val.length === 6) {
            val.split('').forEach(function (c, i) {
                boxes[i].value = c;
                boxes[i].classList.add('filled');
            });
            updateSubmit();
        }
    });

    // ── Countdown timer (10 min = 600s) ──────────────────
    var seconds = 600;
    var timerEl = document.getElementById('timerVal');
    var timerWrap = document.getElementById('otpTimer');

    var timer = setInterval(function () {
        seconds--;
        if (seconds <= 0) {
            clearInterval(timer);
            timerWrap.classList.add('expired');
            timerEl.textContent = '0:00';
            submit.disabled = true;
            submit.textContent = 'Code expired — request a new one';
            document.getElementById('resendForm').classList.add('open');
            return;
        }
        var m = Math.floor(seconds / 60);
        var s = seconds % 60;
        timerEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
        if (seconds <= 60) timerWrap.classList.add('expired');
    }, 1000);

})();
</script>
</body>
</html>
