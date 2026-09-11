<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-2">Payment settings</h1>
<p class="text-muted text-sm mb-6">Switch payment methods on or off for your property. Merchant credentials for online gateways are managed platform-wide.</p>

<form method="post" action="/manage/gateways" class="bg-white border border-line rounded-xl2 p-6 max-w-xl space-y-5">
  <?= csrf_field() ?>

  <?php foreach (['ozow' => 'Ozow (instant EFT)', 'payfast' => 'PayFast (cards)', 'manual' => 'Manual / offline payment'] as $key => $label): ?>
  <label class="flex items-start gap-3 border border-line rounded-xl p-4 <?= $gateways[$key]['available_globally'] ? 'cursor-pointer' : 'opacity-50' ?>">
    <input type="checkbox" name="enabled[]" value="<?= $key ?>" <?= ! empty($gateways[$key]['enabled']) ? 'checked' : '' ?>
           <?= $gateways[$key]['available_globally'] ? '' : 'disabled' ?> class="mt-1 w-4 h-4 accent-signal">
    <span>
      <span class="block font-bold"><?= $label ?></span>
      <span class="block text-xs text-muted mt-0.5">
        <?= $gateways[$key]['available_globally'] ? 'Available for your property.' : 'Not enabled platform-wide yet — ask Dropa support to activate it.' ?>
      </span>
    </span>
  </label>
  <?php endforeach; ?>

  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Manual payment instructions for your tenants</label>
    <textarea name="manual_instructions" rows="3" class="w-full border border-linestrong rounded-xl px-4 py-3"><?= esc($gateways['manual']['instructions'] ?? '') ?></textarea>
  </div>

  <button class="bg-ink text-white font-bold px-5 py-3 rounded-xl hover:brightness-110">Save settings</button>
</form>

<?= $this->endSection() ?>
