<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-6">Maintenance</h1>

<div class="bg-white border border-line rounded-xl2 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-[#FCFBF7] text-inksoft text-xs font-bold uppercase">
      <tr>
        <th class="text-left px-5 py-3">Reported by</th>
        <th class="text-left px-5 py-3">Locker</th>
        <th class="text-left px-5 py-3">Issue</th>
        <th class="text-left px-5 py-3">Status</th>
        <th class="px-5 py-3"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tickets as $t): $locker = $lockersById[$t['locker_id']] ?? null; $user = $usersById[$t['raised_by']] ?? null; ?>
      <tr class="border-t border-line align-top">
        <td class="px-5 py-3 font-semibold"><?= esc($user->full_name ?? 'Unknown') ?></td>
        <td class="px-5 py-3 text-muted"><?= $locker ? 'Locker ' . esc($locker['label']) : '—' ?></td>
        <td class="px-5 py-3 text-inksoft max-w-sm"><?= esc($t['issue']) ?></td>
        <td class="px-5 py-3">
          <span class="text-xs font-bold px-2 py-1 rounded-full
            <?= $t['status'] === 'resolved' ? 'bg-oksoft text-ok' : ($t['status'] === 'in_progress' ? 'bg-[#FFFBEC] text-signaldeep' : 'bg-[#FBEAEA] text-alert') ?>">
            <?= esc(str_replace('_', ' ', $t['status'])) ?>
          </span>
        </td>
        <td class="px-5 py-3 text-right">
          <?php if ($t['status'] !== 'resolved'): ?>
          <form method="post" action="/manage/maintenance/<?= $t['id'] ?>/status" class="flex gap-2 justify-end">
            <?= csrf_field() ?>
            <?php if ($t['status'] === 'open'): ?>
              <button name="status" value="in_progress" class="text-signaldeep font-bold hover:brightness-110">Start work</button>
            <?php endif; ?>
            <button name="status" value="resolved" class="text-ok font-bold hover:brightness-110">Resolve</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($tickets)): ?>
      <tr><td colspan="5" class="px-5 py-8 text-center text-muted">No maintenance issues reported.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
