import React, { useCallback, useEffect, useState } from 'react';
import { View } from 'react-native';
import { StatusBar } from 'expo-status-bar';
import * as SplashScreen from 'expo-splash-screen';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import OnboardingScreen from './src/screens/OnboardingScreen';
import RootNavigator from './src/navigation/RootNavigator';
import { AuthProvider, useAuth } from './src/context/AuthContext';
import { colors } from './src/theme/tokens';

const ONBOARDING_SEEN_KEY = 'dropa.onboardingSeen';

SplashScreen.preventAutoHideAsync().catch(() => {});

function Gate({ showOnboarding, onDoneOnboarding }: { showOnboarding: boolean; onDoneOnboarding: () => void }) {
  const { booting } = useAuth();

  const onLayout = useCallback(() => {
    if (!booting) SplashScreen.hideAsync().catch(() => {});
  }, [booting]);

  if (booting) return <View style={{ flex: 1, backgroundColor: colors.ink }} onLayout={onLayout} />;

  return (
    <View style={{ flex: 1, backgroundColor: colors.paper }} onLayout={onLayout}>
      {showOnboarding ? <OnboardingScreen onDone={onDoneOnboarding} /> : <RootNavigator />}
      <StatusBar style="dark" />
    </View>
  );
}

export default function App() {
  const [ready, setReady] = useState(false);
  const [showOnboarding, setShowOnboarding] = useState(false);

  useEffect(() => {
    AsyncStorage.getItem(ONBOARDING_SEEN_KEY)
      .then((seen) => setShowOnboarding(seen !== 'true'))
      .catch(() => setShowOnboarding(true))
      .finally(() => setReady(true));
  }, []);

  const finishOnboarding = () => {
    AsyncStorage.setItem(ONBOARDING_SEEN_KEY, 'true').catch(() => {});
    setShowOnboarding(false);
  };

  if (!ready) return null;

  return (
    <SafeAreaProvider>
      <AuthProvider>
        <Gate showOnboarding={showOnboarding} onDoneOnboarding={finishOnboarding} />
      </AuthProvider>
    </SafeAreaProvider>
  );
}
