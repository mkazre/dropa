<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-2">Manual payments</h1>
<p class="text-muted text-sm mb-6">Tenants who paid offline upload proof here for you to approve.</p>

<div class="bg-white border border-line rounded-xl2 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-[#FCFBF7] text-inksoft text-xs font-bold uppercase">
      <tr>
        <th class="text-left px-5 py-3">Reservation</th>
        <th class="text-left px-5 py-3">Amount</th>
        <th class="text-left px-5 py-3">Proof</th>
        <th class="text-left px-5 py-3">Status</th>
        <th class="px-5 py-3"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($payments as $p): $r = $reservationsById[$p['reservation_id']] ?? null; ?>
      <tr class="border-t border-line">
        <td class="px-5 py-3 font-semibold">#<?= $p['reservation_id'] ?> — <?= esc($r['size_requested'] ?? '—') ?></td>
        <td class="px-5 py-3 text-muted">R<?= esc(number_format((float) $p['amount'], 2)) ?></td>
        <td class="px-5 py-3">
          <?php if ($p['proof_of_payment_url']): ?>
            <a href="/<?= esc($p['proof_of_payment_url']) ?>" target="_blank" class="text-signal-deep font-bold">View file</a>
          <?php else: ?>
            <span class="text-muted">Not uploaded yet</span>
          <?php endif; ?>
        </td>
        <td class="px-5 py-3">
          <span class="text-xs font-bold px-2 py-1 rounded-full <?= $p['status'] === 'paid' ? 'bg-oksoft text-ok' : 'bg-[#FFFBEC] text-signaldeep' ?>"><?= esc($p['status']) ?></span>
        </td>
        <td class="px-5 py-3 text-right">
          <?php if ($p['status'] === 'pending' && $p['proof_of_payment_url']): ?>
          <form method="post" action="/manage/payments/<?= $p['id'] ?>/approve">
            <?= csrf_field() ?>
            <button class="text-ok font-bold hover:brightness-110">Approve</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($payments)): ?>
      <tr><td colspan="5" class="px-5 py-8 text-center text-muted">No manual payments yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
