<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-2">Onboarding Requests</h1>
<p class="text-muted text-sm mb-6">Leads from the "Bring Dropa to your property" form on the website.</p>

<form method="post" action="/admin/onboarding-requests/toggle" class="bg-white border border-line rounded-xl2 p-6 max-w-xl mb-8 flex items-center justify-between">
  <?= csrf_field() ?>
  <div>
    <p class="font-bold">Self-service signup form</p>
    <p class="text-xs text-muted">When off, the website form is hidden and submissions are rejected.</p>
  </div>
  <label class="inline-flex items-center gap-2">
    <input type="checkbox" name="enabled" value="1" <?= $enabled ? 'checked' : '' ?> onchange="this.form.submit()" class="w-5 h-5">
    <span class="text-sm font-bold"><?= $enabled ? 'Enabled' : 'Disabled' ?></span>
  </label>
</form>

<div class="bg-white border border-line rounded-xl2 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-paper text-left">
      <tr>
        <th class="px-4 py-3">Property</th>
        <th class="px-4 py-3">Units</th>
        <th class="px-4 py-3">Contact</th>
        <th class="px-4 py-3">Email</th>
        <th class="px-4 py-3">Received</th>
        <th class="px-4 py-3">Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($requests as $r): ?>
        <tr class="border-t border-line">
          <td class="px-4 py-3 font-bold"><?= esc($r['property_name']) ?></td>
          <td class="px-4 py-3"><?= esc($r['unit_count'] ?? '—') ?></td>
          <td class="px-4 py-3"><?= esc($r['contact_name']) ?></td>
          <td class="px-4 py-3"><?= esc($r['email']) ?></td>
          <td class="px-4 py-3"><?= esc($r['created_at']) ?></td>
          <td class="px-4 py-3">
            <form method="post" action="/admin/onboarding-requests/<?= $r['id'] ?>/status">
              <?= csrf_field() ?>
              <select name="status" onchange="this.form.submit()" class="border border-linestrong rounded-lg px-2 py-1">
                <?php foreach (['new', 'contacted', 'converted', 'dismissed'] as $s): ?>
                  <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (! $requests): ?>
        <tr><td colspan="6" class="px-4 py-6 text-center text-muted">No requests yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
