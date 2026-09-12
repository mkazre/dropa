'use client';

import { useEffect, useState } from 'react';
import { apiGet, apiPost } from '@/lib/api';

export default function OnboardForm() {
  const [checkingStatus, setCheckingStatus] = useState(true);
  const [signupsOpen, setSignupsOpen] = useState(true);

  const [propertyName, setPropertyName] = useState('');
  const [unitCount, setUnitCount] = useState('');
  const [contactName, setContactName] = useState('');
  const [email, setEmail] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [submitted, setSubmitted] = useState(false);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    apiGet<{ enabled: boolean }>('/onboarding-requests/status')
      .then((res) => setSignupsOpen(res.enabled))
      .catch(() => setSignupsOpen(true))
      .finally(() => setCheckingStatus(false));
  }, []);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setLoading(true);
    try {
      await apiPost('/onboarding-requests', {
        property_name: propertyName.trim(),
        unit_count: unitCount.trim() === '' ? null : Number(unitCount),
        contact_name: contactName.trim(),
        email: email.trim(),
      });
      setSubmitted(true);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Something went wrong.');
    } finally {
      setLoading(false);
    }
  };

  if (checkingStatus) {
    return null;
  }

  if (!signupsOpen) {
    return (
      <div className="bg-white border border-line rounded-2xl p-6 text-center">
        <p className="font-bold">Self-service signups are closed right now.</p>
        <p className="text-muted text-sm mt-2">Please contact us directly and we&apos;ll set your property up.</p>
      </div>
    );
  }

  if (submitted) {
    return (
      <div className="bg-ok-soft border border-ok/30 rounded-2xl p-6 text-center">
        <h2 className="text-xl font-extrabold text-ok">Request received</h2>
        <p className="text-ink-soft mt-2 text-sm">We&apos;ll be in touch to set up your Body Corporate Admin account.</p>
      </div>
    );
  }

  return (
    <form onSubmit={submit} className="bg-white border border-line rounded-2xl p-6 space-y-4">
      <div>
        <label className="block text-xs font-bold text-ink-soft mb-1.5">Property name</label>
        <input
          required
          value={propertyName}
          onChange={(e) => setPropertyName(e.target.value)}
          className="w-full border border-line-strong rounded-xl px-4 py-3 focus:outline-none focus:border-ink"
        />
      </div>
      <div>
        <label className="block text-xs font-bold text-ink-soft mb-1.5">Number of units</label>
        <input
          type="number"
          value={unitCount}
          onChange={(e) => setUnitCount(e.target.value)}
          className="w-full border border-line-strong rounded-xl px-4 py-3 focus:outline-none focus:border-ink"
        />
      </div>
      <div>
        <label className="block text-xs font-bold text-ink-soft mb-1.5">Your name</label>
        <input
          required
          value={contactName}
          onChange={(e) => setContactName(e.target.value)}
          className="w-full border border-line-strong rounded-xl px-4 py-3 focus:outline-none focus:border-ink"
        />
      </div>
      <div>
        <label className="block text-xs font-bold text-ink-soft mb-1.5">Email</label>
        <input
          type="email"
          required
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          className="w-full border border-line-strong rounded-xl px-4 py-3 focus:outline-none focus:border-ink"
        />
      </div>
      {error && <p className="text-alert text-sm font-semibold">{error}</p>}
      <button
        disabled={loading}
        type="submit"
        className="w-full bg-ink text-white font-bold py-3.5 rounded-xl hover:brightness-110 disabled:opacity-50"
      >
        {loading ? 'Sending…' : 'Request setup'}
      </button>
      <p className="text-xs text-muted text-center">
        This creates a lead for the Dropa team to follow up on — your property isn&apos;t live until we&apos;ve set up
        your Body Corporate Admin account.
      </p>
    </form>
  );
}
