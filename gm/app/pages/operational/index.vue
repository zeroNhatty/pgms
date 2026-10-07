<script setup lang="ts">
definePageMeta({
  layout: 'operational',
});

const { login } = useSanctumAuth()

const credentials = reactive({
  email: '',
  password: '',
  remember: false,
})

const showAlert = ref(false)
const isLoading = ref(false)

const loginFire = async () => {
  try {
    isLoading.value = true
    showAlert.value = false

    await login(credentials)
  } catch (e) {
    showAlert.value = true
    console.error('Login error:', e)

    // hide alert after 3 seconds
    setTimeout(() => {
      showAlert.value = false
    }, 3000)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div>
    <ErrorAlert v-if="showAlert" />

    <NuxtAnnouncer />

    <div class="hero bg-base-200 min-h-screen">
      <div class="hero-content flex-col lg:flex-row-reverse">
        <div class="text-center lg:text-left">
          <h1 class="text-4xl font-bold">Login now!</h1>
          <p class="py-6">
            PGM - Operational made to make your job easy.
          </p>
        </div>

        <div class="card bg-base-100 w-full max-w-sm shrink-0 shadow-2xl">
          <div class="card-body">
            <form @submit.prevent="loginFire">
              <fieldset class="fieldset">
                <label class="label">Email</label>
                <input
                  v-model="credentials.email"
                  type="email"
                  class="input w-full"
                  placeholder="Email"
                  required
                />

                <label class="label mt-2">Password</label>
                <input
                  v-model="credentials.password"
                  type="password"
                  class="input w-full"
                  placeholder="Password"
                  required
                />

                <label class="label mt-2 flex items-center gap-2 cursor-pointer">
                  <input
                    v-model="credentials.remember"
                    type="checkbox"
                    class="checkbox"
                  />
                  <span>Remember me</span>
                </label>

                <div class="mt-2">
                  <a class="link link-hover text-sm">Forgot password?</a>
                </div>

                <button
                  class="btn btn-neutral mt-4 w-full"
                  type="submit"
                  :disabled="isLoading"
                >
                  <span v-if="isLoading" class="loading loading-spinner"></span>
                  <span v-else>Login</span>
                </button>
              </fieldset>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
