<script setup lang="ts">
import { DrawMode } from "~/components/DrawMode";
import CityMap from "./CityMap.vue";

const client = useSanctumClient();
const current_mode = ref(DrawMode.Default);

const cityMap = ref<InstanceType<typeof CityMap> | null>(null);

const isSaving = ref(false);
const errorMessage = ref<string | null>(null);
const successMessage = ref<string | null>(null);

function registerNodes() {
  errorMessage.value = null;
  successMessage.value = null;
  cityMap.value?.saveMap();
}

async function handleSave(payload: {
  nodes: { clientId: string; longitude: number; latitude: number }[];
  relations: { from: string; to: string }[];
}) {
  if (!payload.nodes.length) {
    errorMessage.value = "Please draw at least one node before saving.";
    return;
  }

  isSaving.value = true;
  errorMessage.value = null;

  try {
    const response = await client("/api/power-grid", {
      method: "POST",
      body: payload,
    });

    console.log("Grid saved:", response);
    successMessage.value = "Power grid saved successfully!";
  } catch (err: any) {
    console.error("Failed to save power grid:", err);
    errorMessage.value =
      err?.data?.message || err?.message || "Failed to save power grid.";
  } finally {
    isSaving.value = false;
  }
}
</script>

<template>
  <p>Draw initial node Designs</p>

  <CityMap
    ref="cityMap"
    :setup="true"
    :mode="current_mode"
    @save="handleSave"
  />

  <p class="font-light text-amber-900">
    * This will be extended to live edit as project progresses
  </p>

  <fieldset class="fieldset">
    <legend class="fieldset-legend">Draw Mode</legend>

    <select v-model="current_mode" class="select">
      <option selected disabled :value="DrawMode.Default">Default</option>
      <option :value="DrawMode.Node">Draw Node</option>
      <option :value="DrawMode.Line">Draw Connection</option>
    </select>
  </fieldset>

  <button class="btn btn-neutral" :disabled="isSaving" @click="registerNodes">
    {{ isSaving? "Saving ..." : "Register Nodes"}}
  </button>
</template>
