const sharp = require('sharp');
const path = require('path');

const brand = (f) => path.join(__dirname, '..', 'assets', 'brand', f);
const out = (f) => path.join(__dirname, '..', 'assets', f);

async function main() {
  // App icon: opaque, square.
  await sharp(brand('icon-mark.svg')).resize(1024, 1024).png().toFile(out('icon.png'));

  // Android adaptive icon: transparent foreground, ink background handled by app.json.
  await sharp(brand('adaptive-foreground.svg'))
    .resize(1024, 1024)
    .png()
    .toFile(out('android-icon-foreground.png'));
  await sharp({ create: { width: 1024, height: 1024, channels: 4, background: '#211E17' } })
    .png()
    .toFile(out('android-icon-background.png'));
  await sharp(brand('adaptive-foreground.svg'))
    .resize(1024, 1024)
    .greyscale()
    .png()
    .toFile(out('android-icon-monochrome.png'));

  // Splash: transparent logo + wordmark, ink background set in app.json.
  await sharp(brand('splash-logo.svg')).resize(900, 900).png().toFile(out('splash-icon.png'));

  // Favicon (web).
  await sharp(brand('icon-mark.svg')).resize(48, 48).png().toFile(out('favicon.png'));

  console.log('Brand assets rebuilt.');
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
