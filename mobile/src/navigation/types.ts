export type AuthStackParamList = {
  Welcome: undefined;
  Login: undefined;
};

export type AppStackParamList = {
  Tabs: undefined;
  ReserveSize: { publicSite?: { id: number; name: string } } | undefined;
  ReserveShare: { reservationId: number };
  Payment: { reservationId: number };
  ParcelDetail: { parcelId: number };
  Collect: undefined;
  ChangePassword: undefined;
  PublicLockers: undefined;
  TrackDeliveries: undefined;
};

export type TabParamList = {
  Home: undefined;
  Parcels: undefined;
  Profile: undefined;
};
