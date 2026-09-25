<script setup>
import { computed, reactive, ref } from "vue";
import SigninForm from "./components/auth/signin.vue";
import SignupForm from "./components/auth/signup.vue";

const API_BASE = "/api";

const authMode = ref("signin");
const authError = ref("");
const authSuccess = ref("");

const session = reactive({
  token: localStorage.getItem("dominitory_token") || "",
  user: (() => {
    try {
      return JSON.parse(localStorage.getItem("dominitory_user") || "null");
    } catch {
      return null;
    }
  })(),
});

const isLoggedIn = computed(() => !!session.token);

function setSession(token, user) {
  session.token = token;
  session.user = user;
  localStorage.setItem("dominitory_token", token);
  localStorage.setItem("dominitory_user", JSON.stringify(user));
}

function clearSession() {
  session.token = "";
  session.user = null;
  localStorage.removeItem("dominitory_token");
  localStorage.removeItem("dominitory_user");
}

async function submitLogin(payload) {
  authError.value = "";
  authSuccess.value = "";

  try {
    const response = await fetch(`${API_BASE}/signin`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || data.error || "Login failed");
    }

    setSession(data.access_token, data.user || { email: payload.email });
    authSuccess.value = "Login successful!";
  } catch (error) {
    authError.value = error.message;
  }
}

async function submitSignup(payload) {
  authError.value = "";
  authSuccess.value = "";

  try {
    const response = await fetch(`${API_BASE}/signup`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || data.error || "Registration failed");
    }

    setSession(
      data.access_token,
      data.user || { name: payload.name, email: payload.email },
    );
    authSuccess.value = "Registration successful!";
  } catch (error) {
    authError.value = error.message;
  }
}

async function logout() {
  const token = session.token;

  try {
    if (token) {
      await fetch(`${API_BASE}/signout`, {
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
    clearSession();
    authSuccess.value = "You are logged out.";
    authError.value = "";
  }
}
</script>

<template>
  <main class="auth-shell">
    <section v-if="!isLoggedIn" class="auth-card">
      <div class="tab-row">
        <button
          :class="['tab-button', { active: authMode === 'signin' }]"
          type="button"
          @click="authMode = 'signin'"
        >
          Login
        </button>
        <button
          :class="['tab-button', { active: authMode === 'signup' }]"
          type="button"
          @click="authMode = 'signup'"
        >
          Register
        </button>
      </div>

      <component
        :is="authMode === 'signin' ? SigninForm : SignupForm"
        @submit-login="submitLogin"
        @submit-signup="submitSignup"
        @switch-mode="authMode = $event"
      />

      <p v-if="authError" class="message error">{{ authError }}</p>
      <p v-if="authSuccess" class="message success">{{ authSuccess }}</p>
    </section>

    <section v-else class="auth-card dashboard">
      <h1>Dashboard</h1>
      <p>
        Welcome,
        <strong>{{
          session.user?.name || session.user?.email || "User"
        }}</strong>
      </p>
      <p class="token-label">Token saved in browser storage</p>

      <button class="logout-button" type="button" @click="logout">
        Logout
      </button>
    </section>
  </main>
</template>
