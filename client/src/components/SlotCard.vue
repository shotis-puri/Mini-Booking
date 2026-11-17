<script setup>
import Button from 'primevue/button'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import { computed } from 'vue'
import { timeOf } from '../utils/datetime'

const props = defineProps({
  item: { type: Object, required: true },
  disabled: { type: Boolean, default: false },
  cancelling: { type: Boolean, default: false },
})
defineEmits(['book', 'cancel'])

const STATUS = {
  available: { label: 'Свободно', severity: 'success' },
  booked: { label: 'Занято', severity: 'danger' },
  past: { label: 'Прошлые', severity: 'secondary' },
}

const status = computed(() => STATUS[props.item.status])
const time = computed(() => `${timeOf(props.item.starts_at)}–${timeOf(props.item.ends_at)}`)
</script>

<template>
  <Card :class="['slot-card', `slot-card--${item.status}`]">
    <template #content>
      <div class="slot-card__row">
        <span class="slot-card__time">{{ time }}</span>
        <Tag :value="status.label" :severity="status.severity" />
      </div>

      <Button
        v-if="item.status === 'available'"
        label="Забронировать"
        icon="pi pi-calendar-plus"
        class="slot-card__action"
        fluid
        :disabled="disabled"
        @click="$emit('book', item)"
      />
      <Button
        v-else-if="item.status === 'booked'"
        label="Отменить бронь"
        icon="pi pi-times"
        severity="danger"
        variant="outlined"
        class="slot-card__action"
        fluid
        :loading="cancelling"
        :disabled="disabled"
        @click="$emit('cancel', item)"
      />
    </template>
  </Card>
</template>

<style scoped>
.slot-card--past {
  opacity: 0.55;
}
.slot-card__row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}
.slot-card__time {
  font-size: 1.25rem;
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}
.slot-card__action {
  margin-top: 1rem;
}
</style>
