// Design tokens ported 1:1 from dropa-prototype.html so the app matches
// the original demo's warm-graphite / signal-yellow visual language.
export const colors = {
  ink: '#211E17',
  inkSoft: '#4A4638',
  paper: '#FFFFFF',
  bg: '#100F0C',
  signal: '#FFC400',
  signalDeep: '#E0A500',
  line: '#E7E4DB',
  lineStrong: '#D8D3C6',
  ok: '#1F8A5B',
  okSoft: '#E6F4EC',
  alert: '#D64545',
  muted: '#8C887C',
  cream: '#FCFBF7',
} as const;

export const radius = {
  sm: 12,
  md: 16,
  lg: 20,
  pill: 999,
};

export const spacing = (n: number) => n * 4;

export const type = {
  h1: { fontSize: 27, fontWeight: '800' as const, letterSpacing: -0.9 },
  h2: { fontSize: 19, fontWeight: '800' as const, letterSpacing: -0.4 },
  body: { fontSize: 15, fontWeight: '400' as const },
  sub: { fontSize: 14, color: colors.muted, lineHeight: 21 },
  label: { fontSize: 12.5, fontWeight: '700' as const, color: colors.inkSoft },
};
