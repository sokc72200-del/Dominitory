import { defineStore } from "pinia";
import { ref } from "vue";

export const useAuthStore = defineStore("auth", () => {
  const user = ref(null);

  async function login({ email, password }) {
    // Replace with your real API call
    const res = await fetch("/api/login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email, password }),
    });

    if (!res.ok) throw new Error("Invalid credentials");

    const data = await res.json(); // expect { user: { email, role }, token }
    user.value = data.user;
    localStorage.setItem("token", data.token);
    return data.user;
  }

  function logout() {
    user.value = null;
    localStorage.removeItem("token");
  }

  return { user, login, logout };
});