import Link from 'next/link';
import CollectForm from './CollectForm';

export default function CollectPage() {
  return (
    <main className="min-h-screen bg-cream flex items-center justify-center px-6 py-16">
      <div className="max-w-md w-full">
        <Link href="/" className="flex items-center gap-2 font-extrabold text-lg tracking-tight mb-8">
          <span className="w-3 h-3 rounded-md bg-signal inline-block" />
          Dropa
        </Link>
        <h1 className="text-2xl font-extrabold tracking-tight mb-2">Collect a parcel</h1>
        <p className="text-muted text-sm mb-6">
          No app or account needed — a resident&apos;s PIN, QR code, or a delegate code they shared with you all work here.
        </p>
        <CollectForm />
      </div>
    </main>
  );
}
