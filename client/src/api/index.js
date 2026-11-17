
const BASE = (import.meta.env.VITE_API_URL ?? '/api').replace(/\/$/, '')

export class ApiError extends Error {
  constructor({ status, message, errors = {} }) {
    super(message)
    this.status = status
    this.errors = errors
  }
}

async function request(path, { method = 'GET', body } = {}) {
  let res
  try {
    res = await fetch(`${BASE}${path}`, {
      method,
      headers: {
        Accept: 'application/json',
        ...(body ? { 'Content-Type': 'application/json' } : {}),
      },
      body: body ? JSON.stringify(body) : undefined,
    })
  } catch {
    throw new ApiError({
      status: 0,
      message: 'Нет связи с сервером. Проверьте соединение и повторите попытку.',
    })
  }

  if (res.status === 204) return null

  let data = null
  try {
    data = await res.json()
  } catch {
  }

  if (!res.ok) {
    throw new ApiError({
      status: res.status,
      message:
        res.status >= 500
          ? 'Ошибка сервера. Повторите попытку позже.'
          : (data?.message ?? `Ошибка запроса (${res.status}).`),
      errors: data?.errors ?? {},
    })
  }

  return data
}

export const fetchSlots = () => request('/slots').then((r) => r.data)

export const createBooking = ({ slotId, name, email }) =>
  request('/bookings', {
    method: 'POST',
    body: { slot_id: slotId, customer_name: name, customer_email: email },
  })

export const cancelBooking = (bookingId) =>
  request(`/bookings/${bookingId}`, { method: 'DELETE' })
