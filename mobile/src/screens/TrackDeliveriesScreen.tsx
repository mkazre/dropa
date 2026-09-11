import React, { useCallback, useState } from 'react';
import { View, Text, StyleSheet, FlatList, Pressable, TextInput } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useFocusEffect } from '@react-navigation/native';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import { PreAlertsApi } from '../api';
import type { Prealert } from '../api';
import { Button, EmptyState } from '../components/ui';
import { ApiError } from '../api/client';
import { colors } from '../theme/tokens';
import type { AppStackParamList } from '../navigation/types';

type Props = NativeStackScreenProps<AppStackParamList, 'TrackDeliveries'>;

const statusLabel: Record<Prealert['status'], string> = {
  watching: 'Watching for updates',
  out_for_delivery: 'Out for delivery',
  reserved: 'Locker reserved',
  cancelled: 'Cancelled',
};

export default function TrackDeliveriesScreen({ navigation }: Props) {
  const [prealerts, setPrealerts] = useState<Prealert[]>([]);
  const [adding, setAdding] = useState(false);
  const [courier, setCourier] = useState('');
  const [trackingNumber, setTrackingNumber] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const load = useCallback(() => {
    PreAlertsApi.mine().then(setPrealerts).catch(() => {});
  }, []);

  useFocusEffect(load);

  const submit = async () => {
    if (!courier.trim() || !trackingNumber.trim()) {
      setError('Please fill in both fields.');
      return;
    }
    setError(null);
    setLoading(true);
    try {
      await PreAlertsApi.create(courier.trim(), trackingNumber.trim(), 'M');
      setCourier('');
      setTrackingNumber('');
      setAdding(false);
      load();
    } catch (e) {
      setError(e instanceof ApiError ? e.message : 'Could not add that tracking number.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.screen}>
      <View style={styles.topbar}>
        <Pressable onPress={() => navigation.goBack()}><Text style={styles.back}>‹</Text></Pressable>
        <Text style={styles.h2}>Track a delivery</Text>
      </View>
      <Text style={styles.sub}>Paste a tracking number and we'll reserve a locker the moment it's out for delivery.</Text>

      {adding ? (
        <View style={styles.addBox}>
          <TextInput value={courier} onChangeText={setCourier} placeholder="Courier (e.g. Courier Guy)" placeholderTextColor={colors.muted} style={styles.input} />
          <TextInput value={trackingNumber} onChangeText={setTrackingNumber} placeholder="Tracking number" placeholderTextColor={colors.muted} style={[styles.input, { marginTop: 8 }]} />
          {error ? <Text style={styles.error}>{error}</Text> : null}
          <Button title="Start watching" onPress={submit} loading={loading} style={{ marginTop: 10 }} />
        </View>
      ) : (
        <Pressable style={styles.addBtn} onPress={() => setAdding(true)}>
          <Text style={styles.addBtnText}>+ Add a tracking number</Text>
        </Pressable>
      )}

      <FlatList
        data={prealerts}
        keyExtractor={(p) => String(p.id)}
        contentContainerStyle={{ paddingHorizontal: 22, paddingTop: 8, paddingBottom: 22 }}
        ListEmptyComponent={<EmptyState title="Nothing being tracked" body="Add a tracking number above to get a locker ready automatically." />}
        renderItem={({ item }) => (
          <View style={styles.row}>
            <View style={{ flex: 1 }}>
              <Text style={styles.title}>{item.courier} · {item.tracking_number}</Text>
              <Text style={styles.address}>Size {item.size}</Text>
            </View>
            <Text style={styles.status}>{statusLabel[item.status]}</Text>
          </View>
        )}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.paper, paddingTop: 50 },
  topbar: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingHorizontal: 22 },
  back: { fontSize: 24, color: colors.ink, width: 24 },
  h2: { fontSize: 19, fontWeight: '800', color: colors.ink },
  sub: { color: colors.muted, fontSize: 13, paddingHorizontal: 22, marginTop: 8, lineHeight: 19 },
  addBtn: { marginHorizontal: 22, marginTop: 16, borderWidth: 1.5, borderColor: colors.lineStrong, borderRadius: 13, paddingVertical: 14, alignItems: 'center' },
  addBtnText: { color: colors.ink, fontWeight: '700', fontSize: 14 },
  addBox: { marginHorizontal: 22, marginTop: 16, backgroundColor: colors.cream, borderWidth: 1.5, borderColor: colors.line, borderRadius: 16, padding: 14 },
  input: { borderWidth: 1.5, borderColor: colors.lineStrong, borderRadius: 11, padding: 12, fontSize: 14, color: colors.ink, backgroundColor: '#fff' },
  error: { color: colors.alert, fontSize: 12.5, fontWeight: '600', marginTop: 8 },
  row: { flexDirection: 'row', alignItems: 'center', paddingVertical: 14, borderTopWidth: 1, borderTopColor: colors.line },
  title: { fontSize: 14, fontWeight: '700', color: colors.ink },
  address: { fontSize: 12, color: colors.muted, marginTop: 2 },
  status: { fontSize: 11.5, fontWeight: '700', color: colors.signalDeep, textAlign: 'right' },
});
