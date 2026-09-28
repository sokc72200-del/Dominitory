<script setup>
import { computed, reactive } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

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
    router.push("/signin");
  }
}
</script>

<template>
  <main class="auth-shell">
    <section class="auth-card dashboard">
      <h1>User Dashboard</h1>
      <p>
        Welcome,
        <strong>{{ userName }}</strong>
      </p>

      <button class="logout-button" type="button" @click="logout">
        Logout
      </button>
    </section>
  </main>
</template>