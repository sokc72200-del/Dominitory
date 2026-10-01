<script setup>
import { reactive, ref } from "vue";

const emit = defineEmits(["submit-signup", "switch-mode"]);

const form = reactive({
  name: "",
  email: "",
  gender: "",
  password: "",
  password_confirmation: "",
});

const processing = ref(false);
const errors = ref({});

function submit() {
  errors.value = {};

  if (form.password !== form.password_confirmation) {
    errors.value = {
      password_confirmation: ["Passwords do not match."],
    };
    return;
  }

  processing.value = true;
  emit("submit-signup", { ...form });
  // reset form
  Object.assign(form, {
    name: "",
    email: "",
    gender: "",
    password: "",
    password_confirmation: "",
  });
  processing.value = false;
}
</script>

<template>
  <section class="signup-panel">
    <div class="auth-header">
      <h1>Create an account</h1>
      <p class="subtitle">Join us and get started in seconds</p>
    </div>

    <form class="auth-form" @submit.prevent="submit">
      <!-- Name -->
      <div class="field">
        <label for="signup-name">Full name</label>
        <input
          id="signup-name"
          v-model="form.name"
          type="text"
          placeholder="Your name"
          required
        />
        <span v-if="errors.name" class="error">{{ errors.name[0] }}</span>
      </div>

      <!-- Email -->
      <div class="field">
        <label for="signup-email">Email</label>
        <input
          id="signup-email"
          v-model="form.email"
          type="email"
          placeholder="you@example.com"
          required
        />
        <span v-if="errors.email" class="error">{{ errors.email[0] }}</span>
      </div>

      <!-- Gender -->
      <div class="field">
        <label for="signup-gender">Gender</label>
        <select id="signup-gender" v-model="form.gender" required>
          <option value="" disabled>Select gender</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
          <option value="other">Other</option>
          <option value="prefer_not">Prefer not to say</option>
        </select>
        <span v-if="errors.gender" class="error">{{ errors.gender[0] }}</span>
      </div>

      <!-- Password -->
      <div class="field">
        <label for="signup-password">Password</label>
        <input
          id="signup-password"
          v-model="form.password"
          type="password"
          placeholder="Create a password"
          required
        />
        <span v-if="errors.password" class="error">{{
          errors.password[0]
        }}</span>
      </div>

      <!-- Confirm Password -->
      <div class="field">
        <label for="signup-password-confirmation">Confirm password</label>
        <input
          id="signup-password-confirmation"
          v-model="form.password_confirmation"
          type="password"
          placeholder="Confirm your password"
          required
        />
        <span v-if="errors.password_confirmation" class="error">
          {{ errors.password_confirmation[0] }}
        </span>
      </div>

      <button class="submit-button" type="submit" :disabled="processing">
        <span v-if="processing" class="spinner"></span>
        {{ processing ? "Creating account..." : "Create account" }}
      </button>
    </form>

    <p class="switch-mode">
      Already have an account?
      <button
        class="link-button"
        type="button"
        @click="emit('switch-mode', 'signin')"
      >
        Sign in
      </button>
    </p>
  </section>
</template>
