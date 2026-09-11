import { apiRequest } from './client';
import type {
  LockerAvailability,
  LockerSize,
  Me,
  Parcel,
  PaymentInitiateResult,
  PaymentQuote,
  Reservation,
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
};

export const ReservationsApi = {
  create: (size: LockerSize) => apiRequest<Reservation>('/reservations', { method: 'POST', body: { size } }),
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
};
