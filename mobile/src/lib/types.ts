export type ApiUser = {
  id: number;
  firstname: string;
  middlename: string;
  lastname: string;
  name: string;
  email: string;
  role: string;
  birthday: string | null;
  cellphone_no: string | null;
  address: string | null;
  parents_name_guardian: string | null;
  parents_phone_no: string | null;
  fb_messenger_account: string | null;
  profile_pic: string | null;
  profile_pic_url: string | null;
  created_at: string | null;
};

export type AuthSession = {
  token: string;
  token_type: string;
  expires_at: string | null;
  user: ApiUser;
};

export type Enrollment = {
  id: number;
  program_type: string;
  status: 'pending' | 'enrolled' | 'completed' | string;
  batch: string | null;
  enrolled_at: string | null;
  enrollment_date: string | null;
  insured: boolean;
  total_fee: number | null;
  created_at: string | null;
};

export type ExamResult = {
  diagnostic_exam: number | null;
  preboard_exam: number | null;
  compre_exam: number | null;
};

export type Payment = {
  id: number;
  amount: number | null;
  reference_number: string | null;
  payment_method: string | null;
  payment_type: string | null;
  status: string;
  receipt: string | null;
  receipt_url: string | null;
  payment_date: string | null;
  created_at: string | null;
};

export type Announcement = {
  id: number;
  title: string;
  message: string;
  category: string;
  audience: string;
  created_at: string | null;
};

export type DashboardPayload = {
  user: ApiUser;
  enrollment: Enrollment | null;
  total_paid: number;
  balance: number | null;
  exam_result: ExamResult | null;
  recent_payments: Payment[];
  announcements: Announcement[];
};
