<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIAKAD MTs - Sistem Informasi Akademik Madrasah</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
      window.onerror = function(msg, url, line, col, error) {
        var el = document.getElementById('debug-err');
        if (!el) {
          el = document.createElement('div');
          el.id = 'debug-err';
          el.style.cssText = 'position:fixed;top:0;left:0;right:0;background:#ef4444;color:white;padding:16px;z-index:999999;font-family:sans-serif;font-size:13px;word-break:break-all;box-shadow:0 10px 25px rgba(0,0,0,0.5);';
          document.body.appendChild(el);
        }
        el.innerHTML = '⚠️ <b>JS ERROR DI HP:</b> ' + msg + '<br><small style="opacity:0.8">' + url + ' (Line:' + line + ')</small>';
      };
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <div id="app">
        <div style="min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #0f172a; color: #ffffff; font-family: system-ui, -apple-system, sans-serif; text-align: center; padding: 24px; box-sizing: border-box;">
            <div style="width: 52px; height: 52px; border: 4px solid rgba(99, 102, 241, 0.2); border-top-color: #6366f1; border-radius: 50%; animation: spin-loader 0.8s linear infinite; margin-bottom: 20px;"></div>
            <h2 style="margin: 0; font-size: 20px; font-weight: 800; tracking-tight; font-family: sans-serif;">Memuat Aplikasi Sekolah...</h2>
            <p style="margin: 8px 0 0 0; font-size: 13px; color: #94a3b8;">Mohon tunggu, sedang menghubungkan ke server lokal...</p>
        </div>
        <style>
            @keyframes spin-loader { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        </style>
    </div>
</body>
</html>
