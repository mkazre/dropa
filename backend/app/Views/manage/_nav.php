<?php $isOwner = auth()->user()->inGroup('property_admin', 'superadmin'); ?>
<a href="/manage" class="block px-3 py-2 rounded-lg hover:bg-white/10">Dashboard</a>
<a href="/manage/parcels" class="block px-3 py-2 rounded-lg hover:bg-white/10">Parcels</a>
<a href="/manage/maintenance" class="block px-3 py-2 rounded-lg hover:bg-white/10">Maintenance</a>
<?php if ($isOwner): ?>
<a href="/manage/tenants" class="block px-3 py-2 rounded-lg hover:bg-white/10">Tenants</a>
<a href="/manage/staff" class="block px-3 py-2 rounded-lg hover:bg-white/10">Staff</a>
<a href="/manage/payments" class="block px-3 py-2 rounded-lg hover:bg-white/10">Payments</a>
<a href="/manage/gateways" class="block px-3 py-2 rounded-lg hover:bg-white/10">Payment Settings</a>
<a href="/manage/pricing" class="block px-3 py-2 rounded-lg hover:bg-white/10">Pricing</a>
<a href="/manage/branding" class="block px-3 py-2 rounded-lg hover:bg-white/10">Branding</a>
<?php endif; ?>
