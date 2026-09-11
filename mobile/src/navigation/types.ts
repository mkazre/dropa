export type AuthStackParamList = {
  Welcome: undefined;
  Login: undefined;
};

export type AppStackParamList = {
  Tabs: undefined;
  ReserveSize: undefined;
  ReserveShare: { reservationId: number };
  Payment: { reservationId: number };
  ParcelDetail: { parcelId: number };
  Collect: undefined;
  ChangePassword: undefined;
};

export type TabParamList = {
  Home: undefined;
  Parcels: undefined;
  Profile: undefined;
};
