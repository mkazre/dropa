<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($title ?? 'Dropa Admin') ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          ink: '#211E17',
          inksoft: '#4A4638',
          paper: '#FFFFFF',
          signal: '#FFC400',
          signaldeep: '#E0A500',
          line: '#E7E4DB',
          linestrong: '#D8D3C6',
          ok: '#1F8A5B',
          oksoft: '#E6F4EC',
          alert: '#D64545',
          muted: '#8C887C',
        },
        borderRadius: { xl2: '20px' },
      }
    }
  }
</script>
</head>
<body class="bg-[#FCFBF7] text-ink font-sans min-h-screen">
  <div class="flex min-h-screen">
    <aside class="w-64 bg-ink text-white flex-none flex flex-col">
      <div class="px-6 py-6 flex items-center gap-2 border-b border-white/10">
        <span class="w-4 h-4 rounded bg-signal inline-block"></span>
        <span class="font-extrabold text-xl tracking-tight">Dropa</span>
        <span class="text-xs text-white/40 ml-1"><?= esc($panelLabel ?? '') ?></span>
      </div>
      <nav class="flex-1 py-4 px-3 space-y-1 text-sm font-semibold">
        <?= $nav ?? '' ?>
      </nav>
      <div class="px-6 py-4 border-t border-white/10 text-xs text-white/50">
        <?= esc(auth()->user()->email ?? '') ?>
        <form method="post" action="/logout" class="mt-2">
          <?= csrf_field() ?>
          <button class="text-signal font-bold">Log out</button>
        </form>
      </div>
    </aside>
    <main class="flex-1 p-8">
      <?php if (session('error')): ?>
        <div class="mb-6 rounded-2xl border border-alert/30 bg-red-50 text-alert px-4 py-3 text-sm font-semibold"><?= esc(session('error')) ?></div>
      <?php endif; ?>
      <?php if (session('success')): ?>
        <div class="mb-6 rounded-2xl border border-ok/30 bg-oksoft text-ok px-4 py-3 text-sm font-semibold"><?= esc(session('success')) ?></div>
      <?php endif; ?>
      <?= $this->renderSection('content') ?>
    </main>
  </div>
</body>
</html>
