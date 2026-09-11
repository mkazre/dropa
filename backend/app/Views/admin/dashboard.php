<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-6">Platform overview</h1>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
  <div class="bg-white border border-line rounded-xl2 p-5">
    <div class="text-xs font-bold text-inksoft uppercase">Properties</div>
    <div class="text-3xl font-extrabold mt-1"><?= (int) $properties ?></div>
  </div>
  <div class="bg-white border border-line rounded-xl2 p-5">
    <div class="text-xs font-bold text-inksoft uppercase">Reservations</div>
    <div class="text-3xl font-extrabold mt-1"><?= (int) $reservations ?></div>
  </div>
  <div class="bg-white border border-line rounded-xl2 p-5">
    <div class="text-xs font-bold text-inksoft uppercase">Awaiting collection</div>
    <div class="text-3xl font-extrabold mt-1 text-signaldeep"><?= (int) $parcelsAwaiting ?></div>
  </div>
  <div class="bg-white border border-line rounded-xl2 p-5">
    <div class="text-xs font-bold text-inksoft uppercase">Collected</div>
    <div class="text-3xl font-extrabold mt-1 text-ok"><?= (int) $parcelsCollected ?></div>
  </div>
</div>

<p class="text-muted text-sm">Manage properties, payment gateways and platform-wide settings from the left-hand menu.</p>

<?= $this->endSection() ?>
