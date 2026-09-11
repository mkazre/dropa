import Link from 'next/link';

export default function OnboardPage() {
  return (
    <main className="min-h-screen bg-cream flex items-center justify-center px-6 py-16">
      <div className="max-w-md w-full">
        <Link href="/" className="flex items-center gap-2 font-extrabold text-lg tracking-tight mb-8">
          <span className="w-3 h-3 rounded-md bg-signal inline-block" />
          Dropa
        </Link>
        <h1 className="text-2xl font-extrabold tracking-tight mb-2">Bring Dropa to your property</h1>
        <p className="text-muted text-sm mb-6">
          Tell us about your complex or estate and we&apos;ll set up your Body Corporate Admin account.
        </p>
        <form className="bg-white border border-line rounded-2xl p-6 space-y-4">
          <div>
            <label className="block text-xs font-bold text-ink-soft mb-1.5">Property name</label>
            <input required className="w-full border border-line-strong rounded-xl px-4 py-3 focus:outline-none focus:border-ink" />
          </div>
          <div>
            <label className="block text-xs font-bold text-ink-soft mb-1.5">Number of units</label>
            <input type="number" className="w-full border border-line-strong rounded-xl px-4 py-3 focus:outline-none focus:border-ink" />
          </div>
          <div>
            <label className="block text-xs font-bold text-ink-soft mb-1.5">Your name</label>
            <input required className="w-full border border-line-strong rounded-xl px-4 py-3 focus:outline-none focus:border-ink" />
          </div>
          <div>
            <label className="block text-xs font-bold text-ink-soft mb-1.5">Email</label>
            <input type="email" required className="w-full border border-line-strong rounded-xl px-4 py-3 focus:outline-none focus:border-ink" />
          </div>
          <button type="submit" className="w-full bg-ink text-white font-bold py-3.5 rounded-xl hover:brightness-110">
            Request setup
          </button>
          <p className="text-xs text-muted text-center">
            This is a lead-capture placeholder — self-service property signup is on the roadmap; today the Super Admin
            creates properties and their first Body Corporate account from the Dropa Admin panel.
          </p>
        </form>
      </div>
    </main>
  );
}
