import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { useAuth } from '../context/AuthContext';
import { usePushRegistration } from '../hooks/usePushRegistration';
import AuthNavigator from './AuthNavigator';
import AppNavigator from './AppNavigator';

export default function RootNavigator() {
  const { me } = useAuth();
  usePushRegistration(!!me);

  return (
    <NavigationContainer>
      {me ? <AppNavigator /> : <AuthNavigator />}
    </NavigationContainer>
  );
}
