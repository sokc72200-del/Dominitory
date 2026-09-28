<script setup>
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import SignIn from "@/components/SignIn.vue";

const router = useRouter();
const auth = useAuthStore();

const emit = defineEmits(["submit-login", "switch-mode"]);
const form = reactive({
  email: "",
  password: "",
});

function submit() {
  emit("submit-login", { ...form });
  form.email = "";
  form.password = "";
}
async function handleLogin(credentials) {
  try {
    const user = await auth.login(credentials);
    router.push(user.role === "admin" ? "/admin" : "/dashboard");
  } catch (err) {
    console.error(err);
    // show an error message to the user here
  }
}
</script>

<template>
  <SignIn @submit-login="handleLogin" @switch-mode="/* handle signup switch */" />
  <section>
    <h1>Welcome back</h1>

    <form class="auth-form" @submit.prevent="submit">
      <div class="field">
        <label for="signin-email">Email</label>
        <input
          id="signin-email"
          v-model="form.email"
          type="email"
          placeholder="you@example.com"
          required
        />
      </div>

      <div class="field">
        <label for="signin-password">Password</label>
        <input
          id="signin-password"
          v-model="form.password"
          type="password"
          placeholder="Enter password"
          required
        />
      </div>

      <button class="submit-button" type="submit">Login</button>
    </form>

    <button
      class="link-button"
      type="button"
      @click="emit('switch-mode', 'signup')"
    >
      Need an account?
    </button>
  </section>
</template>
