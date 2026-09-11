import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, Linking, Pressable, Image } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import * as ImagePicker from 'expo-image-picker';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import { Button } from '../components/ui';
import { PaymentsApi, ReservationsApi } from '../api';
import { ApiError } from '../api/client';
import type { PaymentQuote } from '../api/types';
import { colors, radius } from '../theme/tokens';
import type { AppStackParamList } from '../navigation/types';

type Props = NativeStackScreenProps<AppStackParamList, 'Payment'>;

const methodLabels: Record<string, string> = {
  ozow: 'Ozow (instant EFT)',
  payfast: 'PayFast (card)',
  manual: 'Manual / offline payment',
};

export default function PaymentScreen({ route, navigation }: Props) {
  const { reservationId } = route.params;
  const [quote, setQuote] = useState<PaymentQuote | null>(null);
  const [method, setMethod] = useState<string | null>(null);
  const [paymentId, setPaymentId] = useState<number | null>(null);
  const [instructions, setInstructions] = useState<string | null>(null);
  const [proofUri, setProofUri] = useState<string | null>(null);
  const [uploaded, setUploaded] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    ReservationsApi.paymentOptions(reservationId).then(setQuote).catch(() => {});
  }, [reservationId]);

  const choose = async (m: string) => {
    setMethod(m);
    setError(null);
    setLoading(true);
    try {
      const result = await PaymentsApi.initiate(reservationId, m);
      setPaymentId(result.payment_id);
      if (result.redirect_url) {
        await Linking.openURL(result.redirect_url);
      } else if (result.instructions) {
        setInstructions(result.instructions);
      }
    } catch (e) {
      setError(e instanceof ApiError ? e.message : 'Could not start this payment.');
    } finally {
      setLoading(false);
    }
  };

  const pickProof = async () => {
    const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (!permission.granted) return;

    const result = await ImagePicker.launchImageLibraryAsync({ mediaTypes: ['images'], quality: 0.7 });
    if (!result.canceled && result.assets[0]) {
      setProofUri(result.assets[0].uri);
    }
  };

  const uploadProof = async () => {
    if (!paymentId || !proofUri) return;
    setLoading(true);
    setError(null);
    try {
      await PaymentsApi.uploadProof(paymentId, proofUri);
      setUploaded(true);
    } catch (e) {
      setError(e instanceof ApiError ? e.message : 'Could not upload your proof of payment.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.screen}>
      <View style={styles.topbar}>
        <Pressable onPress={() => navigation.goBack()}><Text style={styles.back}>‹</Text></Pressable>
        <Text style={styles.h2}>Pay for your reservation</Text>
      </View>
      <ScrollView contentContainerStyle={{ padding: 22 }}>
        {quote && <Text style={styles.amount}>R{quote.amount.toFixed(2)}</Text>}

        {!method && quote?.methods.map((m) => (
          <Pressable key={m.method} style={styles.methodRow} onPress={() => choose(m.method)}>
            <Text style={styles.methodLabel}>{methodLabels[m.method] ?? m.method}</Text>
            <Text style={styles.methodArrow}>›</Text>
          </Pressable>
        ))}

        {!method && quote?.methods.length === 0 && (
          <Text style={styles.sub}>No payment methods are currently available for your property — contact your Body Corporate.</Text>
        )}

        {method === 'manual' && instructions && !uploaded && (
          <View style={styles.manualCard}>
            <Text style={styles.manualTitle}>Instructions</Text>
            <Text style={styles.manualBody}>{instructions}</Text>

            {proofUri ? (
              <Image source={{ uri: proofUri }} style={styles.preview} />
            ) : null}

            <Button title={proofUri ? 'Choose a different file' : 'Attach proof of payment'} variant="ghost" onPress={pickProof} style={{ marginTop: 14 }} />
            {proofUri && (
              <Button title="Submit proof" onPress={uploadProof} loading={loading} style={{ marginTop: 10 }} />
            )}
          </View>
        )}

        {uploaded && (
          <View style={styles.manualCard}>
            <Text style={styles.manualTitle}>Submitted ✓</Text>
            <Text style={styles.manualBody}>Your proof of payment is with your Body Corporate for approval.</Text>
            <Button title="Done" onPress={() => navigation.popToTop()} style={{ marginTop: 14 }} />
          </View>
        )}

        {method && method !== 'manual' && !instructions && (
          <View style={styles.manualCard}>
            <Text style={styles.manualTitle}>Complete payment in your browser</Text>
            <Text style={styles.manualBody}>We opened your bank/card page. Come back here once you're done — we'll update your reservation automatically.</Text>
            <Button title="Done" onPress={() => navigation.popToTop()} style={{ marginTop: 14 }} />
          </View>
        )}

        {error ? <Text style={styles.error}>{error}</Text> : null}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.paper, paddingTop: 50 },
  topbar: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingHorizontal: 22, marginBottom: 8 },
  back: { fontSize: 24, color: colors.ink, width: 24 },
  h2: { fontSize: 19, fontWeight: '800', color: colors.ink },
  amount: { fontSize: 32, fontWeight: '800', color: colors.ink, marginBottom: 18 },
  sub: { color: colors.muted, fontSize: 13.5, lineHeight: 20 },
  methodRow: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    borderWidth: 1.5, borderColor: colors.line, borderRadius: radius.md, padding: 16, marginBottom: 10, backgroundColor: '#fff',
  },
  methodLabel: { fontWeight: '700', fontSize: 15, color: colors.ink },
  methodArrow: { fontSize: 20, color: colors.muted },
  manualCard: { backgroundColor: colors.cream, borderWidth: 1.5, borderColor: colors.line, borderRadius: radius.md, padding: 16, marginTop: 8 },
  manualTitle: { fontWeight: '800', fontSize: 15, color: colors.ink },
  manualBody: { color: colors.inkSoft, fontSize: 13, marginTop: 6, lineHeight: 19 },
  preview: { width: '100%', height: 160, borderRadius: 12, marginTop: 12 },
  error: { color: colors.alert, fontSize: 13, fontWeight: '600', marginTop: 14 },
});
