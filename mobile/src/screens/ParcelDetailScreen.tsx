import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, Pressable } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import QRCode from 'react-native-qrcode-svg';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import { ParcelsApi } from '../api';
import type { Parcel } from '../api/types';
import { colors, radius } from '../theme/tokens';
import type { AppStackParamList } from '../navigation/types';

type Props = NativeStackScreenProps<AppStackParamList, 'ParcelDetail'>;

export default function ParcelDetailScreen({ route, navigation }: Props) {
  const { parcelId } = route.params;
  const [parcel, setParcel] = useState<Parcel | null>(null);

  useEffect(() => {
    ParcelsApi.mine().then((all) => setParcel(all.find((p) => p.id === parcelId) ?? null));
  }, [parcelId]);

  if (!parcel) return null;

  const collected = parcel.status === 'collected';

  return (
    <SafeAreaView style={styles.screen}>
      <View style={styles.topbar}>
        <Pressable onPress={() => navigation.goBack()}><Text style={styles.back}>‹</Text></Pressable>
        <Text style={styles.h2}>Parcel from {parcel.sender_name ?? 'a courier'}</Text>
      </View>

      <View style={{ padding: 22, alignItems: 'center' }}>
        {collected ? (
          <Text style={styles.collectedText}>Collected on {new Date(parcel.collected_at!).toLocaleString()}</Text>
        ) : (
          <>
            <Text style={styles.sub}>Scan this at the locker screen, or tap Collect when you're standing at it.</Text>

            <View style={styles.pinRow}>
              {parcel.pickup_pin.split('').map((digit, i) => (
                <View key={i} style={styles.pinBox}><Text style={styles.pinDigit}>{digit}</Text></View>
              ))}
            </View>

            <View style={styles.qrBox}>
              <QRCode value={parcel.qr_token} size={160} color={colors.ink} backgroundColor="#fff" />
            </View>
          </>
        )}
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.paper, paddingTop: 50 },
  topbar: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingHorizontal: 22 },
  back: { fontSize: 24, color: colors.ink, width: 24 },
  h2: { fontSize: 17, fontWeight: '800', color: colors.ink, flexShrink: 1 },
  sub: { color: colors.muted, fontSize: 13.5, textAlign: 'center', lineHeight: 20, marginTop: 4 },
  pinRow: { flexDirection: 'row', gap: 8, marginTop: 22 },
  pinBox: { width: 36, height: 50, borderRadius: 11, backgroundColor: colors.ink, alignItems: 'center', justifyContent: 'center' },
  pinDigit: { color: '#fff', fontSize: 22, fontWeight: '800' },
  qrBox: { width: 190, height: 190, marginTop: 22, backgroundColor: '#fff', borderWidth: 1.5, borderColor: colors.line, borderRadius: radius.md, alignItems: 'center', justifyContent: 'center' },
  collectedText: { color: colors.ok, fontWeight: '700', fontSize: 15, marginTop: 40 },
});
