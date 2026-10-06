<script setup lang="ts">
definePageMeta({
  layout: 'operational-logged-in',
  middleware: ['sanctum:auth'],
});
</script>
<template>
<div class="tabs tabs-lift">
  <label class="tab">
    <input type="radio" name="my_tabs_4" checked/>
    Manage Users
  </label>
  <div class="tab-content bg-base-100 border-base-300 p-6">
      <ManageUsers/>
  </div>

  <label class="tab">
    <input type="radio" name="my_tabs_4" />
    Manage Nodes
  </label>
  <div class="tab-content bg-base-100 border-base-300 p-6">
      <ManageNodes/>
  </div>
</div>
</template>

<style scoped>
body {
    margin: 1rem;;
}
</style>
