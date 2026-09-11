<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-6">Invite a staff member</h1>

<form method="post" action="/manage/staff" class="bg-white border border-line rounded-xl2 p-6 max-w-xl space-y-4">
  <?= csrf_field() ?>

  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Full name</label>
    <input name="full_name" required class="w-full border border-linestrong rounded-xl px-4 py-3">
  </div>

  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Email</label>
    <input type="email" name="email" required class="w-full border border-linestrong rounded-xl px-4 py-3">
  </div>

  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Mobile number</label>
    <input name="phone" class="w-full border border-linestrong rounded-xl px-4 py-3">
  </div>

  <button class="bg-ink text-white font-bold px-5 py-3 rounded-xl hover:brightness-110">Send invite</button>
</form>

<?= $this->endSection() ?>
