// https://nuxt.com/docs/api/configuration/nuxt-config
import tailwindcss from "@tailwindcss/vite";
import { fileURLToPath } from "node:url";
export default defineNuxtConfig({
  runtimeConfig: {
    serverUrl: process.env.SERVER_DATABASE_URL,
    public: {
      reverbAppKey: process.env.NUXT_REVERB_APP_KEY,
      reverbHost: process.env.NUXT_REVERB_HOST || "127.0.0.1",
      reverbPort: process.env.NUXT_REVERB_PORT || "8080",
      reverbScheme: process.env.NUXT_REVERB_SCHEME || "http",
    },
  },
  compatibilityDate: "2025-07-15",
  devtools: { enabled: true },
  alias: { "@": fileURLToPath(new URL("~/app", import.meta.url)) },
  css: ["~/assets/css/main.css", "maplibre-gl/dist/maplibre-gl.css"],
  modules: ["@nuxt/test-utils", "nuxt-auth-sanctum"],
  sanctum: {
    baseUrl: "http://localhost:8000",
    redirect: {
      onLogin: "/operational/dashboard",
      onAuthOnly: "/operational",
    },
    logLevel: 4,
  },
  vite: {
    optimizeDeps: {
      include: [
        "@vue/devtools-core",
        "@vue/devtools-kit",
        "zod",
        "laravel-echo",
        "pusher-js",
      ],
      exclude: ["maplibre-gl"],
    },
    plugins: [tailwindcss() as any],
  },
});
