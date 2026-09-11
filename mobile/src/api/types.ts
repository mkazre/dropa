export type LockerSize = 'S' | 'M' | 'L' | 'XL';

export type User = {
  id: number;
  name: string | null;
  email: string;
  phone?: string | null;
  groups: string[];
  property_id?: number | null;
};

export type Property = {
  id: number;
  name: string;
  type: 'complex' | 'public_site';
  address: string | null;
  logo_url: string | null;
  brand_color: string | null;
  reservation_hold_hours: number;
};

export type Unit = {
  id: number;
  property_id: number;
  unit_number: string;
};

export type Me = {
  id: number;
  name: string | null;
  email: string;
  phone: string | null;
  groups: string[];
  property: Property | null;
  unit: Unit | null;
};

export type Reservation = {
  id: number;
  property_id: number;
  tenant_id: number;
  locker_id: number | null;
  size_requested: LockerSize;
  deposit_code: string;
  status: 'pending' | 'held' | 'deposited' | 'expired' | 'cancelled';
  expires_at: string;
  created_at: string;
};

export type Parcel = {
  id: number;
  reservation_id: number;
  sender_name: string | null;
  sender_contact: string | null;
  pickup_pin: string;
  qr_token: string;
  delegate_code: string | null;
  photo_url: string | null;
  deposited_at: string | null;
  collected_at: string | null;
  status: 'awaiting_deposit' | 'awaiting_collection' | 'collected';
};

export type PaymentMethod = {
  method: 'ozow' | 'payfast' | 'manual';
  instructions: string | null;
};

export type PaymentQuote = {
  amount: number;
  methods: PaymentMethod[];
};

export type PaymentInitiateResult = {
  redirect_url: string | null;
  instructions: string | null;
  reference: string;
  payment_id: number;
  amount: number;
};

export type LockerAvailability = Record<LockerSize, number>;
