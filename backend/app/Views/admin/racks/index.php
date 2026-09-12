<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<a href="/admin/properties" class="text-inksoft text-sm font-bold hover:text-ink">&larr; Properties</a>
<h1 class="text-2xl font-extrabold tracking-tight mt-2 mb-2">Lockers — <?= esc($property['name']) ?></h1>
<p class="text-muted text-sm mb-6">Physical locker racks installed at this property. Add a rack, then add its individual lockers.</p>

<form method="post" action="/admin/properties/<?= $property['id'] ?>/racks" class="bg-white border border-line rounded-xl2 p-6 max-w-xl mb-8 space-y-4">
  <?= csrf_field() ?>
  <p class="font-bold">Add a rack</p>
  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Rack name</label>
    <input name="name" required placeholder="e.g. Lobby bank A" class="w-full border border-linestrong rounded-xl px-4 py-3">
  </div>
  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Location</label>
    <input name="location_desc" placeholder="e.g. Ground floor, main entrance" class="w-full border border-linestrong rounded-xl px-4 py-3">
  </div>
  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Hardware provider</label>
    <select name="hardware_provider" class="w-full border border-linestrong rounded-xl px-4 py-3">
      <option value="mock">Mock (no physical hardware yet)</option>
      <option value="hivebox">Hive-Box</option>
    </select>
  </div>
  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">External rack ID (from provider, optional)</label>
    <input name="external_rack_id" class="w-full border border-linestrong rounded-xl px-4 py-3">
  </div>
  <button class="bg-ink text-white font-bold px-5 py-3 rounded-xl hover:brightness-110">Add rack</button>
</form>

<div class="bg-white border border-line rounded-xl2 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-[#FCFBF7] text-inksoft text-xs font-bold uppercase">
      <tr>
        <th class="text-left px-5 py-3">Rack</th>
        <th class="text-left px-5 py-3">Provider</th>
        <th class="text-left px-5 py-3">Status</th>
        <th class="text-left px-5 py-3">Lockers</th>
        <th class="px-5 py-3"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($racks as $rack): ?>
      <tr class="border-t border-line">
        <td class="px-5 py-3 font-semibold"><?= esc($rack['name']) ?> <span class="text-muted text-xs">— <?= esc($rack['location_desc'] ?? '') ?></span></td>
        <td class="px-5 py-3"><span class="text-xs font-bold px-2 py-1 rounded-full bg-cream"><?= esc(strtoupper($rack['hardware_provider'])) ?></span></td>
        <td class="px-5 py-3"><?= esc($rack['status']) ?></td>
        <td class="px-5 py-3"><?= (int) $rack['locker_count'] ?></td>
        <td class="px-5 py-3 text-right space-x-3">
          <a href="/admin/racks/<?= $rack['id'] ?>/lockers" class="text-inksoft font-bold hover:text-ink">Manage lockers</a>
          <form method="post" action="/admin/racks/<?= $rack['id'] ?>/delete" class="inline" onsubmit="return confirm('Remove this rack and all its lockers?')">
            <?= csrf_field() ?>
            <button class="text-alert font-bold">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($racks)): ?>
      <tr><td colspan="5" class="px-5 py-8 text-center text-muted">No racks yet — add one above.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
