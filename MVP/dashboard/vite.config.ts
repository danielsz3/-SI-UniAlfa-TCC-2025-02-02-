import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { VitePWA } from "vite-plugin-pwa";

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    react(),
    VitePWA({
      registerType: "autoUpdate",
      includeAssets: [
        "favicon.svg",
        "robots.txt",
        "apple-touch-icon.png",
      ],
      manifest: {
        name: "PetAffinity - Admin",
        short_name: "PetAffinity",
        description: "Meu Progressive Web App feito com React + Vite",
        theme_color: "#337ab7",
        background_color: "#ffffff",
        display: "standalone",
        start_url: "/",
        icons: [
          {
            src: "pwa/192.png",
            sizes: "192x192",
            type: "image/png",
          },
          {
            src: "pwa/512.png",
            sizes: "512x512",
            type: "image/png",
          },
          {
            src: "pwa/512.png",
            sizes: "512x512",
            type: "image/png",
            purpose: "any maskable",
          },
        ],
      },
    }),
  ],
  server: {
    port: 5173,                // porta default do Vite
    host: true,                // permite acesso externo (docker)
    watch: {
      usePolling: true,        // hot reload funciona dentro do docker
    },
    proxy: {
      // Encaminha chamadas "/api" para o Laravel (backend)
      '/api': {
        target: process.env.VITE_API_URL || 'http://backend:9000',
        changeOrigin: true,
        secure: false,
      },
    },
  },
  build: {
    outDir: 'dist',            // saída padrão do build
  },
})
