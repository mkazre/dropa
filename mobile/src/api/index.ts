import { apiRequest } from './client';
import type {
  LockerAvailability,
  LockerSize,
  Me,
  Parcel,
  PaymentInitiateResult,
  PaymentQuote,
  Reservation,
  Resident,
} from './types';

export const AuthApi = {
  login: (email: string, password: string) =>
    apiRequest<{ token: string; user: unknown }>('/auth/login', { method: 'POST', body: { email, password }, auth: false }),
  logout: () => apiRequest<void>('/auth/logout', { method: 'POST' }),
  changePassword: (new_password: string) =>
    apiRequest<void>('/auth/change-password', { method: 'POST', body: { new_password } }),
};

export const AccountApi = {
  me: () => apiRequest<Me>('/me'),
  registerPushToken: (push_token: string) =>
    apiRequest<void>('/me/push-token', { method: 'POST', body: { push_token } }),
};

export const PropertiesApi = {
  mine: () => apiRequest('/properties/mine'),
  availability: () => apiRequest<LockerAvailability>('/lockers/availability'),
  residents: (q: string) => apiRequest<Resident[]>(`/properties/mine/residents?q=${encodeURIComponent(q)}`),
};

export const ReservationsApi = {
  create: (size: LockerSize, recipientUnitNumber?: string) =>
    apiRequest<Reservation>('/reservations', {
      method: 'POST',
      body: { size, recipient_unit_number: recipientUnitNumber || undefined },
    }),
  mine: () => apiRequest<Reservation[]>('/reservations/mine'),
  cancel: (id: number) => apiRequest<void>(`/reservations/${id}/cancel`, { method: 'POST' }),
  paymentOptions: (id: number) => apiRequest<PaymentQuote>(`/reservations/${id}/payment-options`),
};

export const PaymentsApi = {
  initiate: (reservation_id: number, method: string) =>
    apiRequest<PaymentInitiateResult>('/payments', { method: 'POST', body: { reservation_id, method } }),
  uploadProof: (paymentId: number, fileUri: string) => {
    const form = new FormData();
    // React Native's FormData accepts this {uri,name,type} shape directly.
    form.append('proof', { uri: fileUri, name: 'proof.jpg', type: 'image/jpeg' } as unknown as Blob);

    return apiRequest<void>(`/payments/${paymentId}/proof`, { method: 'POST', body: form, isFormData: true });
  },
};

export const ParcelsApi = {
  mine: () => apiRequest<Parcel[]>('/parcels/mine'),
  collect: (code: string) => apiRequest<Parcel>('/parcels/collect', { method: 'POST', body: { code }, auth: false }),
  createDelegateCode: (parcelId: number) => apiRequest<Parcel>(`/parcels/${parcelId}/delegate`, { method: 'POST' }),
  /** Peer-to-peer send: the sender is dropping it off in person, so they deposit their own reservation right in the app. */
  deposit: (depositCode: string, senderName: string) =>
    apiRequest<Parcel>('/parcels/deposit', { method: 'POST', body: { deposit_code: depositCode, sender_name: senderName }, auth: false }),
};

export type MaintenanceTicket = {
  id: number;
  property_id: number;
  locker_id: number | null;
  issue: string;
  status: 'open' | 'in_progress' | 'resolved';
  created_at: string;
};

export const MaintenanceApi = {
  report: (issue: string, parcelId?: number) =>
    apiRequest<MaintenanceTicket>('/maintenance-tickets', { method: 'POST', body: { issue, parcel_id: parcelId ?? null } }),
  mine: () => apiRequest<MaintenanceTicket[]>('/maintenance-tickets/mine'),
};
