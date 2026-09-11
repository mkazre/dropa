<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-6">Parcels</h1>

<div class="bg-white border border-line rounded-xl2 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-[#FCFBF7] text-inksoft text-xs font-bold uppercase">
      <tr>
        <th class="text-left px-5 py-3">Sender</th>
        <th class="text-left px-5 py-3">Size</th>
        <th class="text-left px-5 py-3">Deposited</th>
        <th class="text-left px-5 py-3">Collected</th>
        <th class="text-left px-5 py-3">Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($parcels as $p): $r = $reservationsById[$p['reservation_id']] ?? null; ?>
      <tr class="border-t border-line">
        <td class="px-5 py-3 font-semibold"><?= esc($p['sender_name'] ?? 'Unknown') ?></td>
        <td class="px-5 py-3 text-muted"><?= esc($r['size_requested'] ?? '—') ?></td>
        <td class="px-5 py-3 text-muted"><?= esc($p['deposited_at'] ?? '—') ?></td>
        <td class="px-5 py-3 text-muted"><?= esc($p['collected_at'] ?? '—') ?></td>
        <td class="px-5 py-3">
          <span class="text-xs font-bold px-2 py-1 rounded-full <?= $p['status'] === 'collected' ? 'bg-oksoft text-ok' : 'bg-[#FFFBEC] text-signaldeep' ?>">
            <?= esc(str_replace('_', ' ', $p['status'])) ?>
          </span>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($parcels)): ?>
      <tr><td colspan="5" class="px-5 py-8 text-center text-muted">No parcels yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
