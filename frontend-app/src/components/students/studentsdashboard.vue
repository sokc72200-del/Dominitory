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
  <main class="student-shell">
    <section class="student-card">
      <div class="student-banner">
        <span class="student-avatar">{{
          userName
            .split(" ")
            .map((n) => n[0])
            .join("")
            .slice(0, 2)
            .toUpperCase()
        }}</span>
        <div class="student-banner-copy">
          <p>Resident portal</p>
          <h1>{{ userName }}</h1>
        </div>
      </div>

      <div class="student-body">
        <div class="room-placeholder">
          Room assignment not loaded yet — wire this up to
          <code>GET /api/my-room</code>.
        </div>

        <button class="logout-button" type="button" @click="logout">
          Logout
        </button>
      </div>
    </section>
  </main>
</template>
