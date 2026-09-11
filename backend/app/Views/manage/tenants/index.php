<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="flex items-center justify-between mb-6">
  <h1 class="text-2xl font-extrabold tracking-tight">Tenants</h1>
  <a href="/manage/tenants/new" class="bg-signal text-[#241f00] font-bold px-4 py-2.5 rounded-xl hover:brightness-95">+ Invite tenant</a>
</div>

<div class="bg-white border border-line rounded-xl2 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-[#FCFBF7] text-inksoft text-xs font-bold uppercase">
      <tr>
        <th class="text-left px-5 py-3">Name</th>
        <th class="text-left px-5 py-3">Unit</th>
        <th class="text-left px-5 py-3">Email</th>
        <th class="text-left px-5 py-3">Phone</th>
        <th class="px-5 py-3"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tenants as $t): ?>
      <tr class="border-t border-line">
        <td class="px-5 py-3 font-semibold"><?= esc($t->full_name ?? '—') ?></td>
        <td class="px-5 py-3 text-muted"><?= esc($unitsById[$t->unit_id]['unit_number'] ?? '—') ?></td>
        <td class="px-5 py-3 text-muted"><?= esc($t->email) ?></td>
        <td class="px-5 py-3 text-muted"><?= esc($t->phone ?? '—') ?></td>
        <td class="px-5 py-3 text-right">
          <form method="post" action="/manage/tenants/<?= $t->id ?>/delete" onsubmit="return confirm('Remove this tenant?')">
            <?= csrf_field() ?>
            <button class="text-alert font-bold hover:brightness-110">Remove</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($tenants)): ?>
      <tr><td colspan="5" class="px-5 py-8 text-center text-muted">No tenants yet — invite your first resident.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
