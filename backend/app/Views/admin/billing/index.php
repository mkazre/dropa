<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-6">Billing</h1>

<h2 class="text-sm font-bold text-inksoft uppercase mb-2">Subscription fees</h2>
<div class="bg-white border border-line rounded-xl2 overflow-hidden mb-8">
  <table class="w-full text-sm">
    <thead class="bg-[#FCFBF7] text-inksoft text-xs font-bold uppercase">
      <tr><th class="text-left px-5 py-3">Property</th><th class="text-left px-5 py-3">Monthly fee (R)</th><th class="px-5 py-3"></th></tr>
    </thead>
    <tbody>
      <?php foreach ($properties as $p): ?>
      <tr class="border-t border-line">
        <td class="px-5 py-3 font-semibold"><?= esc($p['name']) ?></td>
        <td class="px-5 py-3" colspan="2">
          <form method="post" action="/admin/billing/<?= $p['id'] ?>/fee" class="flex items-center gap-2">
            <?= csrf_field() ?>
            <input type="number" step="0.01" name="subscription_monthly_fee" value="<?= esc((string) $p['subscription_monthly_fee']) ?>"
                   class="w-32 border border-linestrong rounded-lg px-3 py-2">
            <button class="text-signaldeep font-bold text-xs">Save</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="flex items-center justify-between mb-2">
  <h2 class="text-sm font-bold text-inksoft uppercase">Invoices</h2>
  <form method="post" action="/admin/billing/generate">
    <?= csrf_field() ?>
    <button class="bg-signal text-[#241f00] font-bold px-4 py-2 rounded-xl text-sm hover:brightness-95">Generate this month's invoices</button>
  </form>
</div>
<div class="bg-white border border-line rounded-xl2 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-[#FCFBF7] text-inksoft text-xs font-bold uppercase">
      <tr>
        <th class="text-left px-5 py-3">Property</th>
        <th class="text-left px-5 py-3">Period</th>
        <th class="text-left px-5 py-3">Amount</th>
        <th class="text-left px-5 py-3">Status</th>
        <th class="px-5 py-3"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($invoices as $inv): ?>
      <tr class="border-t border-line">
        <td class="px-5 py-3 font-semibold"><?= esc($propertiesById[$inv['property_id']]['name'] ?? '—') ?></td>
        <td class="px-5 py-3 text-muted"><?= esc(date('F Y', strtotime($inv['period_month']))) ?></td>
        <td class="px-5 py-3">R<?= esc(number_format((float) $inv['amount'], 2)) ?></td>
        <td class="px-5 py-3">
          <span class="text-xs font-bold px-2 py-1 rounded-full <?= $inv['status'] === 'paid' ? 'bg-oksoft text-ok' : 'bg-[#FFFBEC] text-signaldeep' ?>"><?= esc($inv['status']) ?></span>
        </td>
        <td class="px-5 py-3 text-right">
          <?php if ($inv['status'] === 'pending'): ?>
          <form method="post" action="/admin/billing/<?= $inv['id'] ?>/paid">
            <?= csrf_field() ?>
            <button class="text-ok font-bold hover:brightness-110">Mark paid</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($invoices)): ?>
      <tr><td colspan="5" class="px-5 py-8 text-center text-muted">No invoices generated yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
