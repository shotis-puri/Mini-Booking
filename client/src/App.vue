<script setup>
import Button from 'primevue/button'
import ConfirmDialog from 'primevue/confirmdialog'
import Message from 'primevue/message'
import ProgressSpinner from 'primevue/progressspinner'
import SelectButton from 'primevue/selectbutton'
import Toast from 'primevue/toast'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import { computed, onMounted, ref, watch } from 'vue'
import BookingDialog from './components/BookingDialog.vue'
import SlotCard from './components/SlotCard.vue'
import { useBookings } from './composables/useBookings'
import { useSlots } from './composables/useSlots'
import { dateLabel, dateOf, offsetOf, timeOf } from './utils/datetime'

const toast = useToast()
const confirm = useConfirm()

const { slots, loading, loaded, loadError, load } = useSlots()
const { submitting, cancellingId, fieldErrors, formError, actionError, resetFormState, book, cancel } =
  useBookings(load)

const selectedDate = ref(null)

const dateOptions = computed(() => {
  const dates = [...new Set(slots.value.map((s) => dateOf(s.starts_at)))]
  return dates.map((value) => ({ value, label: dateLabel(value) }))
})

watch(dateOptions, (options) => {
  if (!options.length) return
  if (selectedDate.value && options.some((o) => o.value === selectedDate.value)) return
  const withFree = options.find((o) =>
    slots.value.some((s) => dateOf(s.starts_at) === o.value && s.status === 'available'),
  )
  selectedDate.value = (withFree ?? options[0]).value
})

const visibleSlots = computed(() =>
  slots.value.filter((s) => dateOf(s.starts_at) === selectedDate.value),
)

const timezone = computed(() => (slots.value.length ? offsetOf(slots.value[0].starts_at) : ''))

const busy = computed(() => submitting.value || cancellingId.value !== null)

const dialogVisible = ref(false)
const bookingSlot = ref(null)

const dialogTitle = computed(() =>
  bookingSlot.value
    ? `${dateLabel(dateOf(bookingSlot.value.starts_at))}, ${timeOf(bookingSlot.value.starts_at)}–${timeOf(bookingSlot.value.ends_at)}`
    : '',
)

function openBooking(item) {
  bookingSlot.value = item
  resetFormState()
  dialogVisible.value = true
}

async function submitBooking(form) {
  const result = await book(bookingSlot.value.id, form)

  if (result.status === 'created') {
    dialogVisible.value = false
    toast.add({ severity: 'success', summary: 'Готово', detail: 'Слот забронирован.', life: 4000 })
  } else if (result.status === 'conflict') {
    dialogVisible.value = false
    toast.add({ severity: 'warn', summary: 'Слот недоступен', detail: result.message, life: 7000 })
  }
}

function askCancel(item) {
  confirm.require({
    header: 'Отменить бронь?',
    message: `Слот ${timeOf(item.starts_at)}–${timeOf(item.ends_at)} снова станет свободным.`,
    icon: 'pi pi-exclamation-triangle',
    rejectProps: { label: 'Нет', severity: 'secondary', variant: 'text' },
    acceptProps: { label: 'Отменить бронь', severity: 'danger' },
    accept: () => runCancel(item.booking_id),
  })
}

async function runCancel(bookingId) {
  const result = await cancel(bookingId)
  if (result === 'cancelled') {
    toast.add({ severity: 'success', summary: 'Готово', detail: 'Бронь отменена.', life: 4000 })
  } else if (result === 'gone') {
    toast.add({ severity: 'info', summary: 'Бронь не найдена', detail: 'Список обновлён.', life: 5000 })
  }
}

onMounted(load)
</script>

<template>
  <main class="page">
    <header class="page__header">
      <div>
        <h1>Переговорная</h1>
        <p v-if="timezone" class="page__subtitle">
          Ежедневно 09:00–18:00 · время расписания: {{ timezone }}
        </p>
      </div>
      <Button
        icon="pi pi-refresh"
        label="Обновить"
        severity="secondary"
        variant="outlined"
        :loading="loading && loaded"
        :disabled="busy || loading"
        @click="load"
      />
    </header>

    <Message v-if="loadError" severity="error" class="page__message">
      <div class="page__message-row">
        <span>{{ loadError }}</span>
        <Button label="Повторить" size="small" severity="secondary" :loading="loading" @click="load" />
      </div>
    </Message>

    <Message v-if="actionError" severity="error" class="page__message">
      <div class="page__message-row">
        <span>{{ actionError.message }}</span>
        <Button label="Повторить" size="small" severity="secondary" :disabled="busy" @click="actionError.retry()" />
      </div>
    </Message>

    <div v-if="!loaded && loading" class="page__loading">
      <ProgressSpinner aria-label="Загрузка расписания" />
    </div>

    <template v-else-if="loaded">
      <SelectButton
        v-model="selectedDate"
        :options="dateOptions"
        option-label="label"
        option-value="value"
        :allow-empty="false"
        :disabled="busy"
        class="page__dates"
        aria-label="Дата"
      />

      <section class="page__grid">
        <SlotCard
          v-for="item in visibleSlots"
          :key="item.id"
          :item="item"
          :disabled="busy"
          :cancelling="cancellingId === item.booking_id && item.booking_id !== null"
          @book="openBooking"
          @cancel="askCancel"
        />
      </section>
    </template>

    <BookingDialog
      v-model:visible="dialogVisible"
      :title="dialogTitle"
      :submitting="submitting"
      :field-errors="fieldErrors"
      :form-error="formError"
      @submit="submitBooking"
    />

    <ConfirmDialog />
    <Toast position="top-center" />
  </main>
</template>

<style scoped>
.page {
  max-width: 64rem;
  margin: 0 auto;
  padding: 1rem;
}
.page__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
}
.page__header h1 {
  margin: 0;
  font-size: 1.75rem;
}
.page__subtitle {
  margin: 0.25rem 0 0;
  color: var(--p-text-muted-color, #666);
}
.page__message {
  margin-bottom: 1rem;
}
.page__message-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}
.page__loading {
  display: flex;
  justify-content: center;
  padding: 4rem 0;
}
.page__dates {
  display: flex;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}
.page__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr));
  gap: 0.75rem;
}
@media (max-width: 575px) {
  .page__header {
    flex-direction: column;
  }
}
</style>
