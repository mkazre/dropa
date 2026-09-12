import Link from 'next/link';

export default function Home() {
  return (
    <main className="min-h-screen bg-paper text-ink">
      <header className="max-w-5xl mx-auto flex items-center justify-between px-6 py-6">
        <div className="flex items-center gap-2 font-extrabold text-xl tracking-tight">
          <span className="w-3.5 h-3.5 rounded-md bg-signal inline-block" />
          Dropa
        </div>
        <nav className="flex items-center gap-6 text-sm font-semibold">
          <Link href="/drop-off" className="whitespace-nowrap transition-colors hover:text-signal-deep">Drop off a parcel</Link>
          <Link href="/collect" className="whitespace-nowrap transition-colors hover:text-signal-deep">Collect a parcel</Link>
          <Link
            href="/onboard"
            className="whitespace-nowrap bg-ink text-white px-4 py-2.5 rounded-xl transition-all hover:brightness-110 hover:-translate-y-0.5 hover:shadow-md"
          >
            Register your property
          </Link>
        </nav>
      </header>

      <section className="max-w-5xl mx-auto px-6 py-20 grid md:grid-cols-2 gap-12 items-center">
        <div>
          <h1 className="text-4xl md:text-5xl font-extrabold tracking-tight leading-tight">
            Your building&apos;s digital letterbox.
          </h1>
          <p className="text-muted mt-5 text-lg leading-relaxed">
            Dropa brings smart parcel lockers to complexes, townhouses and estates. Residents reserve a locker
            when they&apos;re expecting a delivery, couriers drop off with a code — no accounts, no meetups — and
            the locker frees itself back up the moment it&apos;s collected.
          </p>
          <div className="flex flex-wrap gap-3 mt-8">
            <Link
              href="/onboard"
              className="whitespace-nowrap bg-signal text-[#241f00] font-bold px-5 py-3.5 rounded-xl transition-all hover:brightness-95 hover:-translate-y-0.5 hover:shadow-md"
            >
              Bring Dropa to your property
            </Link>
            <Link
              href="/drop-off"
              className="whitespace-nowrap bg-white border-2 border-line-strong text-ink font-bold px-5 py-3.5 rounded-xl transition-all hover:border-ink hover:bg-cream hover:-translate-y-0.5 hover:shadow-md"
            >
              I have a drop-off code
            </Link>
          </div>
        </div>
        <div className="bg-cream border border-line rounded-3xl p-8">
          <ul className="space-y-5 text-sm">
            {[
              ['Reserve ahead', 'Residents pick a locker size before the courier even arrives.'],
              ['No app needed for couriers', 'A deposit code is all it takes to drop off.'],
              ['Collect on your time', 'PIN, QR, or in-app unlock — 24/7.'],
              ['Lockers self-release', 'Collected parcels free the locker for the next delivery.'],
            ].map(([title, body]) => (
              <li key={title} className="flex gap-3">
                <span className="w-2 h-2 rounded-full bg-signal-deep mt-2 flex-none" />
                <span>
                  <b className="block">{title}</b>
                  <span className="text-muted">{body}</span>
                </span>
              </li>
            ))}
          </ul>
        </div>
      </section>
    </main>
  );
}
