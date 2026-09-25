import vue from "@vitejs/plugin-vue";
import { defineConfig } from "vite";

export default defineConfig({
  plugins: [vue()],
  server: {
    host: "0.0.0.0",
    proxy: {
      "/api": {
        target: "http://backend-service:8000",
        changeOrigin: true,
        secure: false,
      },
    },
  },
});
