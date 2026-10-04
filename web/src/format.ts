import dayjs from 'dayjs';

export const today = () => dayjs().format('YYYY-MM-DD');

// Amounts arrive formatted ("1,150.00") or as numbers.
export const toNumber = (value: string | number | null | undefined) =>
  typeof value === 'number' ? value : Number(String(value ?? '0').replace(/[^0-9.-]/g, '')) || 0;

const money = new Intl.NumberFormat('ar-SA-u-nu-latn', { maximumFractionDigits: 2 });
export const formatMoney = (value: number) => `${money.format(value)} ر.س`;

export const formatDate = (value?: string | null) => (value ? dayjs(value).format('YYYY/MM/DD') : '—');

export function daysLate(endDate?: string | null) {
  if (!endDate) return 0;
  return Math.max(0, dayjs().startOf('day').diff(dayjs(endDate).startOf('day'), 'day'));
}

export function isTaskOverdue(task: { status: number; is_overdue?: boolean; end_date?: string | null }) {
  if (typeof task.is_overdue === 'boolean') return task.is_overdue;
  // Older back-end without is_overdue: a date-only end date is due by the end of that day.
  return task.status === 1 && !!task.end_date && dayjs(task.end_date).endOf('day').isBefore(dayjs());
}
