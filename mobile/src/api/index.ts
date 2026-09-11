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
  recommendSize: (dims: { weight_kg?: number; length_cm?: number; width_cm?: number; height_cm?: number }) =>
    apiRequest<{ size: LockerSize }>('/lockers/recommend-size', { method: 'POST', body: dims }),
};

export type PublicSite = {
  id: number;
  name: string;
  address: string | null;
};

export const PublicSitesApi = {
  list: () => apiRequest<PublicSite[]>('/public-sites'),
  availability: (propertyId: number) => apiRequest<LockerAvailability>(`/public-sites/${propertyId}/availability`),
};

export const ReservationsApi = {
  create: (size: LockerSize, opts?: { recipientUnitNumber?: string; publicPropertyId?: number }) =>
    apiRequest<Reservation>('/reservations', {
      method: 'POST',
      body: {
        size,
        recipient_unit_number: opts?.recipientUnitNumber || undefined,
        property_id: opts?.publicPropertyId,
      },
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

export type Prealert = {
  id: number;
  courier: string;
  tracking_number: string;
  size: LockerSize;
  status: 'watching' | 'out_for_delivery' | 'reserved' | 'cancelled';
  reservation_id: number | null;
  created_at: string;
};

export const PreAlertsApi = {
  create: (courier: string, trackingNumber: string, size: LockerSize) =>
    apiRequest<Prealert>('/pre-alerts', { method: 'POST', body: { courier, tracking_number: trackingNumber, size } }),
  mine: () => apiRequest<Prealert[]>('/pre-alerts/mine'),
  cancel: (id: number) => apiRequest<void>(`/pre-alerts/${id}/cancel`, { method: 'POST' }),
};
