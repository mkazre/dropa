<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="flex items-center justify-between mb-6">
  <h1 class="text-2xl font-extrabold tracking-tight">Staff</h1>
  <a href="/manage/staff/new" class="bg-signal text-[#241f00] font-bold px-4 py-2.5 rounded-xl hover:brightness-95">+ Invite staff</a>
</div>
<p class="text-muted text-sm mb-6 -mt-4">On-site concierge/security — limited kiosk-assist access, not full tenant self-service.</p>

<div class="bg-white border border-line rounded-xl2 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-[#FCFBF7] text-inksoft text-xs font-bold uppercase">
      <tr>
        <th class="text-left px-5 py-3">Name</th>
        <th class="text-left px-5 py-3">Email</th>
        <th class="text-left px-5 py-3">Phone</th>
        <th class="px-5 py-3"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($staff as $s): ?>
      <tr class="border-t border-line">
        <td class="px-5 py-3 font-semibold"><?= esc($s->full_name ?? '—') ?></td>
        <td class="px-5 py-3 text-muted"><?= esc($s->email) ?></td>
        <td class="px-5 py-3 text-muted"><?= esc($s->phone ?? '—') ?></td>
        <td class="px-5 py-3 text-right">
          <form method="post" action="/manage/staff/<?= $s->id ?>/delete" onsubmit="return confirm('Remove this staff member?')">
            <?= csrf_field() ?>
            <button class="text-alert font-bold hover:brightness-110">Remove</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($staff)): ?>
      <tr><td colspan="4" class="px-5 py-8 text-center text-muted">No staff accounts yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
