import Link from 'next/link';
import OnboardForm from './OnboardForm';

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
        <OnboardForm />
      </div>
    </main>
  );
}
