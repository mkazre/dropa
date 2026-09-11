<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-2">Branding</h1>
<p class="text-muted text-sm mb-6">Shown to your residents in the Dropa app — on their home screen and in the emails they get.</p>

<form method="post" action="/manage/branding" class="bg-white border border-line rounded-xl2 p-6 max-w-xl space-y-4">
  <?= csrf_field() ?>

  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Logo URL</label>
    <input name="logo_url" value="<?= esc($property['logo_url'] ?? '') ?>" placeholder="https://…/logo.png"
           class="w-full border border-linestrong rounded-xl px-4 py-3 focus:outline-none focus:border-ink">
    <p class="text-xs text-muted mt-1">Paste a link to an image hosted elsewhere — direct upload isn't available yet.</p>
  </div>

  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Accent colour</label>
    <div class="flex items-center gap-3">
      <input type="color" name="brand_color" value="<?= esc($property['brand_color'] ?? '#FFC400') ?>" class="w-14 h-11 border border-linestrong rounded-lg">
      <span class="text-sm text-muted">Used for the "Expecting a parcel" tile in the app.</span>
    </div>
  </div>

  <?php if ($property['logo_url']): ?>
  <div>
    <label class="block text-xs font-bold text-inksoft mb-1.5">Preview</label>
    <img src="<?= esc($property['logo_url']) ?>" alt="Logo preview" class="h-16 rounded-lg border border-line">
  </div>
  <?php endif; ?>

  <button class="bg-ink text-white font-bold px-5 py-3 rounded-xl hover:brightness-110">Save branding</button>
</form>

<?= $this->endSection() ?>
