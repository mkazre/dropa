<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="flex items-center justify-between mb-6">
  <h1 class="text-2xl font-extrabold tracking-tight">Properties</h1>
  <a href="/admin/properties/new" class="bg-signal text-[#241f00] font-bold px-4 py-2.5 rounded-xl hover:brightness-95">+ New property</a>
</div>

<div class="bg-white border border-line rounded-xl2 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-[#FCFBF7] text-inksoft text-xs font-bold uppercase">
      <tr>
        <th class="text-left px-5 py-3">Name</th>
        <th class="text-left px-5 py-3">Type</th>
        <th class="text-left px-5 py-3">Address</th>
        <th class="text-left px-5 py-3">Status</th>
        <th class="px-5 py-3"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($properties as $p): ?>
      <tr class="border-t border-line">
        <td class="px-5 py-3 font-semibold"><?= esc($p['name']) ?></td>
        <td class="px-5 py-3 text-muted"><?= esc($p['type']) ?></td>
        <td class="px-5 py-3 text-muted"><?= esc($p['address'] ?? '—') ?></td>
        <td class="px-5 py-3">
          <span class="text-xs font-bold px-2 py-1 rounded-full <?= $p['status'] === 'active' ? 'bg-oksoft text-ok' : 'bg-[#FBEAEA] text-alert' ?>"><?= esc($p['status']) ?></span>
        </td>
        <td class="px-5 py-3 text-right space-x-3">
          <a href="/admin/properties/<?= $p['id'] ?>/lockers" class="text-inksoft font-bold hover:text-ink">Lockers</a>
          <a href="/admin/properties/<?= $p['id'] ?>/edit" class="text-inksoft font-bold hover:text-ink">Edit</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($properties)): ?>
      <tr><td colspan="5" class="px-5 py-8 text-center text-muted">No properties yet — add your first complex or estate.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
