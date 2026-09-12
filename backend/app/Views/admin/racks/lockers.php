<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<a href="/admin/properties/<?= $property['id'] ?>/lockers" class="text-inksoft text-sm font-bold hover:text-ink">&larr; <?= esc($property['name']) ?> racks</a>
<h1 class="text-2xl font-extrabold tracking-tight mt-2 mb-2">Lockers — <?= esc($rack['name']) ?></h1>
<p class="text-muted text-sm mb-6">Individual compartments in this rack.</p>

<form method="post" action="/admin/racks/<?= $rack['id'] ?>/lockers" class="bg-white border border-line rounded-xl2 p-6 max-w-xl mb-8 space-y-4">
  <?= csrf_field() ?>
  <p class="font-bold">Add a locker</p>
  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Label</label>
    <input name="label" required placeholder="e.g. A1" class="w-full border border-linestrong rounded-xl px-4 py-3">
  </div>
  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Size</label>
    <select name="size" class="w-full border border-linestrong rounded-xl px-4 py-3">
      <?php foreach (['S', 'M', 'L', 'XL'] as $s): ?>
        <option value="<?= $s ?>" <?= $s === 'M' ? 'selected' : '' ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">External compartment ID (from provider, optional)</label>
    <input name="external_compartment_id" class="w-full border border-linestrong rounded-xl px-4 py-3">
  </div>
  <button class="bg-ink text-white font-bold px-5 py-3 rounded-xl hover:brightness-110">Add locker</button>
</form>

<div class="bg-white border border-line rounded-xl2 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-[#FCFBF7] text-inksoft text-xs font-bold uppercase">
      <tr>
        <th class="text-left px-5 py-3">Label</th>
        <th class="text-left px-5 py-3">Size</th>
        <th class="text-left px-5 py-3">Status</th>
        <th class="px-5 py-3"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($lockers as $locker): ?>
      <tr class="border-t border-line">
        <td class="px-5 py-3 font-semibold"><?= esc($locker['label']) ?></td>
        <td class="px-5 py-3"><?= esc($locker['size']) ?></td>
        <td class="px-5 py-3">
          <span class="text-xs font-bold px-2 py-1 rounded-full <?= $locker['status'] === 'available' ? 'bg-oksoft text-ok' : 'bg-cream' ?>"><?= esc($locker['status']) ?></span>
        </td>
        <td class="px-5 py-3 text-right">
          <form method="post" action="/admin/lockers/<?= $locker['id'] ?>/delete" class="inline" onsubmit="return confirm('Remove this locker?')">
            <?= csrf_field() ?>
            <button class="text-alert font-bold">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($lockers)): ?>
      <tr><td colspan="4" class="px-5 py-8 text-center text-muted">No lockers yet — add one above.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
