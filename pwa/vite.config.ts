import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  base: '/tg/ozna/app/',
  server: {
    proxy: {
      '/tg/ozna/api': {
        target: 'https://api.ladyzenko.pro',
        changeOrigin: true,
      },
    },
  },
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
  },
})
