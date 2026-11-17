import { ref } from 'vue'
import { fetchSlots } from '../api'

export function useSlots() {
  const slots = ref([])
  const loading = ref(false)
  const loaded = ref(false)
  const loadError = ref(null)

  async function load() {
    loading.value = true
    loadError.value = null
    try {
      slots.value = await fetchSlots()
      loaded.value = true
    } catch (e) {
      loadError.value = e.message
    } finally {
      loading.value = false
    }
  }

  return { slots, loading, loaded, loadError, load }
}
