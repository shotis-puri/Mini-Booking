
export const dateOf = (iso) => iso.slice(0, 10)
export const timeOf = (iso) => iso.slice(11, 16)

export function offsetOf(iso) {
  const offset = iso.slice(19)
  return offset === 'Z' ? 'UTC' : `UTC${offset}`
}

export function dateLabel(date) {
  const [y, m, d] = date.split('-').map(Number)
  return new Intl.DateTimeFormat('ru-RU', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    timeZone: 'UTC',
  }).format(new Date(Date.UTC(y, m - 1, d)))
}
