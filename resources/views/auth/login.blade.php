<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login - Master Hub System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>

    <style>
        :root{
            --midnight:#07122e;
            --cobalt:#1238c4;
            --hp:#0096d6;
            --glow:#22d3ee;
            --ink:#0f172a;
            --muted:#64748b;
            --line:#dbe3ee;
            --danger:#b42318;
        }

        *{ margin:0; padding:0; box-sizing:border-box; }

        body{
            min-height:100vh;
            font-family:'Segoe UI Variable Text','Segoe UI',system-ui,-apple-system,Roboto,Arial,sans-serif;
            -webkit-font-smoothing:antialiased;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:28px 16px;
            overflow-x:hidden;
            background:var(--midnight);
        }

        /* ===== LATAR BERGERAK ===== */
        .bg{ position:fixed; inset:0; z-index:0; overflow:hidden;
             background:linear-gradient(135deg,#050c20 0%,#0f2358 55%,#1646c8 100%); }
        .bg::after{ content:""; position:absolute; inset:0;
             background:radial-gradient(circle at 1px 1px, rgba(255,255,255,.07) 1px, transparent 0) 0 0 / 26px 26px; }
        .orb{ position:absolute; border-radius:50%; filter:blur(80px); animation:drift 18s ease-in-out infinite; }
        .orb.a{ width:440px; height:440px; background:#2563eb; top:-140px; left:-120px; opacity:.5; }
        .orb.b{ width:380px; height:380px; background:var(--glow); bottom:-160px; right:-100px; opacity:.28; animation-delay:-7s; }
        .orb.c{ width:300px; height:300px; background:var(--hp); top:45%; left:58%; opacity:.22; animation-delay:-12s; }

        @keyframes drift{
            0%,100%{ transform:translate(0,0) scale(1); }
            50%{ transform:translate(46px,-34px) scale(1.12); }
        }

        /* ===== KARTU ===== */
        .login-card{
            position:relative; z-index:1;
            width:100%; max-width:940px; min-height:540px;
            display:grid; grid-template-columns:1.05fr .95fr;
            border-radius:22px; overflow:hidden; background:#fff;
            box-shadow:0 40px 90px rgba(2,8,23,.55), 0 2px 8px rgba(2,8,23,.25);
            animation:rise .7s cubic-bezier(.2,.8,.2,1) both;
        }

        @keyframes rise{
            from{ opacity:0; transform:translateY(24px) scale(.985); }
            to{ opacity:1; transform:none; }
        }

        /* ===== PANEL KIRI ===== */
        .welcome-panel{
            position:relative; overflow:hidden; color:#fff;
            padding:34px 38px;
            display:flex; flex-direction:column; justify-content:space-between; gap:24px;
            background:
                radial-gradient(520px 320px at 0% 100%, rgba(34,211,238,.45), transparent 65%),
                linear-gradient(155deg, #081a5c 0%, var(--cobalt) 60%, #1d6bff 100%);
        }

        #net{ position:absolute; inset:0; width:100%; height:100%; z-index:1; }
        .welcome-panel > *:not(#net){ position:relative; z-index:2; }

        .brand-logo{
            width:60px; height:60px; flex:none; margin-bottom:20px;
            filter:drop-shadow(0 6px 12px rgba(0,114,198,.28));
        }
        .brand-logo svg{ width:100%; height:100%; display:block; }

        .welcome-text{ margin:auto 0; }

        .greeting{ color:#bfe3ff; font-size:.95rem; margin-bottom:8px; }

        .welcome-text h1{
            font-size:clamp(1.9rem, 3.2vw, 2.5rem);
            font-weight:800; line-height:1.12; letter-spacing:-.5px;
            margin-bottom:22px; max-width:9em;
        }

        /* slide yang berganti otomatis */
        .slides{ position:relative; min-height:78px; max-width:34ch; }
        .slide{
            position:absolute; inset:0;
            display:flex; gap:14px; align-items:flex-start;
            opacity:0; transform:translateY(8px);
            transition:opacity .5s ease, transform .5s ease;
            pointer-events:none;
        }
        .slide.active{ opacity:1; transform:none; pointer-events:auto; }
        .slide .ico{
            flex:none; width:40px; height:40px; border-radius:12px;
            display:flex; align-items:center; justify-content:center;
            background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.22);
            font-size:16px;
        }
        .slide strong{ display:block; font-size:.98rem; margin-bottom:2px; }
        .slide span{ display:block; color:#cfe0ff; font-size:.86rem; line-height:1.5; }

        .dots{ display:flex; gap:8px; margin-top:16px; }
        .dots button{
            width:8px; height:8px; padding:0; border:0; border-radius:99px;
            background:rgba(255,255,255,.35); cursor:pointer;
            transition:width .3s, background-color .3s;
        }
        .dots button.on{ width:26px; background:#fff; }
        .dots button:focus-visible{ outline:2px solid #fff; outline-offset:3px; }

        .welcome-foot{
            display:flex; justify-content:space-between; align-items:flex-end; gap:12px;
            font-size:.8rem; color:#a9c4f5;
        }
        .clock{ text-align:right; line-height:1.3; }
        .clock b{ display:block; color:#fff; font-size:1.25rem; font-weight:600; font-variant-numeric:tabular-nums; }

        /* ===== PANEL KANAN ===== */
        .form-panel{
            padding:44px 46px;
            display:flex; flex-direction:column; justify-content:center;
            background:linear-gradient(180deg, #ffffff 0%, #f1f5fd 100%);
        }

        .title{ color:var(--ink); font-size:25px; font-weight:700; letter-spacing:-.2px; margin-bottom:6px; }
        .subtitle{ color:var(--muted); margin-bottom:24px; font-size:14px; }

        .alert-err{
            display:flex; gap:10px; align-items:flex-start;
            margin-bottom:16px; padding:11px 14px;
            border-radius:12px; border:1px solid #f4c7c3; background:#fef3f2;
            color:var(--danger); font-size:13.5px; line-height:1.45;
            animation:shake .45s ease;
        }
        .alert-err i{ margin-top:2px; }

        @keyframes shake{
            0%,100%{ transform:translateX(0); }
            20%{ transform:translateX(-7px); }
            40%{ transform:translateX(6px); }
            60%{ transform:translateX(-4px); }
            80%{ transform:translateX(3px); }
        }

        .input-group-modern{ position:relative; margin-bottom:14px; }

        .input-group-modern > i.lead{
            position:absolute; top:50%; left:16px; transform:translateY(-50%);
            color:#94a3b8; font-size:15px; z-index:5; pointer-events:none;
            transition:color .2s;
        }
        .input-group-modern:focus-within > i.lead{ color:var(--cobalt); }

        .form-panel .form-control{
            height:50px; border:1px solid var(--line); border-radius:12px;
            padding-left:46px; padding-right:46px;
            font-size:15px; color:var(--ink); background:#fff;
            box-shadow:0 1px 2px rgba(15,23,42,.05);
            transition:border-color .2s, box-shadow .2s;
        }
        .form-panel .form-control::placeholder{ color:#94a3b8; }
        .form-panel .form-control:hover{ border-color:#b9c6d8; }
        .form-panel .form-control:focus{
            border-color:var(--cobalt);
            box-shadow:0 0 0 4px rgba(18,56,196,.14);
        }

        .toggle-eye{
            position:absolute; right:6px; top:50%; transform:translateY(-50%);
            width:38px; height:38px; border-radius:10px; border:0; background:transparent;
            display:flex; justify-content:center; align-items:center;
            color:var(--muted); cursor:pointer; z-index:5;
            transition:background-color .15s, color .15s;
        }
        .toggle-eye:hover{ background:#e8eef7; color:var(--ink); }
        .toggle-eye:focus-visible{ outline:2px solid var(--cobalt); }

        .caps-hint{
            display:none; align-items:center; gap:8px;
            margin:-4px 0 12px; font-size:12.5px; color:#9a6700;
        }
        .caps-hint.show{ display:flex; }

        .btn-login{
            width:100%; height:50px; margin-top:8px; border:none; border-radius:12px;
            background:linear-gradient(90deg, #081a5c, var(--cobalt) 55%, var(--hp));
            color:#fff; font-size:15.5px; font-weight:600; letter-spacing:.2px;
            display:flex; justify-content:center; align-items:center; gap:10px;
            box-shadow:0 12px 24px rgba(18,56,196,.35);
            transition:transform .2s, box-shadow .2s, filter .2s;
        }
        .btn-login:hover{ transform:translateY(-2px); filter:brightness(1.08); box-shadow:0 16px 30px rgba(18,56,196,.42); }
        .btn-login:active{ transform:none; }
        .btn-login:focus-visible{ outline:3px solid rgba(18,56,196,.4); outline-offset:2px; }
        .btn-login:disabled{ cursor:progress; filter:saturate(.7); transform:none; }

        .footer-text{
            margin-top:26px; padding-top:16px; border-top:1px solid #e6ecf5;
            text-align:center; color:#94a3b8; font-size:12.5px;
        }

        /* ===== HP / TABLET ===== */
        @media(max-width:760px){
            .login-card{ grid-template-columns:1fr; min-height:0; max-width:440px; }
            .welcome-panel{ padding:26px 28px 28px; gap:18px; }
            .brand-logo{ width:48px; height:48px; }
            .welcome-text h1{ font-size:1.65rem; margin-bottom:0; }
            .slides, .dots, .welcome-foot{ display:none; }
            .form-panel{ padding:30px 26px 26px; }
        }

        @media (prefers-reduced-motion: reduce){
            .login-card, .orb, .alert-err{ animation:none; }
            .slide{ transition:none; }
        }
    </style>
</head>

<body>

    <div class="bg" aria-hidden="true">
        <div class="orb a"></div>
        <div class="orb b"></div>
        <div class="orb c"></div>
    </div>

    <div class="login-card">

        <!-- ============ PANEL KIRI ============ -->
        <div class="welcome-panel">

            <canvas id="net" aria-hidden="true"></canvas>

            <div class="welcome-text">
                <div class="greeting" id="greeting">Selamat datang</div>
                <h1>Master Hub System</h1>

                <!-- Ganti teks slide sesuai fitur sebenarnya -->
                <div class="slides" id="slides">
                    <div class="slide active">
                        <div class="ico"><i class="fa-solid fa-gauge-high"></i></div>
                        <div><strong>Semua case dalam satu tempat</strong>
                        <span>Pantau progres service tanpa pindah-pindah halaman.</span></div>
                    </div>
                    <div class="slide">
                        <div class="ico"><i class="fa-solid fa-chart-column"></i></div>
                        <div><strong>Laporan yang jelas</strong>
                        <span>Grafik dan data operasional tersaji ringkas untuk keputusan lebih cepat.</span></div>
                    </div>
                    <div class="slide">
                        <div class="ico"><i class="fa-solid fa-shield-halved"></i></div>
                        <div><strong>Akses sesuai peran</strong>
                        <span>Setiap pengguna masuk dengan akun dan hak aksesnya sendiri.</span></div>
                    </div>
                </div>

                <div class="dots" id="dots" role="tablist" aria-label="Informasi sistem"></div>
            </div>

            <div class="welcome-foot">
                <span>HP Service Center Semarang</span>
                <div class="clock"><b id="clock">--:--</b><span id="date"></span></div>
            </div>

        </div>

        <!-- ============ PANEL KANAN ============ -->
        <div class="form-panel">

            <!-- Logo HP (SVG bawaan, tidak butuh file gambar) -->
            <div class="brand-logo" role="img" aria-label="HP">
                <svg viewBox="0 0 100 100" aria-hidden="true">
                    <defs>
                        <linearGradient id="hpGrad" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0" stop-color="#28b8f5"/>
                            <stop offset="1" stop-color="#0077c8"/>
                        </linearGradient>
                        <mask id="hpMask">
                            <rect width="100" height="100" fill="#000"/>
                            <circle cx="50" cy="50" r="48" fill="#fff"/>
                            <g transform="translate(50 50) skewX(-15) scale(.92) translate(-56 -54)"
                               fill="none" stroke="#000" stroke-width="12" stroke-linejoin="round">
                                <path d="M26 -25 V76 M26 48 C26 41 30 38 37 38 C44 38 48 41 48 48 V76"/>
                                <path d="M66 32 V125 M66 38 H76 Q86 38 86 48 V60 Q86 70 76 70 H66"/>
                            </g>
                        </mask>
                    </defs>
                    <circle cx="50" cy="50" r="48" fill="url(#hpGrad)" mask="url(#hpMask)"/>
                </svg>
            </div>

            <div class="title">Masuk ke akun Anda</div>
            <div class="subtitle">Gunakan username dan password yang terdaftar.</div>

            @php $pesan = $errors->any() ? $errors->first() : session('error'); @endphp
            @if ($pesan)
                <div class="alert-err" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $pesan }}</span>
                </div>
            @endif

            <form method="POST" action="/login" id="loginForm">
                @csrf

                <div class="input-group-modern">
                    <i class="fa-solid fa-user lead"></i>
                    <input type="text" name="name" class="form-control"
                        placeholder="Username" value="{{ old('name') }}"
                        autocomplete="username" autofocus required>
                </div>

                <div class="input-group-modern">
                    <i class="fa-solid fa-lock lead"></i>
                    <input type="password" name="password" id="password" class="form-control"
                        placeholder="Password" autocomplete="current-password" required>
                    <button type="button" class="toggle-eye" id="eyeBtn" aria-label="Tampilkan password">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                </div>

                <div class="caps-hint" id="capsHint">
                    <i class="fa-solid fa-triangle-exclamation"></i> Caps Lock sedang aktif
                </div>

                <button type="submit" class="btn-login" id="btnLogin">
                    <i class="fa-solid fa-right-to-bracket" id="btnIcon"></i>
                    <span id="btnText">Login System</span>
                </button>
            </form>

            <div class="footer-text">© 2026 Master Hub System</div>

        </div>

    </div>

<script>
(function () {

    /* ---------- Sapaan & jam sesuai waktu ---------- */
    var hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    var bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

    function tick() {
        var d = new Date(), h = d.getHours();
        var sapa = h < 11 ? 'Selamat pagi' : h < 15 ? 'Selamat siang' : h < 18 ? 'Selamat sore' : 'Selamat malam';
        document.getElementById('greeting').textContent = sapa + ', silakan masuk';
        document.getElementById('clock').textContent =
            String(h).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
        document.getElementById('date').textContent =
            hari[d.getDay()] + ', ' + d.getDate() + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear();
    }
    tick(); setInterval(tick, 15000);

    /* ---------- Slide otomatis ---------- */
    var slides = document.querySelectorAll('.slide'),
        dotsBox = document.getElementById('dots'),
        cur = 0, timer;

    slides.forEach(function (_, i) {
        var b = document.createElement('button');
        b.type = 'button';
        b.setAttribute('aria-label', 'Slide ' + (i + 1));
        b.onclick = function () { show(i); restart(); };
        dotsBox.appendChild(b);
    });

    function show(n) {
        cur = n;
        slides.forEach(function (s, i) { s.classList.toggle('active', i === n); });
        dotsBox.querySelectorAll('button').forEach(function (b, i) { b.classList.toggle('on', i === n); });
    }
    function restart() {
        clearInterval(timer);
        timer = setInterval(function () { show((cur + 1) % slides.length); }, 5000);
    }
    show(0); restart();

    /* ---------- Animasi jaringan di panel kiri ---------- */
    var c = document.getElementById('net'), x = c.getContext('2d'),
        reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        w, h, pts = [];

    function size() {
        var r = c.parentNode.getBoundingClientRect(), d = window.devicePixelRatio || 1;
        w = r.width; h = r.height;
        c.width = w * d; c.height = h * d;
        x.setTransform(d, 0, 0, d, 0, 0);
        var n = Math.min(60, Math.round(w * h / 9000));
        pts = [];
        for (var i = 0; i < n; i++) {
            pts.push({ x: Math.random() * w, y: Math.random() * h,
                       vx: (Math.random() - .5) * .35, vy: (Math.random() - .5) * .35 });
        }
    }

    function draw() {
        x.clearRect(0, 0, w, h);
        var i, j, p, q, dx, dy, dd;
        for (i = 0; i < pts.length; i++) {
            p = pts[i];
            p.x += p.vx; p.y += p.vy;
            if (p.x < 0 || p.x > w) p.vx *= -1;
            if (p.y < 0 || p.y > h) p.vy *= -1;
            x.fillStyle = 'rgba(255,255,255,.75)';
            x.beginPath(); x.arc(p.x, p.y, 1.6, 0, 6.283); x.fill();
        }
        for (i = 0; i < pts.length; i++) {
            for (j = i + 1; j < pts.length; j++) {
                p = pts[i]; q = pts[j];
                dx = p.x - q.x; dy = p.y - q.y; dd = dx * dx + dy * dy;
                if (dd < 9500) {
                    x.strokeStyle = 'rgba(125,211,252,' + (.4 * (1 - dd / 9500)) + ')';
                    x.lineWidth = 1;
                    x.beginPath(); x.moveTo(p.x, p.y); x.lineTo(q.x, q.y); x.stroke();
                }
            }
        }
        if (!reduce) requestAnimationFrame(draw);
    }

    size(); draw();
    window.addEventListener('resize', function () { size(); if (reduce) draw(); });

    /* ---------- Password: tampil/sembunyi + Caps Lock ---------- */
    var pw = document.getElementById('password'),
        eye = document.getElementById('eyeIcon'),
        eyeBtn = document.getElementById('eyeBtn'),
        caps = document.getElementById('capsHint');

    eyeBtn.addEventListener('click', function () {
        var hide = pw.type === 'password';
        pw.type = hide ? 'text' : 'password';
        eye.className = hide ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        eyeBtn.setAttribute('aria-label', hide ? 'Sembunyikan password' : 'Tampilkan password');
    });

    function cekCaps(e) {
        if (e.getModifierState) caps.classList.toggle('show', e.getModifierState('CapsLock'));
    }
    pw.addEventListener('keyup', cekCaps);
    pw.addEventListener('keydown', cekCaps);
    pw.addEventListener('blur', function () { caps.classList.remove('show'); });

    /* ---------- Tombol loading saat dikirim ---------- */
    var form = document.getElementById('loginForm'),
        btn = document.getElementById('btnLogin'),
        btnIcon = document.getElementById('btnIcon'),
        btnText = document.getElementById('btnText');

    form.addEventListener('submit', function () {
        btn.disabled = true;
        btnIcon.className = 'fa-solid fa-circle-notch fa-spin';
        btnText.textContent = 'Memeriksa akun...';
    });

    window.addEventListener('pageshow', function () {   // tombol "Back" di browser
        btn.disabled = false;
        btnIcon.className = 'fa-solid fa-right-to-bracket';
        btnText.textContent = 'Login System';
    });

})();
</script>
</body>
</html>
