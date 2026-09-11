<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-6">Bulk import tenants</h1>

<form method="post" action="/manage/tenants/import" enctype="multipart/form-data" class="bg-white border border-line rounded-xl2 p-6 max-w-xl space-y-4">
  <?= csrf_field() ?>

  <p class="text-sm text-muted">
    Upload a CSV with a header row: <code class="bg-cream px-1.5 py-0.5 rounded font-mono text-xs">unit_number,full_name,email,phone</code>
    (phone is optional). Each row is invited exactly like the single-invite form — existing emails are skipped, not overwritten.
  </p>

  <input type="file" name="csv" accept=".csv,text/csv" required class="block w-full text-sm border border-linestrong rounded-xl px-4 py-3">

  <button class="bg-ink text-white font-bold px-5 py-3 rounded-xl hover:brightness-110">Import</button>
</form>

<?= $this->endSection() ?>
