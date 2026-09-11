<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-2">Broadcast to your residents</h1>
<p class="text-muted text-sm mb-6">Sends a push + email to every tenant at your property.</p>

<form method="post" action="/manage/broadcast/send" class="bg-white border border-line rounded-xl2 p-6 max-w-xl space-y-4">
  <?= csrf_field() ?>

  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Title</label>
    <input name="title" required class="w-full border border-linestrong rounded-xl px-4 py-3">
  </div>

  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Message</label>
    <textarea name="body" rows="4" required class="w-full border border-linestrong rounded-xl px-4 py-3"></textarea>
  </div>

  <button class="bg-ink text-white font-bold px-5 py-3 rounded-xl hover:brightness-110">Send broadcast</button>
</form>

<?= $this->endSection() ?>
