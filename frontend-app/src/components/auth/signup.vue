<script setup>
import { reactive, ref } from "vue";

const emit = defineEmits(["submit-signup", "switch-mode"]);

const form = reactive({
  name: "",
  email: "",
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
  form.name = "";
  form.email = "";
  form.password = "";
  form.password_confirmation = "";
  processing.value = false;
}
</script>

<template>
  <section>
    <h1>Create an account</h1>

    <form class="auth-form" @submit.prevent="submit">
      <div class="field">
        <label for="signup-name">Name</label>
        <input
          id="signup-name"
          v-model="form.name"
          type="text"
          placeholder="Your Name"
          required
        />
        <span v-if="errors.name" class="error">{{ errors.name[0] }}</span>
      </div>

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

      <div class="field">
        <label for="signup-password">Password</label>
        <input
          id="signup-password"
          v-model="form.password"
          type="password"
          placeholder="Enter password"
          required
        />
        <span v-if="errors.password" class="error">{{
          errors.password[0]
        }}</span>
      </div>

      <div class="field">
        <label for="signup-password-confirmation">Confirm Password</label>
        <input
          id="signup-password-confirmation"
          v-model="form.password_confirmation"
          type="password"
          placeholder="Confirm password"
          required
        />
      </div>

      <button class="submit-button" type="submit" :disabled="processing">
        {{ processing ? "Creating..." : "Create Account" }}
      </button>
    </form>

    <button
      class="link-button"
      type="button"
      @click="emit('switch-mode', 'signin')"
    >
      Already have an account?
    </button>
  </section>
</template>
