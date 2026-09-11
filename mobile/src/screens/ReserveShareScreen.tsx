import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, Share, Pressable } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import * as Clipboard from 'expo-clipboard';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import { Button } from '../components/ui';
import { ReservationsApi, ParcelsApi } from '../api';
import { ApiError } from '../api/client';
import { useAccessibility } from '../context/AccessibilityContext';
import { useAuth } from '../context/AuthContext';
import type { PaymentQuote, Reservation } from '../api/types';
import { colors, radius } from '../theme/tokens';
import type { AppStackParamList } from '../navigation/types';

type Props = NativeStackScreenProps<AppStackParamList, 'ReserveShare'>;

export default function ReserveShareScreen({ route, navigation }: Props) {
  const { scale } = useAccessibility();
  const { me } = useAuth();
  const { reservationId } = route.params;
  const [reservation, setReservation] = useState<Reservation | null>(null);
  const [quote, setQuote] = useState<PaymentQuote | null>(null);
  const [copied, setCopied] = useState(false);
  const [depositing, setDepositing] = useState(false);
  const [depositDone, setDepositDone] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    ReservationsApi.mine().then((all) => setReservation(all.find((r) => r.id === reservationId) ?? null));
    ReservationsApi.paymentOptions(reservationId).then(setQuote).catch(() => {});
  }, [reservationId]);

  const code = reservation?.deposit_code ?? '······';
  // A peer-to-peer send: this reservation is for someone else, and I'm the one who set it up.
  const isSendingToNeighbour = !!reservation && !!me && reservation.tenant_id !== me.id;

  const share = async () => {
    await Share.share({
      message: `Please drop my parcel off at the Dropa locker using this code: ${code}`,
    });
  };

  const copy = async () => {
    await Clipboard.setStringAsync(code);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const depositNow = async () => {
    setError(null);
    setDepositing(true);
    try {
      await ParcelsApi.deposit(code, me?.name ?? 'A neighbour');
      setDepositDone(true);
    } catch (e) {
      setError(e instanceof ApiError ? e.message : 'Could not complete the drop-off.');
    } finally {
      setDepositing(false);
    }
  };

  const needsPayment = quote !== null && quote.amount > 0;

  if (depositDone) {
    return (
      <SafeAreaView style={styles.screen}>
        <View style={styles.successWrap}>
          <View style={styles.badge}><Text style={{ fontSize: 30 }}>✓</Text></View>
          <Text style={styles.h1}>Delivered</Text>
          <Text style={styles.sub}>They've been notified and can collect whenever suits them.</Text>
          <Button title="Back to home" onPress={() => navigation.popToTop()} style={{ marginTop: 30, width: '100%' }} />
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.screen}>
      <View style={styles.topbar}>
        <Text style={styles.h2}>Locker reserved</Text>
      </View>

      <View style={{ paddingHorizontal: 22, flex: 1 }}>
        {isSendingToNeighbour ? (
          <>
            <Text style={styles.sub}>Head to the locker now and place the parcel inside — this code identifies the locker that's holding it open for you.</Text>
            <View style={styles.codeBox}>
              <Text style={[styles.code, { fontSize: scale(30) }]}>{code.split('').join(' ')}</Text>
            </View>
            <Button title="I've dropped it off" onPress={depositNow} loading={depositing} style={{ marginTop: 18 }} />
            {error ? <Text style={styles.error}>{error}</Text> : null}
          </>
        ) : (
          <>
            <Text style={styles.sub}>Share this code with your courier — they'll enter it at the locker to drop off your parcel.</Text>

            <View style={styles.codeBox}>
              <Text style={[styles.code, { fontSize: scale(30) }]}>{code.split('').join(' ')}</Text>
            </View>

            <View style={styles.row}>
              <Pressable style={styles.smallBtn} onPress={copy}>
                <Text style={styles.smallBtnText}>{copied ? 'Copied ✓' : 'Copy code'}</Text>
              </Pressable>
              <Pressable style={[styles.smallBtn, styles.smallBtnDark]} onPress={share}>
                <Text style={[styles.smallBtnText, styles.smallBtnTextLight]}>Share ›</Text>
              </Pressable>
            </View>
          </>
        )}

        {reservation ? (
          <Text style={styles.expiry}>
            Held until {new Date(reservation.expires_at).toLocaleString()} — the locker releases automatically if nothing is dropped off.
          </Text>
        ) : null}

        {needsPayment ? (
          <View style={styles.paymentCard}>
            <Text style={styles.paymentTitle}>R{quote!.amount.toFixed(2)} due for this reservation</Text>
            <Text style={styles.paymentSub}>Pay now, or any time before you collect.</Text>
            <Button title="Pay now" onPress={() => navigation.navigate('Payment', { reservationId })} style={{ marginTop: 12 }} />
          </View>
        ) : null}
      </View>

      <View style={{ padding: 22 }}>
        <Button title="Done" variant="dark" onPress={() => navigation.popToTop()} />
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.paper, paddingTop: 50 },
  topbar: { paddingHorizontal: 22, marginBottom: 4 },
  h2: { fontSize: 19, fontWeight: '800', color: colors.ink },
  h1: { fontSize: 24, fontWeight: '800', color: colors.ink },
  sub: { color: colors.muted, fontSize: 13.5, marginTop: 10, lineHeight: 20 },
  codeBox: {
    backgroundColor: colors.ink, borderRadius: radius.md, paddingVertical: 26, alignItems: 'center', marginTop: 20,
  },
  code: { color: colors.signal, fontSize: 30, fontWeight: '800', letterSpacing: 4 },
  row: { flexDirection: 'row', gap: 10, marginTop: 14 },
  smallBtn: { flex: 1, borderWidth: 1.5, borderColor: colors.lineStrong, borderRadius: 13, paddingVertical: 12, alignItems: 'center' },
  smallBtnDark: { backgroundColor: colors.ink, borderColor: colors.ink },
  smallBtnText: { fontWeight: '700', fontSize: 13.5, color: colors.ink },
  smallBtnTextLight: { color: '#fff' },
  expiry: { color: colors.muted, fontSize: 12, marginTop: 16, lineHeight: 18 },
  paymentCard: { backgroundColor: '#FFFBEC', borderWidth: 1.5, borderColor: colors.signal, borderRadius: radius.md, padding: 16, marginTop: 24 },
  paymentTitle: { fontWeight: '800', fontSize: 15, color: colors.ink },
  paymentSub: { color: colors.inkSoft, fontSize: 12.5, marginTop: 4 },
  error: { color: colors.alert, fontSize: 13, fontWeight: '600', marginTop: 14 },
  successWrap: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: 30 },
  badge: { width: 80, height: 80, borderRadius: 22, backgroundColor: colors.okSoft, alignItems: 'center', justifyContent: 'center', marginBottom: 20 },
});
