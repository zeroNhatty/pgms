<script setup lang="ts">
import { z } from "zod";

const { $echo } = useNuxtApp();
const client = useSanctumClient();

definePageMeta({
    layout: "operational-logged-in",
    middleware: ["sanctum:auth"],
});

// Zod schemas
const NodeSchema = z.object({
    id: z.number().int(),
    location: z.string().optional().nullable(),
    longitude: z.coerce.number().optional().nullable(),
    latitude: z.coerce.number().optional().nullable(),
    status: z.string().optional().nullable(),
});

const UserSchema = z.object({
    id: z.number().int(),
    firstname: z.string().nullable().optional(),
    middle_name: z.string().nullable().optional(),
    lastname: z.string().nullable().optional(),
});

const TicketSchema = z.object({
    id: z.number().int(),
    status: z.string(),
    node_id: z.number().int(),
    assignee_id: z.number().int().nullable(),
    node: NodeSchema.optional().nullable(),
    assignee: UserSchema.optional().nullable(),
});

export type Ticket = z.infer<typeof TicketSchema>;

const PaginatedTicketsSchema = z.object({
    data: z.array(TicketSchema),
    current_page: z.number(),
    last_page: z.number(),
    per_page: z.number(),
    total: z.number(),
});

type PaginatedTickets = z.infer<typeof PaginatedTicketsSchema>;


const filterTicketStatus = ref<string>("all");
const filterNodeStatus = ref<string>("all");
const currentPage = ref<number>(1);

//restet page when filters change
watch([filterTicketStatus, filterNodeStatus], () => {
    currentPage.value = 1;
});

// Fetch paginated tickets
const {
    data: paginatedData,
    error,
    status,
    refresh,
} = await useAsyncData<PaginatedTickets>(
    () => `tickets-${filterTicketStatus.value}-${filterNodeStatus.value}-${currentPage.value}`,
    async () => {
        const queryParams = new URLSearchParams();

        if (filterTicketStatus.value !== "all") {
            queryParams.set("status", filterTicketStatus.value);
        }
        if (filterNodeStatus.value !== "all") {
            queryParams.set("node_status", filterNodeStatus.value);
        }
        queryParams.set("page", String(currentPage.value));
        queryParams.set("per_page", "8");

        const rawData = await client(`/api/tickets?${queryParams.toString()}`);
        return PaginatedTicketsSchema.parse(rawData);
    },
    {
        watch: [filterTicketStatus, filterNodeStatus, currentPage],
    }
);

const selectedTicket = ref<Ticket | null>(null);
const modalRef = ref<HTMLDialogElement | null>(null);

function openModal(ticket: Ticket) {
    selectedTicket.value = ticket;
    nextTick(() => {
        modalRef.value?.showModal();
    });
}

function handleTicketUpsert(rawTicket: unknown) {
    if (!paginatedData.value?.data) return;

    try {
        const ticketData = TicketSchema.parse(rawTicket);
        const index = paginatedData.value.data.findIndex((t) => t.id === ticketData.id);

        if (index !== -1) {
            const updated = { ...paginatedData.value.data[index], ...ticketData };
            paginatedData.value.data.splice(index, 1, updated);

            if (selectedTicket.value?.id === ticketData.id) {
                selectedTicket.value = updated;
            }
        } else {
            refresh();
        }
    } catch (err) {
        console.error("Zod Validation Failed on WS Payload:", err);
        refresh();
    }
}

onMounted(() => {
    $echo
        .channel("tickets")
        .listen(".ticket.updated", (event: { ticket: unknown }) => {
            handleTicketUpsert(event.ticket);
        })
        .listen(".ticket.created", (event: { ticket: unknown }) => {
            handleTicketUpsert(event.ticket);
        });
});

onUnmounted(() => {
    $echo.leaveChannel("tickets");
});
</script>

<template>
    <div class="max-w-2xl mx-auto p-4 space-y-4">
        <!-- Main Card -->
        <div class="card bg-base-100 shadow-sm border border-base-200 w-full">
            <div class="card-body p-6">
                <!-- Header -->
                <div class="flex justify-between items-center pb-3 border-b border-base-200">
                    <h2 class="card-title text-xl font-bold">
                        Incident Tickets
                    </h2>
                    <span v-if="paginatedData" class="badge badge-neutral text-xs">
                        {{ paginatedData.total }} Total
                    </span>
                </div>

                <!-- Filters -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    <!-- Ticket Status Filter -->
                    <div class="form-control w-full">
                        <label class="label py-1">
                            <span class="label-text text-xs font-semibold uppercase text-base-content/60">
                                Ticket Status
                            </span>
                        </label>
                        <select v-model="filterTicketStatus" class="select select-bordered select-sm w-full">
                            <option value="all">All Ticket Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="assigned">Being Maintained</option>
                            <option value="solved">Solved</option>
                        </select>
                    </div>

                    <!-- Node Condition Filter -->
                    <div class="form-control w-full">
                        <label class="label py-1">
                            <span class="label-text text-xs font-semibold uppercase text-base-content/60">
                                Node Condition
                            </span>
                        </label>
                        <select v-model="filterNodeStatus" class="select select-bordered select-sm w-full">
                            <option value="all">All Node Conditions</option>
                            <option value="inactive">Node Offline (Inactive)</option>
                            <option value="active">Node Online (Active)</option>
                            <option value="being_maintained">Node Under Maintenance</option>
                        </select>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="status === 'pending'" class="flex justify-center p-12">
                    <span class="loading loading-spinner loading-md text-primary"></span>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="alert alert-error my-4">
                    <span>Error loading tickets: {{ error.message }}</span>
                </div>

                <!-- Tickets List -->
                <div
                    v-else-if="paginatedData?.data && paginatedData.data.length > 0"
                    class="space-y-3 mt-4"
                >
                    <div
                        v-for="ticket in paginatedData.data"
                        :key="ticket.id"
                        class="p-4 bg-base-200/50 hover:bg-base-200/80 transition-colors rounded-lg space-y-2 border border-base-content/5"
                    >
                        <!-- Top Row -->
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-sm tracking-wide">
                                Ticket #{{ ticket.id }}
                            </span>

                            <div class="flex items-center gap-2">
                                <!-- Node live status pill -->
                                <span
                                    v-if="ticket.node?.status === 'active'"
                                    class="badge badge-xs badge-success text-white py-1 px-2 text-[10px]"
                                >
                                    Node Online
                                </span>
                                <span
                                    v-else-if="ticket.node?.status === 'inactive'"
                                    class="badge badge-xs badge-error text-white py-1 px-2 text-[10px]"
                                >
                                    Node Offline
                                </span>

                                <!-- Ticket status badge -->
                                <div
                                    v-if="ticket.status === 'solved'"
                                    class="badge badge-success gap-1 text-white text-xs"
                                >
                                    Solved
                                </div>
                                <div
                                    v-else-if="ticket.status === 'assigned'"
                                    class="badge badge-warning gap-1 text-xs"
                                >
                                    Being Maintained
                                </div>
                                <div v-else class="badge badge-error gap-1 text-white text-xs">
                                    Pending
                                </div>
                            </div>
                        </div>

                        <!-- Ticket details -->
                        <div class="text-sm space-y-1">
                            <p>
                                <strong>Node:</strong>
                                <span class="text-base-content/80 ml-1">
                                    <template v-if="ticket.node?.latitude && ticket.node?.longitude">
                                        ({{ ticket.node.latitude }}, {{ ticket.node.longitude }})
                                    </template>
                                    <template v-else-if="ticket.node?.location">
                                        {{ ticket.node.location }}
                                    </template>
                                    <template v-else>
                                        Node #{{ ticket.node_id }}
                                    </template>
                                </span>
                            </p>
                            <p>
                                <strong>Assignee:</strong>
                                <span v-if="ticket.assignee" class="text-base-content/80 ml-1">
                                    {{ ticket.assignee.firstname }} {{ ticket.assignee.lastname }}
                                </span>
                                <span v-else class="text-base-content/50 italic ml-1">
                                    Unassigned
                                </span>
                            </p>
                        </div>

                        <div class="flex justify-end pt-1">
                            <button
                                class="btn btn-sm btn-outline"
                                @click="openModal(ticket)"
                            >
                                More...
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="py-12 text-center text-base-content/60">
                    No tickets match the selected filters.
                </div>

                <!-- Pagination Footer -->
                <div
                    v-if="paginatedData && paginatedData.last_page > 1"
                    class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-base-200 mt-4"
                >
                    <span class="text-xs text-base-content/60">
                        Page {{ paginatedData.current_page }} of {{ paginatedData.last_page }}
                        ({{ paginatedData.total }} tickets)
                    </span>

                    <div class="join">
                        <button
                            class="join-item btn btn-sm"
                            :disabled="currentPage <= 1"
                            @click="currentPage--"
                        >
                            « Prev
                        </button>
                        <button class="join-item btn btn-sm btn-ghost cursor-default">
                            {{ currentPage }}
                        </button>
                        <button
                            class="join-item btn btn-sm"
                            :disabled="currentPage >= paginatedData.last_page"
                            @click="currentPage++"
                        >
                            Next »
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <TicketModal
            ref="modalRef"
            :ticket="selectedTicket"
            @claimed="refresh"
            @solved="refresh"
        />
    </div>
</template>
