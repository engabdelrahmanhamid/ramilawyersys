export type Admin = { id: number; name: string; email?: string; super: number; branch_id?: number | null; token?: string };
export type Named = { id: number; name: string } | null;
export type Client = { id: number; name: string; phone?: string } | null;
export type CaseRef = { id: number; name: string; number?: string; client?: Client } | null;

export type Session = {
  id: number;
  type: number; // 1 = session (جلسة), 2 = revision (مراجعة)
  gregorian_date: string;
  session_time?: string | null;
  destination?: string | null;
  session_requirements?: string | null;
  status?: Named;
  admin?: Named;
  client?: Client;
  case?: CaseRef;
};

export type Task = {
  id: number;
  description?: string | null;
  end_date?: string | null;
  status: number; // 1 = open, 2 = done, 3 = done late
  is_overdue?: boolean;
  task_type?: Named;
  admin?: Named;
  created_by?: Named;
  client_id?: Client;
};

export type Receipt = {
  id: number;
  date: string;
  total_amount: string | number;
  paid_amount: string | number | null;
  unpaid_amount: string | number | null;
  payment_status: number;
  case?: CaseRef;
  service?: { id: number; name: string; client?: Client } | null;
};

// List rows. The back-end returns the same { data, pagination } envelope as the other lists.
export type ClientListItem = {
  id: number;
  name: string;
  phone?: string | null;
  email?: string | null;
  type?: Named;
  status?: Named;
  branch?: Named;
};

export type CaseListItem = {
  id: number;
  name: string;
  number?: string | null;
  client?: Client;
  type?: Named;
  status?: Named;
};

export type Appointment = {
  id: number;
  gregorian_date?: string | null;
  date?: string | null;
  time?: string | null;
  destination?: string | null;
  note?: string | null;
  status?: Named | number;
  admin?: Named;
  client?: Client;
};

export type Contact = {
  id: number;
  date?: string | null;
  description?: string | null;
  method?: number | null; // channel: phone / whatsapp / visit ...
  contact_reason?: Named;
  type?: Named;
  admin?: Named;
  client?: Client;
};
