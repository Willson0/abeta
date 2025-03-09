import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  build: {
    rollupOptions: {
      // Игнорировать определённые варнинги
      onwarn(warning, warn) {
        // Фильтровать варнинги, например, о нехватке файлов
        if (warning.code === 'FILE_NOT_FOUND') {
          return; // Пропустить этот варнинг
        }
        warn(warning); // Вывести остальные варнинги
      },
    },
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
})
