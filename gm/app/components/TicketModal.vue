<script setup lang="ts">
import type { Ticket } from '~/pages/operational/tickets.vue';

const user = useUserSession()['user']
const client = useSanctumClient();
const emit = defineEmits(['claimed', 'solved']);

const props = defineProps<{
  ticket: Ticket | null;
}>();

// Expose the modal element to parent
const dialogRef = ref<HTMLDialogElement | null>(null);

defineExpose({
  showModal: () => dialogRef.value?.showModal(),
  close: () => dialogRef.value?.close(),
})

 async function technicianClaim() {
  if (!props.ticket || !user.value?.id) return

  try {
    const updatedTicket = await client(`/api/tickets/${props.ticket.id}`, {
      method: 'PUT',
      body: {
        assignee_id: user.value.id,
        status: 'assigned',
      },
    })

    console.log('Ticket claimed successfully:', updatedTicket)

    const modalEl = document.getElementById('someMo') as HTMLDialogElement | null
    modalEl?.close()

    emit('claimed')

  } catch (err) {
    console.error('Failed to claim ticket:', err)
  }
}

async function ticketResolve() {
  if (!props.ticket || !user.value?.id) return

  try {
    const updatedTicket = await client(`/api/tickets/${props.ticket.id}`, {
      method: 'PUT',
      body: {
        assignee_id: props.ticket.assignee_id ?? user.value.id,
        status: 'solved',
      },
    })

    console.log('Ticket marked solved successfully:', updatedTicket)

    const modalEl = document.getElementById('someMo') as HTMLDialogElement | null
    modalEl?.close()

    emit('solved')

  } catch (err) {
    console.error('Failed to solve ticket:', err)
  }
}
</script>

<template>
  <dialog ref="dialogRef" class="modal modal-bottom sm:modal-middle">
    <div v-if="props.ticket" class="modal-box">
      <h3 class="text-lg font-bold">Ticket #{{ props.ticket.id }}</h3>

      <div class="py-4 space-y-2">
        <p><strong>Location:</strong> {{ props.ticket.node?.location ?? `Node #${props.ticket.node_id}` }}</p>
        <p><strong>Status:</strong> {{ props.ticket.status }}</p>
        <p>
          <strong>Assignee:</strong>
          <span v-if="props.ticket.assignee">
            {{ props.ticket.assignee.firstname }} {{ props.ticket.assignee.middle_name ?? '' }} {{ props.ticket.assignee.lastname }}
          </span>
          <span v-else class="italic">Unassigned</span>
        </p>
      </div>

      <div class="modal-action">
        <form method="dialog">
          <button v-if="props.ticket.status === 'pending' && user?.role != 'manager'" class="btn btn-primary mr-2" @click="technicianClaim">
            Handle Issue
          </button>
          <button v-if="props.ticket.status === 'assigned' && user?.id === props.ticket.assignee_id" class="btn btn-primary mr-2" @click="ticketResolve">
            Mark as Solved
          </button>
          <button class="btn">Close</button>
        </form>
      </div>
    </div>
  </dialog>
</template>
