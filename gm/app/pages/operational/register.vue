<script setup lang="ts">
  definePageMeta({
    layout: 'operational',
    middleware: ['sanctum:auth'],
  });
</script>

<template>
  <div class="hero min-h-screen">
    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
      <legend class="fieldset-legend">Technician Details</legend>

      <label class="label">First Name</label>
      <input type="text" class="input" placeholder="firstname " />

      <label class="label">Middle Name</label>
      <input type="text" class="input" placeholder="middlename" />

      <label class="label">Last Name</label>
      <input type="text" class="input" placeholder="lastname" />

      <label class="label">Phone Number</label>
      <input type="tel" class="input" placeholder="+251xxxxxxxxx" />

      <label class="label">Date of Birth</label>
      <input type="date" class="input" placeholder="01 / 01 / 1997" />

      <label class="label">Address</label>
      <input type="text" class="input" placeholder="Piasa, Addis Ababa" />

      <button class="btn btn-neutral">Register</button>
    </fieldset>
  </div>
</template>

<style scoped>

</style>