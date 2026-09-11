<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-2">Payment gateways</h1>
<p class="text-muted text-sm mb-6">Enabled here by default for every property. A Body Corporate Admin can still switch these off for their own property in the Manage panel — the merchant credentials below stay platform-wide.</p>

<form method="post" action="/admin/gateways" class="bg-white border border-line rounded-xl2 p-6 max-w-xl space-y-6">
  <?= csrf_field() ?>

  <label class="flex items-start gap-3 border border-line rounded-xl p-4 cursor-pointer">
    <input type="checkbox" name="enabled[]" value="ozow" <?= ! empty($gateways['ozow']['enabled']) ? 'checked' : '' ?> class="mt-1 w-4 h-4 accent-signal">
    <span class="flex-1">
      <span class="block font-bold">Ozow (instant EFT)</span>
      <span class="block text-xs text-muted mt-0.5 mb-3">Hosted payment page — redirects the tenant to their bank.</span>
      <span class="grid grid-cols-2 gap-2">
        <input name="ozow_site_code" placeholder="Site code" value="<?= esc($gateways['ozow']['credentials']['site_code'] ?? '') ?>" class="border border-linestrong rounded-lg px-3 py-2 text-sm">
        <input name="ozow_private_key" type="password" placeholder="Private key" value="<?= esc($gateways['ozow']['credentials']['private_key'] ?? '') ?>" class="border border-linestrong rounded-lg px-3 py-2 text-sm">
      </span>
      <label class="flex items-center gap-2 text-xs text-muted mt-2">
        <input type="checkbox" name="ozow_is_test" <?= ($gateways['ozow']['credentials']['is_test'] ?? 'true') === 'true' ? 'checked' : '' ?> class="accent-signal">
        Test mode
      </label>
    </span>
  </label>

  <label class="flex items-start gap-3 border border-line rounded-xl p-4 cursor-pointer">
    <input type="checkbox" name="enabled[]" value="payfast" <?= ! empty($gateways['payfast']['enabled']) ? 'checked' : '' ?> class="mt-1 w-4 h-4 accent-signal">
    <span class="flex-1">
      <span class="block font-bold">PayFast (cards)</span>
      <span class="block text-xs text-muted mt-0.5 mb-3">Hosted payment page for card payments.</span>
      <span class="grid grid-cols-2 gap-2">
        <input name="payfast_merchant_id" placeholder="Merchant ID" value="<?= esc($gateways['payfast']['credentials']['merchant_id'] ?? '') ?>" class="border border-linestrong rounded-lg px-3 py-2 text-sm">
        <input name="payfast_merchant_key" type="password" placeholder="Merchant key" value="<?= esc($gateways['payfast']['credentials']['merchant_key'] ?? '') ?>" class="border border-linestrong rounded-lg px-3 py-2 text-sm">
        <input name="payfast_passphrase" type="password" placeholder="Passphrase (optional)" value="<?= esc($gateways['payfast']['credentials']['passphrase'] ?? '') ?>" class="border border-linestrong rounded-lg px-3 py-2 text-sm col-span-2">
      </span>
      <label class="flex items-center gap-2 text-xs text-muted mt-2">
        <input type="checkbox" name="payfast_sandbox" <?= ($gateways['payfast']['credentials']['sandbox'] ?? 'true') === 'true' ? 'checked' : '' ?> class="accent-signal">
        Sandbox mode
      </label>
    </span>
  </label>

  <label class="flex items-start gap-3 border border-line rounded-xl p-4 cursor-pointer">
    <input type="checkbox" name="enabled[]" value="manual" <?= ! empty($gateways['manual']['enabled']) ? 'checked' : '' ?> class="mt-1 w-4 h-4 accent-signal">
    <span class="flex-1">
      <span class="block font-bold">Manual / offline payment</span>
      <span class="block text-xs text-muted mt-0.5 mb-3">Tenants pay via management office / EFT and upload proof of payment for approval.</span>
      <textarea name="manual_instructions" rows="3" placeholder="e.g. Pay by EFT to Account 123456, branch code 250655, and upload your proof of payment." class="w-full border border-linestrong rounded-lg px-3 py-2 text-sm"><?= esc($gateways['manual']['instructions'] ?? '') ?></textarea>
    </span>
  </label>

  <button class="bg-ink text-white font-bold px-5 py-3 rounded-xl hover:brightness-110">Save settings</button>
</form>

<?= $this->endSection() ?>
