<script setup lang="ts">
import { z } from "zod";

const client = useSanctumClient();

const ActiveTicketSchema = z.object({
  id: z.number().int(),
  status: z.string(),
  node_id: z.number().int(),
  node: z
    .object({
      id: z.number().int(),
      longitude: z.coerce.number().optional().nullable(),
      latitude: z.coerce.number().optional().nullable(),
    })
    .optional()
    .nullable(),
});

const ManagedUserSchema = z.object({
  id: z.number().int(),
  firstname: z.string(),
  middle_name: z.string().optional().nullable(),
  lastname: z.string(),
  email: z.string().email(),
  role: z.enum(["technician", "manager"]),
  gender: z.enum(["male", "female"]).optional().nullable(),
  date_of_birth: z.string().optional().nullable(),
  active_tickets_count: z.coerce.number().default(0),
  solved_tickets_count: z.coerce.number().default(0),
  assigned_tickets: z.array(ActiveTicketSchema).optional().default([]),
});

export type ManagedUser = z.infer<typeof ManagedUserSchema>;

const {
  data: users,
  status,
  error,
  refresh,
} = await useAsyncData<ManagedUser[]>("manage-users", async () => {
  const res = await client("/api/users");
  return z.array(ManagedUserSchema).parse(res);
});

// Filters
const search = ref("");
const roleFilter = ref<"all" | "technician" | "manager">("all");
const availabilityFilter = ref<"all" | "busy" | "available">("all");

const filteredUsers = computed(() => {
  if (!users.value) return [];

  return users.value.filter((user) => {
    // Search query
    const fullName = `${user.firstname} ${user.middle_name ?? ""} ${user.lastname}`.toLowerCase();
    const matchesSearch =
      fullName.includes(search.value.toLowerCase()) ||
      user.email.toLowerCase().includes(search.value.toLowerCase());

    // Role filter
    const matchesRole =
      roleFilter.value === "all" || user.role === roleFilter.value;

    // Availability filter
    let matchesAvailability = true;
    if (availabilityFilter.value === "busy") {
      matchesAvailability = user.role === "technician" && user.active_tickets_count > 0;
    } else if (availabilityFilter.value === "available") {
      matchesAvailability = user.role === "technician" && user.active_tickets_count === 0;
    }

    return matchesSearch && matchesRole && matchesAvailability;
  });
});

// Summary Stats
const stats = computed(() => {
  if (!users.value) return { total: 0, technicians: 0, onDuty: 0, solved: 0 };

  const technicians = users.value.filter((u) => u.role === "technician");
  const onDuty = technicians.filter((u) => u.active_tickets_count > 0).length;
  const solved = technicians.reduce((acc, u) => acc + u.solved_tickets_count, 0);

  return {
    total: users.value.length,
    technicians: technicians.length,
    onDuty,
    solved,
  };
});
</script>

<template>
  <div class="space-y-6">
    <!-- Top Stats Overview -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="stat bg-base-200/60 rounded-box p-4 border border-base-content/5">
        <div class="stat-title text-xs">Total Staff</div>
        <div class="stat-value text-2xl text-primary">{{ stats.total }}</div>
        <div class="stat-desc">Managers & Technicians</div>
      </div>

      <div class="stat bg-base-200/60 rounded-box p-4 border border-base-content/5">
        <div class="stat-title text-xs">Technicians</div>
        <div class="stat-value text-2xl">{{ stats.technicians }}</div>
        <div class="stat-desc">Field workforce</div>
      </div>

      <div class="stat bg-base-200/60 rounded-box p-4 border border-base-content/5">
        <div class="stat-title text-xs">Currently Handling</div>
        <div class="stat-value text-2xl text-warning">{{ stats.onDuty }}</div>
        <div class="stat-desc">Active in field</div>
      </div>

      <div class="stat bg-base-200/60 rounded-box p-4 border border-base-content/5">
        <div class="stat-title text-xs">Total Solved</div>
        <div class="stat-value text-2xl text-success">{{ stats.solved }}</div>
        <div class="stat-desc">Lifetime resolved tickets</div>
      </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-center justify-between">
      <div class="w-full sm:w-72">
        <input
          v-model="search"
          type="text"
          placeholder="Search staff by name or email..."
          class="input input-bordered input-sm w-full"
        />
      </div>

      <div class="flex flex-wrap gap-2 w-full sm:w-auto justify-end">
        <!-- Role filter -->
        <select v-model="roleFilter" class="select select-bordered select-sm">
          <option value="all">All Roles</option>
          <option value="technician">Technicians</option>
          <option value="manager">Managers</option>
        </select>

        <!-- Availability filter -->
        <select v-model="availabilityFilter" class="select select-bordered select-sm">
          <option value="all">All Statuses</option>
          <option value="available">Available (Idle)</option>
          <option value="busy">Handling Ticket (Busy)</option>
        </select>

        <button class="btn btn-sm btn-ghost" @click="refresh()">
          ↻ Refresh
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="status === 'pending'" class="flex justify-center p-12">
      <span class="loading loading-spinner loading-lg text-primary"></span>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="alert alert-error">
      <span>Error loading staff: {{ error.message }}</span>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto rounded-box border border-base-200 bg-base-100 shadow-sm">
      <table class="table table-zebra w-full">
        <thead class="bg-base-200/50">
          <tr>
            <th>User</th>
            <th>Role</th>
            <th>Live Status</th>
            <th>Current Assignment</th>
            <th class="text-right">Tickets Solved</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in filteredUsers" :key="user.id" class="hover">
            <!-- Name & Email -->
            <td>
              <div class="flex items-center gap-3">
                <div class="avatar placeholder">
                  <div class="bg-neutral text-neutral-content rounded-full w-9 h-9 text-xs font-bold uppercase">
                    {{ user.firstname[0] }}{{ user.lastname[0] }}
                  </div>
                </div>
                <div>
                  <div class="font-bold text-sm">
                    {{ user.firstname }} {{ user.middle_name ?? "" }} {{ user.lastname }}
                  </div>
                  <div class="text-xs text-base-content/60 font-mono">{{ user.email }}</div>
                </div>
              </div>
            </td>

            <!-- Role Badge -->
            <td>
              <span
                v-if="user.role === 'manager'"
                class="badge badge-neutral badge-sm font-semibold capitalize"
              >
                Manager
              </span>
              <span
                v-else
                class="badge badge-ghost badge-sm font-semibold capitalize"
              >
                Technician
              </span>
            </td>

            <!-- Live Status -->
            <td>
              <template v-if="user.role === 'technician'">
                <!-- Handling Ticket -->
                <span
                  v-if="user.active_tickets_count > 0"
                  class="inline-flex items-center gap-1.5 text-xs font-semibold text-warning"
                >
                  <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                  </span>
                  Handling Issue ({{ user.active_tickets_count }})
                </span>

                <!-- Available -->
                <span
                  v-else
                  class="inline-flex items-center gap-1.5 text-xs font-semibold text-success"
                >
                  <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                  Available
                </span>
              </template>

              <!-- Manager non-field -->
              <span v-else class="text-xs text-base-content/40 italic">
                Office / Dispatch
              </span>
            </td>

            <!-- Current Assignment -->
            <td>
              <div v-if="user.assigned_tickets.length > 0" class="text-xs space-y-1">
                <div
                  v-for="t in user.assigned_tickets"
                  :key="t.id"
                  class="badge badge-outline badge-sm gap-1"
                >
                  <span>Ticket #{{ t.id }}</span>
                  <span v-if="t.node" class="text-base-content/60">
                    (Node #{{ t.node_id }})
                  </span>
                </div>
              </div>
              <span v-else class="text-xs text-base-content/40">
                —
              </span>
            </td>

            <!-- Handled / Solved Count -->
            <td class="text-right">
              <span v-if="user.role === 'technician'" class="font-bold text-sm">
                {{ user.solved_tickets_count }}
                <span class="text-xs text-base-content/50 font-normal">solved</span>
              </span>
              <span v-else class="text-xs text-base-content/40">
                N/A
              </span>
            </td>
          </tr>

          <tr v-if="filteredUsers.length === 0">
            <td colspan="5" class="text-center py-8 text-base-content/60">
              No staff members found matching the criteria.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
