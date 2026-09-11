<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-extrabold tracking-tight mb-6">Audit log</h1>

<div class="bg-white border border-line rounded-xl2 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-[#FCFBF7] text-inksoft text-xs font-bold uppercase">
      <tr>
        <th class="text-left px-5 py-3">When</th>
        <th class="text-left px-5 py-3">Who</th>
        <th class="text-left px-5 py-3">Action</th>
        <th class="text-left px-5 py-3">Subject</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($entries as $e): $user = $usersById[$e['user_id']] ?? null; ?>
      <tr class="border-t border-line">
        <td class="px-5 py-3 text-muted whitespace-nowrap"><?= esc($e['created_at']) ?></td>
        <td class="px-5 py-3 font-semibold"><?= esc($user->full_name ?? $user->email ?? 'System') ?></td>
        <td class="px-5 py-3"><code class="bg-cream px-1.5 py-0.5 rounded text-xs"><?= esc($e['action']) ?></code></td>
        <td class="px-5 py-3 text-muted"><?= esc(trim(($e['subject'] ?? '') . ' #' . ($e['subject_id'] ?? ''), ' #')) ?: '—' ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($entries)): ?>
      <tr><td colspan="4" class="px-5 py-8 text-center text-muted">Nothing logged yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="mt-4 text-sm">
  <?= $pager->links() ?>
</div>

<?= $this->endSection() ?>
