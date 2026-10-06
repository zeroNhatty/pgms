<script setup lang="ts">
const { logout } = useSanctumAuth();

definePageMeta({
    layout: "operational-logged-in",
    middleware: ["sanctum:auth"],
});

const user = useUserSession()["user"];
const isLoggingOut = ref(false);

async function logoutUser() {
    isLoggingOut.value = true;
    try {
        await logout();
    } catch (err) {
        console.error("Logout failed:", err);
    } finally {
        isLoggingOut.value = false;
    }
}
</script>

<template>
    <div class="max-w-xl mx-auto p-4 space-y-6">
        <!-- Main Profile Card -->
        <div class="card bg-base-100 shadow-sm border border-base-200 w-full">
            <div class="card-body p-6 space-y-6">
                <!-- Header with Avatar & Role -->
                <div class="flex items-center justify-between pb-4 border-b border-base-200">
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="bg-neutral text-neutral-content rounded-full w-12 h-12 font-bold text-sm uppercase">
                                {{ user?.firstname?.[0] ?? "" }}{{ user?.lastname?.[0] ?? "" }}
                            </div>
                        </div>
                        <div>
                            <h2 class="card-title text-xl font-bold leading-tight">
                                {{ user?.firstname }} {{ user?.middle_name ?? "" }} {{ user?.lastname }}
                            </h2>
                            <p class="text-xs text-base-content/60 font-mono mt-0.5">
                                {{ user?.email }}
                            </p>
                        </div>
                    </div>

                    <!-- Role Badge -->
                    <div>
                        <span
                            v-if="user?.role === 'manager'"
                            class="badge badge-neutral font-semibold capitalize text-xs"
                        >
                            Manager
                        </span>
                        <span
                            v-else
                            class="badge badge-ghost font-semibold capitalize text-xs"
                        >
                            Technician
                        </span>
                    </div>
                </div>

                <!-- Account Information Grid -->
                <div class="space-y-3">

                    <div class="divide-y divide-base-200 rounded-xl border border-base-200 bg-base-200/30 text-sm overflow-hidden">
                        <!-- First Name -->
                        <div class="flex items-center justify-between px-4 py-3 hover:bg-base-200/60 transition-colors">
                            <span class="text-xs font-semibold uppercase tracking-wider text-base-content/60">First Name</span>
                            <span class="font-medium text-base-content">{{ user?.firstname ?? "—" }}</span>
                        </div>

                        <!-- Middle Name -->
                        <div class="flex items-center justify-between px-4 py-3 hover:bg-base-200/60 transition-colors">
                            <span class="text-xs font-semibold uppercase tracking-wider text-base-content/60">Middle Name</span>
                            <span class="font-medium text-base-content">{{ user?.middle_name || "—" }}</span>
                        </div>

                        <!-- Last Name -->
                        <div class="flex items-center justify-between px-4 py-3 hover:bg-base-200/60 transition-colors">
                            <span class="text-xs font-semibold uppercase tracking-wider text-base-content/60">Last Name</span>
                            <span class="font-medium text-base-content">{{ user?.lastname ?? "—" }}</span>
                        </div>

                        <!-- System Role -->
                        <div class="flex items-center justify-between px-4 py-3 hover:bg-base-200/60 transition-colors">
                            <span class="text-xs font-semibold uppercase tracking-wider text-base-content/60">System Role</span>
                            <span class="font-medium text-base-content capitalize">{{ user?.role ?? "—" }}</span>
                        </div>

                        <!-- Date of Birth -->
                        <div class="flex items-center justify-between px-4 py-3 hover:bg-base-200/60 transition-colors">
                            <span class="text-xs font-semibold uppercase tracking-wider text-base-content/60">Date of Birth</span>
                            <span class="font-medium text-base-content font-mono text-xs">{{ user?.date_of_birth ?? "—" }}</span>
                        </div>

                        <!-- Gender -->
                        <div class="flex items-center justify-between px-4 py-3 hover:bg-base-200/60 transition-colors">
                            <span class="text-xs font-semibold uppercase tracking-wider text-base-content/60">Gender</span>
                            <span class="font-medium text-base-content capitalize">{{ user?.gender ?? "—" }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer / Actions -->
                <div class="pt-4 border-t border-base-200 flex items-center justify-between">
                    <span class="text-xs text-base-content/50">
                        Operational Session Active
                    </span>

                    <button
                        class="btn btn-sm btn-error btn-outline"
                        :disabled="isLoggingOut"
                        @click="logoutUser"
                    >
                        <span v-if="isLoggingOut" class="loading loading-spinner loading-xs"></span>
                        <span v-else>Logout</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
