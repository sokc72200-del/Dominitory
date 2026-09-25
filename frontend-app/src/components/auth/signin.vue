<script setup>
import { reactive } from "vue";

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
</script>

<template>
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
