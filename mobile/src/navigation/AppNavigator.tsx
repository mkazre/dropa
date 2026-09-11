import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import TabsNavigator from './TabsNavigator';
import ReserveSizeScreen from '../screens/ReserveSizeScreen';
import ReserveShareScreen from '../screens/ReserveShareScreen';
import PaymentScreen from '../screens/PaymentScreen';
import ParcelDetailScreen from '../screens/ParcelDetailScreen';
import CollectScreen from '../screens/CollectScreen';
import ChangePasswordScreen from '../screens/ChangePasswordScreen';
import PublicLockersScreen from '../screens/PublicLockersScreen';
import TrackDeliveriesScreen from '../screens/TrackDeliveriesScreen';
import type { AppStackParamList } from './types';

const Stack = createNativeStackNavigator<AppStackParamList>();

export default function AppNavigator() {
  return (
    <Stack.Navigator screenOptions={{ headerShown: false }}>
      <Stack.Screen name="Tabs" component={TabsNavigator} />
      <Stack.Screen name="ReserveSize" component={ReserveSizeScreen} />
      <Stack.Screen name="ReserveShare" component={ReserveShareScreen} />
      <Stack.Screen name="Payment" component={PaymentScreen} />
      <Stack.Screen name="ParcelDetail" component={ParcelDetailScreen} />
      <Stack.Screen name="Collect" component={CollectScreen} />
      <Stack.Screen name="ChangePassword" component={ChangePasswordScreen} />
      <Stack.Screen name="PublicLockers" component={PublicLockersScreen} />
      <Stack.Screen name="TrackDeliveries" component={TrackDeliveriesScreen} />
    </Stack.Navigator>
  );
}
