import React, { useState } from 'react';
import { View, Text, StyleSheet, Pressable } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { CameraView, useCameraPermissions } from 'expo-camera';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import { Button } from '../components/ui';
import { ParcelsApi } from '../api';
import { ApiError } from '../api/client';
import { colors, radius } from '../theme/tokens';
import type { AppStackParamList } from '../navigation/types';

type Props = NativeStackScreenProps<AppStackParamList, 'Collect'>;

const KEYS = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '', '0', '⌫'];

export default function CollectScreen({ navigation }: Props) {
  const [mode, setMode] = useState<'pin' | 'scan'>('pin');
  const [pin, setPin] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);
  const [permission, requestPermission] = useCameraPermissions();
  const [scanned, setScanned] = useState(false);

  const attemptCollect = async (code: string) => {
    setError(null);
    setLoading(true);
    try {
      await ParcelsApi.collect(code);
      setSuccess(true);
    } catch (e) {
      setError(e instanceof ApiError ? e.message : 'Could not open the locker.');
      setPin('');
      setScanned(false);
    } finally {
      setLoading(false);
    }
  };

  const keyTap = (k: string) => {
    if (k === '') return;
    if (k === '⌫') {
      setPin((p) => p.slice(0, -1));
      return;
    }
    const next = pin.length < 6 ? pin + k : pin;
    setPin(next);
    if (next.length === 6) attemptCollect(next);
  };

  const onBarcodeScanned = ({ data }: { data: string }) => {
    if (scanned) return;
    setScanned(true);
    attemptCollect(data);
  };

  if (success) {
    return (
      <SafeAreaView style={styles.screen}>
        <View style={styles.successWrap}>
          <View style={styles.badge}><Text style={{ fontSize: 30 }}>✓</Text></View>
          <Text style={styles.h1}>Collected</Text>
          <Text style={styles.sub}>The locker is now free for the next delivery.</Text>
          <Button title="Back to home" onPress={() => navigation.popToTop()} style={{ marginTop: 30, width: '100%' }} />
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.screen}>
      <View style={styles.topbar}>
        <Pressable onPress={() => navigation.goBack()}><Text style={styles.back}>‹</Text></Pressable>
        <Text style={styles.h2}>Collect a parcel</Text>
      </View>

      <View style={styles.switch}>
        <Pressable style={[styles.switchBtn, mode === 'pin' && styles.switchBtnOn]} onPress={() => setMode('pin')}>
          <Text style={[styles.switchText, mode === 'pin' && styles.switchTextOn]}>Enter PIN</Text>
        </Pressable>
        <Pressable style={[styles.switchBtn, mode === 'scan' && styles.switchBtnOn]} onPress={() => setMode('scan')}>
          <Text style={[styles.switchText, mode === 'scan' && styles.switchTextOn]}>Scan QR</Text>
        </Pressable>
      </View>

      {mode === 'pin' ? (
        <View style={{ padding: 22, flex: 1 }}>
          <View style={styles.pinRow}>
            {Array.from({ length: 6 }).map((_, i) => (
              <View key={i} style={styles.pinBox}><Text style={styles.pinDigit}>{pin[i] ?? ''}</Text></View>
            ))}
          </View>
          {error ? <Text style={styles.error}>{error}</Text> : null}
          <View style={styles.keypad}>
            {KEYS.map((k, i) => (
              <Pressable key={i} disabled={k === '' || loading} style={styles.key} onPress={() => keyTap(k)}>
                <Text style={styles.keyText}>{k}</Text>
              </Pressable>
            ))}
          </View>
        </View>
      ) : (
        <View style={{ flex: 1, padding: 22 }}>
          {!permission?.granted ? (
            <View style={styles.center}>
              <Text style={styles.sub}>Dropa needs camera access to scan your collection QR code.</Text>
              <Button title="Grant camera access" onPress={requestPermission} style={{ marginTop: 16 }} />
            </View>
          ) : (
            <View style={styles.cameraBox}>
              <CameraView
                style={{ flex: 1 }}
                barcodeScannerSettings={{ barcodeTypes: ['qr'] }}
                onBarcodeScanned={scanned ? undefined : onBarcodeScanned}
              />
            </View>
          )}
          {error ? <Text style={styles.error}>{error}</Text> : null}
        </View>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.paper, paddingTop: 50 },
  topbar: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingHorizontal: 22 },
  back: { fontSize: 24, color: colors.ink, width: 24 },
  h2: { fontSize: 19, fontWeight: '800', color: colors.ink },
  switch: { flexDirection: 'row', backgroundColor: colors.line, borderRadius: 999, margin: 22, padding: 4 },
  switchBtn: { flex: 1, paddingVertical: 10, borderRadius: 999, alignItems: 'center' },
  switchBtnOn: { backgroundColor: colors.ink },
  switchText: { fontWeight: '700', fontSize: 13, color: colors.inkSoft },
  switchTextOn: { color: '#fff' },
  pinRow: { flexDirection: 'row', gap: 8, justifyContent: 'center', marginBottom: 20 },
  pinBox: { width: 40, height: 54, borderRadius: 12, borderWidth: 1.5, borderColor: colors.lineStrong, alignItems: 'center', justifyContent: 'center' },
  pinDigit: { fontSize: 24, fontWeight: '800', color: colors.ink },
  keypad: { flexDirection: 'row', flexWrap: 'wrap', gap: 10 },
  key: { width: '30%', paddingVertical: 18, borderRadius: 14, backgroundColor: colors.cream, alignItems: 'center' },
  keyText: { fontSize: 20, fontWeight: '700', color: colors.ink },
  cameraBox: { flex: 1, borderRadius: radius.md, overflow: 'hidden', backgroundColor: '#000' },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  sub: { color: colors.muted, fontSize: 13.5, textAlign: 'center', lineHeight: 20 },
  error: { color: colors.alert, fontSize: 13, fontWeight: '600', marginTop: 14, textAlign: 'center' },
  successWrap: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: 30 },
  badge: { width: 80, height: 80, borderRadius: 22, backgroundColor: colors.okSoft, alignItems: 'center', justifyContent: 'center', marginBottom: 20 },
  h1: { fontSize: 24, fontWeight: '800', color: colors.ink },
});
