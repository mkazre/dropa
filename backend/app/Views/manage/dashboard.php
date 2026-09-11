<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-6">Your property</h1>

<div class="grid grid-cols-3 gap-4 mb-10">
  <div class="bg-white border border-line rounded-xl2 p-5">
    <div class="text-xs font-bold text-inksoft uppercase">Units</div>
    <div class="text-3xl font-extrabold mt-1"><?= (int) $units ?></div>
  </div>
  <div class="bg-white border border-line rounded-xl2 p-5">
    <div class="text-xs font-bold text-inksoft uppercase">Reservations</div>
    <div class="text-3xl font-extrabold mt-1"><?= (int) $reservations ?></div>
  </div>
  <div class="bg-white border border-line rounded-xl2 p-5">
    <div class="text-xs font-bold text-inksoft uppercase">Awaiting collection</div>
    <div class="text-3xl font-extrabold mt-1 text-signaldeep"><?= (int) $awaiting ?></div>
  </div>
</div>

<p class="text-muted text-sm">Invite tenants and monitor parcels for your property from the left-hand menu.</p>

<?= $this->endSection() ?>
