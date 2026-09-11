import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import Logo from '../components/Logo';
import { Button } from '../components/ui';
import { colors } from '../theme/tokens';
import type { AuthStackParamList } from '../navigation/types';

type Props = NativeStackScreenProps<AuthStackParamList, 'Welcome'>;

export default function WelcomeScreen({ navigation }: Props) {
  return (
    <SafeAreaView style={styles.screen}>
      <View style={styles.center}>
        <Logo size={40} dark />
        <Text style={styles.h1}>Your building's digital letterbox.</Text>
        <Text style={styles.sub}>
          Reserve a locker when you're expecting a delivery, share a code with the courier, and collect on your own time.
        </Text>
      </View>
      <Button title="Sign in" onPress={() => navigation.navigate('Login')} />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.paper, paddingHorizontal: 24, paddingBottom: 24 },
  center: { flex: 1, justifyContent: 'center' },
  h1: { fontSize: 27, fontWeight: '800', letterSpacing: -0.9, marginTop: 26, color: colors.ink, lineHeight: 33 },
  sub: { color: colors.muted, fontSize: 14, marginTop: 10, lineHeight: 21 },
});
