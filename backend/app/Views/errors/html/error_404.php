<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page not found — Dropa</title>
    <style>
        body {
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FCFBF7;
            font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #211E17;
        }
        .wrap { text-align: center; padding: 2rem; }
        .brand { display: inline-flex; align-items: center; gap: 8px; font-weight: 800; font-size: 1.1rem; margin-bottom: 2rem; }
        .dot { width: 12px; height: 12px; border-radius: 3px; background: #FFC400; display: inline-block; }
        h1 { font-size: 2.25rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 0.5rem; }
        p { color: #8C887C; margin: 0 0 1.5rem; }
        a.home {
            display: inline-block; background: #211E17; color: #fff; font-weight: 700;
            padding: 0.75rem 1.5rem; border-radius: 12px; text-decoration: none;
        }
        a.home:hover { filter: brightness(1.1); }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="brand"><span class="dot"></span>Dropa</div>
        <h1>404 — Page not found</h1>
        <p>
            <?php if (ENVIRONMENT !== 'production') : ?>
                <?= nl2br(esc($message)) ?>
            <?php else : ?>
                That page doesn&rsquo;t exist, or has moved.
            <?php endif; ?>
        </p>
        <a class="home" href="/login">Go to login</a>
    </div>
</body>
</html>
