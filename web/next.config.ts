import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // The site is fully client-rendered (every page calls the backend API
  // straight from the browser, no server routes/actions) — static export
  // lets it deploy as plain HTML/JS to any host, cPanel included, with no
  // Node.js process running on the server at all.
  output: "export",
  images: { unoptimized: true },
};

export default nextConfig;
