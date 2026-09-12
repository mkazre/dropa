<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-2">Hardware fleet</h1>
<p class="text-muted text-sm mb-6">Every locker rack across every property, whichever provider runs it.</p>

<div class="grid grid-cols-3 gap-4 mb-8">
  <?php foreach ($lastWebhook as $provider => $when): ?>
  <div class="bg-white border border-line rounded-xl2 p-4">
    <div class="text-xs font-bold text-inksoft uppercase"><?= esc(ucfirst($provider)) ?> — last callback</div>
    <div class="text-sm font-semibold mt-1"><?= $when ? esc($when) : 'Never received' ?></div>
  </div>
  <?php endforeach; ?>
</div>

<div class="bg-white border border-line rounded-xl2 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-[#FCFBF7] text-inksoft text-xs font-bold uppercase">
      <tr>
        <th class="text-left px-5 py-3">Property</th>
        <th class="text-left px-5 py-3">Rack</th>
        <th class="text-left px-5 py-3">Provider</th>
        <th class="text-left px-5 py-3">Status</th>
        <th class="text-left px-5 py-3">Lockers</th>
        <th class="px-5 py-3"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($racks as $rack): $counts = $lockerCounts[$rack['id']] ?? []; $property = $properties[$rack['property_id']] ?? null; ?>
      <tr class="border-t border-line">
        <td class="px-5 py-3 font-semibold"><?= esc($property['name'] ?? '—') ?></td>
        <td class="px-5 py-3"><?= esc($rack['name']) ?> <span class="text-muted text-xs">— <?= esc($rack['location_desc'] ?? '') ?></span></td>
        <td class="px-5 py-3">
          <span class="text-xs font-bold px-2 py-1 rounded-full bg-cream"><?= esc(strtoupper($rack['hardware_provider'])) ?></span>
        </td>
        <td class="px-5 py-3">
          <span class="text-xs font-bold px-2 py-1 rounded-full <?= $rack['status'] === 'online' ? 'bg-oksoft text-ok' : 'bg-[#FBEAEA] text-alert' ?>">
            <?= esc($rack['status']) ?>
          </span>
        </td>
        <td class="px-5 py-3 text-xs text-inksoft">
          <?= (int) ($counts['available'] ?? 0) ?> available ·
          <?= (int) ($counts['occupied'] ?? 0) ?> occupied ·
          <?= (int) ($counts['reserved'] ?? 0) ?> reserved ·
          <?= (int) ($counts['out_of_service'] ?? 0) ?> out of service
        </td>
        <td class="px-5 py-3 text-right">
          <a href="/admin/racks/<?= $rack['id'] ?>/lockers" class="text-inksoft font-bold hover:text-ink">Manage</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($racks)): ?>
      <tr><td colspan="6" class="px-5 py-8 text-center text-muted">No locker racks registered yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
