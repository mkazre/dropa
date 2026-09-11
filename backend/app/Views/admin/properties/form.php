<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-6"><?= $property ? 'Edit property' : 'New property' ?></h1>

<form method="post" action="<?= $property ? "/admin/properties/{$property['id']}" : '/admin/properties' ?>"
      class="bg-white border border-line rounded-xl2 p-6 max-w-xl space-y-4">
  <?= csrf_field() ?>

  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Property name</label>
    <input name="name" required value="<?= esc($property['name'] ?? '') ?>"
           class="w-full border border-linestrong rounded-xl px-4 py-3 focus:outline-none focus:border-ink" placeholder="e.g. Rosebank Heights Estate">
  </div>

  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Type</label>
    <select name="type" class="w-full border border-linestrong rounded-xl px-4 py-3">
      <option value="complex" <?= ($property['type'] ?? 'complex') === 'complex' ? 'selected' : '' ?>>Complex / estate</option>
      <option value="public_site" <?= ($property['type'] ?? '') === 'public_site' ? 'selected' : '' ?>>Public site</option>
    </select>
  </div>

  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Address</label>
    <input name="address" value="<?= esc($property['address'] ?? '') ?>"
           class="w-full border border-linestrong rounded-xl px-4 py-3 focus:outline-none focus:border-ink">
  </div>

  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Reservation hold window (hours)</label>
    <input type="number" name="reservation_hold_hours" value="<?= esc((string) ($property['reservation_hold_hours'] ?? 48)) ?>"
           class="w-full border border-linestrong rounded-xl px-4 py-3 focus:outline-none focus:border-ink">
  </div>

  <?php if ($property): ?>
  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Status</label>
    <select name="status" class="w-full border border-linestrong rounded-xl px-4 py-3">
      <?php foreach (['pending', 'active', 'suspended'] as $s): ?>
        <option value="<?= $s ?>" <?= ($property['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>

  <button class="bg-ink text-white font-bold px-5 py-3 rounded-xl hover:brightness-110"><?= $property ? 'Save changes' : 'Create property' ?></button>
</form>

<?= $this->endSection() ?>
