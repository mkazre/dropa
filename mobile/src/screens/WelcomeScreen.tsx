import React from 'react';
import { View, Text, StyleSheet, Pressable } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import Logo from '../components/Logo';
import { colors } from '../theme/tokens';

export default function WelcomeScreen() {
  return (
    <SafeAreaView style={styles.screen}>
      <View style={styles.center}>
        <Logo size={40} dark />
        <Text style={styles.h1}>Your building's digital letterbox.</Text>
        <Text style={styles.sub}>
          Reserve a locker when you're expecting a delivery, share a code with the courier, and collect on your own time.
        </Text>
      </View>
      <Pressable style={styles.btn}>
        <Text style={styles.btnText}>Sign in</Text>
      </Pressable>
      <Pressable style={styles.btnGhost}>
        <Text style={styles.btnGhostText}>I have an invite code</Text>
      </Pressable>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.paper, paddingHorizontal: 24, paddingBottom: 24 },
  center: { flex: 1, justifyContent: 'center' },
  h1: { fontSize: 27, fontWeight: '800', letterSpacing: -0.9, marginTop: 26, color: colors.ink, lineHeight: 33 },
  sub: { color: colors.muted, fontSize: 14, marginTop: 10, lineHeight: 21 },
  btn: { backgroundColor: colors.signal, borderRadius: 15, paddingVertical: 16, alignItems: 'center' },
  btnText: { color: '#241f00', fontWeight: '700', fontSize: 16 },
  btnGhost: { borderWidth: 1.5, borderColor: colors.lineStrong, borderRadius: 15, paddingVertical: 16, alignItems: 'center', marginTop: 10 },
  btnGhostText: { color: colors.ink, fontWeight: '700', fontSize: 16 },
});
