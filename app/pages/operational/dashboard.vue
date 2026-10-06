<script setup lang="ts">
import { z } from "zod";
const { $echo } = useNuxtApp();

definePageMeta({
    layout: "operational-logged-in",
    middleware: ["sanctum:auth"],
});

const NodeSchema = z.object({
  id: z.number().int(),
  longitude: z.coerce.number(),
  latitude: z.coerce.number(),
  status: z.string(),
});

const client = useSanctumClient();
const {
    data: nodes,
    error,
    pending,
} = await useAsyncData("nodes", async () => {
    const response = await client("/api/nodes");
    return z.array(NodeSchema).parse(response);
});

onMounted(() => {
    $echo
        .channel("powernodes")
        .listen(".powernode.updated", (event: { ticket: unknown }) => {
            console.log("WS event [.powernode.updated]:", event);
        });
});

onUnmounted(() => {
    $echo.leaveChannel("powernodes");
});
</script>

<template>
    <div class="p-6 max-w-4xl mx-auto space-y-6">
        <div
            class="flex justify-between items-center pb-4 border-b border-base-content/10"
        >
            <h1 class="text-2xl font-bold tracking-tight">Node Status</h1>
        </div>

        <div v-if="pending" class="flex items-center justify-center py-12">
            <span class="loading loading-ring loading-lg text-primary"></span>
            <span class="ml-4 text-base-content/70 font-medium"
                >Fetching nodes...</span
            >
        </div>

        <div v-else-if="error" class="alert alert-error shadow-sm">
            <span>Error loading data: {{ error.message }}</span>
        </div>

        <div
            v-else
            class="overflow-x-auto rounded-xl border border-base-content/10 bg-base-100 shadow-sm"
        >
            <table class="table table-zebra w-full">
                <thead class="bg-base-200/50">
                    <tr>
                        <th class="w-24">Node ID</th>
                        <th>Location</th>
                        <th class="w-48">Status</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="node in nodes" :key="node.id" class="hover">
                        <td class="font-mono font-bold text-base-content/60">
                            #{{ node.id }}
                        </td>

                        <td class="font-medium">
                            {{ node.longitude + ", " +node.latitude }}
                        </td>

                        <td>
                            <div
                                class="flex items-center gap-2 font-medium text-sm"
                            >
                                <!-- Active -->
                                <template v-if="node.status === 'active'">
                                    <span class="relative flex h-2 w-2">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"
                                        ></span>
                                        <span
                                            class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"
                                        ></span>
                                    </span>
                                    <span class="text-base-content/85"
                                        >Active</span
                                    >
                                </template>

                                <!-- Inactive -->
                                <template
                                    v-else-if="node.status === 'inactive'"
                                >
                                    <span
                                        class="h-2 w-2 rounded-full bg-rose-500"
                                    ></span>
                                    <span class="text-base-content/85"
                                        >Inactive</span
                                    >
                                </template>

                                <!-- Maintenance -->
                                <template
                                    v-else-if="
                                        node.status === 'being_maintained'
                                    "
                                >
                                    <span
                                        class="h-2 w-2 rounded-full bg-amber-500"
                                    ></span>
                                    <span class="text-base-content/85"
                                        >Maintenance</span
                                    >
                                </template>

                                <!-- Unknown Just In Case -->
                                <template v-else>
                                    <span
                                        class="h-2 w-2 rounded-full bg-base-300"
                                    ></span>
                                    <span
                                        class="text-base-content/60 capitalize"
                                    >
                                        {{ node.status }}
                                    </span>
                                </template>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
