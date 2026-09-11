'use client';

import { useState } from 'react';
import { apiPost } from '@/lib/api';

type Parcel = {
  id: number;
  reservation_id: number;
  status: string;
};

export default function DropOffForm() {
  const [depositCode, setDepositCode] = useState('');
  const [senderName, setSenderName] = useState('');
  const [senderContact, setSenderContact] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [result, setResult] = useState<Parcel | null>(null);
  const [loading, setLoading] = useState(false);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setLoading(true);
    try {
      const parcel = await apiPost<Parcel>('/parcels/deposit', {
        deposit_code: depositCode.trim(),
        sender_name: senderName.trim() || null,
        sender_contact: senderContact.trim() || null,
      });
      setResult(parcel);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Something went wrong.');
    } finally {
      setLoading(false);
    }
  };

  if (result) {
    return (
      <div className="bg-ok-soft border border-ok/30 rounded-2xl p-6 text-center">
        <h2 className="text-xl font-extrabold text-ok">Locker assigned</h2>
        <p className="text-ink-soft mt-2 text-sm">
          Head to the kiosk on site — it will open the assigned locker automatically. Place the parcel inside and
          close the door firmly.
        </p>
      </div>
    );
  }

  return (
    <form onSubmit={submit} className="bg-white border border-line rounded-2xl p-6 space-y-4">
      <div>
        <label className="block text-xs font-bold text-ink-soft mb-1.5">Drop-off code</label>
        <input
          required
          value={depositCode}
          onChange={(e) => setDepositCode(e.target.value)}
          placeholder="e.g. 482719"
          className="w-full border border-line-strong rounded-xl px-4 py-3 text-lg tracking-widest font-bold focus:outline-none focus:border-ink"
        />
      </div>
      <div>
        <label className="block text-xs font-bold text-ink-soft mb-1.5">Your name (optional)</label>
        <input
          value={senderName}
          onChange={(e) => setSenderName(e.target.value)}
          className="w-full border border-line-strong rounded-xl px-4 py-3 focus:outline-none focus:border-ink"
        />
      </div>
      <div>
        <label className="block text-xs font-bold text-ink-soft mb-1.5">Courier / company (optional)</label>
        <input
          value={senderContact}
          onChange={(e) => setSenderContact(e.target.value)}
          className="w-full border border-line-strong rounded-xl px-4 py-3 focus:outline-none focus:border-ink"
        />
      </div>
      {error && <p className="text-alert text-sm font-semibold">{error}</p>}
      <button
        disabled={loading}
        className="w-full bg-ink text-white font-bold py-3.5 rounded-xl hover:brightness-110 disabled:opacity-50"
      >
        {loading ? 'Checking code…' : 'Continue to locker'}
      </button>
    </form>
  );
}
