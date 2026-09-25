<script setup>
import { computed, reactive } from "vue";

const session = reactive({
  user: (() => {
    try {
      return JSON.parse(localStorage.getItem("dominitory_user") || "null");
    } catch {
      return null;
    }
  })(),
});

const userName = computed(() => {
  return session.user?.name || session.user?.email || "User";
});

async function logout() {
  const token = localStorage.getItem("dominitory_token");

  try {
    if (token) {
      await fetch("/api/signout", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          Authorization: `Bearer ${token}`,
        },
      });
    }
  } catch (error) {
    console.error("Logout request failed:", error);
  } finally {
    localStorage.removeItem("dominitory_token");
    localStorage.removeItem("dominitory_user");
    window.location.reload();
  }
}
</script>

<template>
  <main class="auth-shell">
    <section class="auth-card dashboard">
      <h1>Dashboard</h1>
      <p>
        Welcome,
        <strong>{{ userName }}</strong>
      </p>
      <p class="token-label">Token saved in browser storage</p>

      <button class="logout-button" type="button" @click="logout">
        Logout
      </button>
    </section>
  </main>
</template>
