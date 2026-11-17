<script setup>
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import { computed, reactive, ref, watch } from 'vue'

const props = defineProps({
  visible: { type: Boolean, required: true },
  title: { type: String, default: '' },
  submitting: { type: Boolean, default: false },
  fieldErrors: { type: Object, default: () => ({}) },
  formError: { type: String, default: null },
})
const emit = defineEmits(['update:visible', 'submit'])

const form = reactive({ name: '', email: '' })
const localErrors = reactive({ name: '', email: '' })
const touchedSubmit = ref(false)

watch(
  () => props.visible,
  (open) => {
    if (open) {
      form.name = ''
      form.email = ''
      localErrors.name = ''
      localErrors.email = ''
    }
  },
)

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

function validate() {
  localErrors.name = form.name.trim() ? '' : 'Введите имя'
  localErrors.email = !form.email.trim()
    ? 'Введите email'
    : EMAIL_RE.test(form.email.trim())
      ? ''
      : 'Введите корректный email'
  return !localErrors.name && !localErrors.email
}

function onSubmit() {
  touchedSubmit.value = true
  if (!validate()) return
  emit('submit', { name: form.name.trim(), email: form.email.trim() })
}

const nameError = computed(() => localErrors.name || props.fieldErrors.customer_name?.[0] || '')
const emailError = computed(() => localErrors.email || props.fieldErrors.customer_email?.[0] || '')
</script>

<template>
  <Dialog
    :visible="visible"
    modal
    :header="title"
    :closable="!submitting"
    :close-on-escape="!submitting"
    :style="{ width: '26rem' }"
    :breakpoints="{ '575px': '92vw' }"
    @update:visible="emit('update:visible', $event)"
  >
    <form class="booking-form" novalidate @submit.prevent="onSubmit">
      <Message v-if="formError" severity="error" :closable="false">{{ formError }}</Message>

      <div class="booking-form__field">
        <label for="customer-name">Имя</label>
        <InputText
          id="customer-name"
          v-model="form.name"
          autocomplete="name"
          maxlength="255"
          fluid
          :invalid="!!nameError"
          :disabled="submitting"
          @input="localErrors.name = ''"
        />
        <small v-if="nameError" class="booking-form__error">{{ nameError }}</small>
      </div>

      <div class="booking-form__field">
        <label for="customer-email">Email</label>
        <InputText
          id="customer-email"
          v-model="form.email"
          type="email"
          autocomplete="email"
          maxlength="255"
          fluid
          :invalid="!!emailError"
          :disabled="submitting"
          @input="localErrors.email = ''"
        />
        <small v-if="emailError" class="booking-form__error">{{ emailError }}</small>
      </div>

      <div class="booking-form__actions">
        <Button
          type="button"
          label="Закрыть"
          severity="secondary"
          variant="text"
          :disabled="submitting"
          @click="emit('update:visible', false)"
        />
        <Button
          type="submit"
          :label="formError ? 'Повторить' : 'Забронировать'"
          icon="pi pi-check"
          :loading="submitting"
        />
      </div>
    </form>
  </Dialog>
</template>

<style scoped>
.booking-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.booking-form__field {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}
.booking-form__error {
  color: var(--p-red-500, #ef4444);
}
.booking-form__actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}
</style>
