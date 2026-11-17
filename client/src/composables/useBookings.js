import { ref } from 'vue'
import { ApiError, cancelBooking, createBooking } from '../api'

export function useBookings(reload) {
  const submitting = ref(false)
  const cancellingId = ref(null)
  const fieldErrors = ref({})
  const formError = ref(null)
  const actionError = ref(null)

  function resetFormState() {
    fieldErrors.value = {}
    formError.value = null
  }

  async function book(slotId, { name, email }) {
    resetFormState()
    submitting.value = true
    try {
      await createBooking({ slotId, name, email })
      await reload()
      return { status: 'created' }
    } catch (e) {
      if (!(e instanceof ApiError)) throw e

      if (e.status === 422) {
        fieldErrors.value = e.errors
        if (e.errors.slot_id) formError.value = e.errors.slot_id[0]
        return { status: 'invalid' }
      }
      if (e.status === 409) {
        await reload()
        return { status: 'conflict', message: e.message }
      }
      formError.value = e.message
      return { status: 'error' }
    } finally {
      submitting.value = false
    }
  }

  async function cancel(bookingId) {
    actionError.value = null
    cancellingId.value = bookingId
    try {
      await cancelBooking(bookingId)
      await reload()
      return 'cancelled'
    } catch (e) {
      if (!(e instanceof ApiError)) throw e

      if (e.status === 404) {
        await reload()
        return 'gone'
      }
      actionError.value = { message: e.message, retry: () => cancel(bookingId) }
      return 'error'
    } finally {
      cancellingId.value = null
    }
  }

  return {
    submitting,
    cancellingId,
    fieldErrors,
    formError,
    actionError,
    resetFormState,
    book,
    cancel,
  }
}
