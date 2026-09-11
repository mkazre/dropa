<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-2">Platform default pricing</h1>
<p class="text-muted text-sm mb-6">Every property inherits these rates unless it sets its own. "Free hours" is how long a reservation holds before the daily rate starts accruing.</p>

<form method="post" action="/admin/pricing" class="bg-white border border-line rounded-xl2 p-6 max-w-xl">
  <?= csrf_field() ?>
  <table class="w-full text-sm">
    <thead class="text-inksoft text-xs font-bold uppercase">
      <tr>
        <th class="text-left pb-3">Size</th>
        <th class="text-left pb-3">Free hours</th>
        <th class="text-left pb-3">Daily rate (R)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($sizes as $size): $r = $rules[$size] ?? null; ?>
      <tr class="border-t border-line">
        <td class="py-3 font-bold"><?= $size ?></td>
        <td class="py-3 pr-4">
          <input type="number" name="free_hours_<?= $size ?>" value="<?= esc((string) ($r['free_hours'] ?? 48)) ?>"
                 class="w-24 border border-linestrong rounded-lg px-3 py-2">
        </td>
        <td class="py-3">
          <input type="number" step="0.01" name="daily_rate_<?= $size ?>" value="<?= esc((string) ($r['daily_rate'] ?? 0)) ?>"
                 class="w-28 border border-linestrong rounded-lg px-3 py-2">
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <button class="mt-6 bg-ink text-white font-bold px-5 py-3 rounded-xl hover:brightness-110">Save pricing</button>
</form>

<?= $this->endSection() ?>
