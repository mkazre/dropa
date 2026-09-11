import React, { useCallback, useEffect, useState } from 'react';
import { View, Text, StyleSheet, Pressable, Share, TextInput } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useFocusEffect } from '@react-navigation/native';
import QRCode from 'react-native-qrcode-svg';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import { Button } from '../components/ui';
import { ParcelsApi, MaintenanceApi } from '../api';
import { ApiError } from '../api/client';
import { useAccessibility } from '../context/AccessibilityContext';
import type { Parcel } from '../api/types';
import { colors, radius } from '../theme/tokens';
import type { AppStackParamList } from '../navigation/types';

type Props = NativeStackScreenProps<AppStackParamList, 'ParcelDetail'>;

export default function ParcelDetailScreen({ route, navigation }: Props) {
  const { scale } = useAccessibility();
  const { parcelId } = route.params;
  const [parcel, setParcel] = useState<Parcel | null>(null);
  const [delegating, setDelegating] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [reporting, setReporting] = useState(false);
  const [reportText, setReportText] = useState('');
  const [reportSent, setReportSent] = useState(false);
  const [reportLoading, setReportLoading] = useState(false);

  const load = useCallback(() => {
    ParcelsApi.mine().then((all) => setParcel(all.find((p) => p.id === parcelId) ?? null));
  }, [parcelId]);

  useEffect(load, [load]);
  useFocusEffect(load);

  if (!parcel) return null;

  const collected = parcel.status === 'collected';

  const letSomeoneElseCollect = async () => {
    setError(null);
    setDelegating(true);
    try {
      const updated = await ParcelsApi.createDelegateCode(parcel.id);
      setParcel(updated);
      await Share.share({
        message: `Please can you collect my Dropa parcel? Use this code at the locker: ${updated.delegate_code}`,
      });
    } catch (e) {
      setError(e instanceof ApiError ? e.message : 'Could not create a delegate code.');
    } finally {
      setDelegating(false);
    }
  };

  const submitReport = async () => {
    if (!reportText.trim()) return;
    setReportLoading(true);
    setError(null);
    try {
      await MaintenanceApi.report(reportText.trim(), parcel.id);
      setReportSent(true);
      setReporting(false);
    } catch (e) {
      setError(e instanceof ApiError ? e.message : 'Could not send your report.');
    } finally {
      setReportLoading(false);
    }
  };

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
            <Text style={[styles.sub, { fontSize: scale(13.5) }]}>Scan this at the locker screen, or tap Collect when you're standing at it.</Text>
            {parcel.reserved_by ? <Text style={[styles.reservedBy, { fontSize: scale(12) }]}>Reserved by {parcel.reserved_by}</Text> : null}

            <View style={styles.pinRow}>
              {parcel.pickup_pin.split('').map((digit, i) => (
                <View key={i} style={[styles.pinBox, { width: scale(36), height: scale(50) }]}>
                  <Text style={[styles.pinDigit, { fontSize: scale(22) }]}>{digit}</Text>
                </View>
              ))}
            </View>

            <View style={styles.qrBox}>
              <QRCode value={parcel.qr_token} size={160} color={colors.ink} backgroundColor="#fff" />
            </View>

            {parcel.delegate_code ? (
              <View style={styles.delegateCard}>
                <Text style={styles.delegateLabel}>Delegate code shared</Text>
                <Text style={styles.delegateCode}>{parcel.delegate_code}</Text>
                <Text style={styles.delegateHint}>Whoever you shared this with can use it instead of your PIN.</Text>
              </View>
            ) : (
              <Button
                title="Let someone else collect it"
                variant="ghost"
                loading={delegating}
                onPress={letSomeoneElseCollect}
                style={{ marginTop: 26, width: '100%' }}
              />
            )}
            {error ? <Text style={styles.error}>{error}</Text> : null}

            <View style={{ width: '100%', marginTop: 14 }}>
              {reportSent ? (
                <Text style={styles.reportSent}>Thanks — we've let your Body Corporate know. ✓</Text>
              ) : reporting ? (
                <View style={styles.reportBox}>
                  <TextInput
                    value={reportText}
                    onChangeText={setReportText}
                    placeholder="What's wrong with this locker?"
                    placeholderTextColor={colors.muted}
                    multiline
                    style={styles.reportInput}
                  />
                  <Button title="Send report" onPress={submitReport} loading={reportLoading} style={{ marginTop: 10 }} />
                </View>
              ) : (
                <Pressable onPress={() => setReporting(true)}>
                  <Text style={styles.reportLink}>Report a problem with this locker</Text>
                </Pressable>
              )}
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
  sub: { color: colors.muted, textAlign: 'center', lineHeight: 20, marginTop: 4 },
  reservedBy: { color: colors.signalDeep, fontWeight: '700', marginTop: 8 },
  pinRow: { flexDirection: 'row', gap: 8, marginTop: 22 },
  pinBox: { width: 36, height: 50, borderRadius: 11, backgroundColor: colors.ink, alignItems: 'center', justifyContent: 'center' },
  pinDigit: { color: '#fff', fontSize: 22, fontWeight: '800' },
  qrBox: { width: 190, height: 190, marginTop: 22, backgroundColor: '#fff', borderWidth: 1.5, borderColor: colors.line, borderRadius: radius.md, alignItems: 'center', justifyContent: 'center' },
  collectedText: { color: colors.ok, fontWeight: '700', fontSize: 15, marginTop: 40 },
  delegateCard: { backgroundColor: '#FFFBEC', borderWidth: 1.5, borderColor: colors.signal, borderRadius: radius.md, padding: 16, marginTop: 26, width: '100%', alignItems: 'center' },
  delegateLabel: { fontSize: 12, fontWeight: '700', color: colors.inkSoft },
  delegateCode: { fontSize: 24, fontWeight: '800', letterSpacing: 3, color: colors.ink, marginTop: 6 },
  delegateHint: { fontSize: 12, color: colors.inkSoft, marginTop: 8, textAlign: 'center' },
  error: { color: colors.alert, fontSize: 13, fontWeight: '600', marginTop: 14, textAlign: 'center' },
  reportLink: { color: colors.muted, fontSize: 12.5, fontWeight: '700', textAlign: 'center', textDecorationLine: 'underline' },
  reportBox: { backgroundColor: colors.cream, borderWidth: 1.5, borderColor: colors.line, borderRadius: radius.md, padding: 14 },
  reportInput: { minHeight: 70, fontSize: 14, color: colors.ink, textAlignVertical: 'top' },
  reportSent: { color: colors.ok, fontWeight: '700', fontSize: 13, textAlign: 'center' },
});
