import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, Pressable } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import { ChoiceCard, Button } from '../components/ui';
import { ReservationsApi, PropertiesApi } from '../api';
import { ApiError } from '../api/client';
import type { LockerAvailability, LockerSize } from '../api/types';
import { colors } from '../theme/tokens';
import type { AppStackParamList } from '../navigation/types';

type Props = NativeStackScreenProps<AppStackParamList, 'ReserveSize'>;

const SIZES: { key: LockerSize; label: string; blurb: string }[] = [
  { key: 'S', label: 'Small', blurb: 'Envelope · up to 3 kg' },
  { key: 'M', label: 'Medium', blurb: 'Shoebox · up to 8 kg' },
  { key: 'L', label: 'Large', blurb: 'Carry-on · up to 15 kg' },
  { key: 'XL', label: 'Extra large', blurb: 'Large box · up to 25 kg' },
];

export default function ReserveSizeScreen({ navigation }: Props) {
  const [availability, setAvailability] = useState<LockerAvailability | null>(null);
  const [selected, setSelected] = useState<LockerSize>('M');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    PropertiesApi.availability().then(setAvailability).catch(() => {});
  }, []);

  const confirm = async () => {
    setError(null);
    setLoading(true);
    try {
      const reservation = await ReservationsApi.create(selected);
      navigation.replace('ReserveShare', { reservationId: reservation.id });
    } catch (e) {
      setError(e instanceof ApiError ? e.message : 'Could not reserve a locker. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.screen}>
      <View style={styles.topbar}>
        <Pressable onPress={() => navigation.goBack()}><Text style={styles.back}>‹</Text></Pressable>
        <Text style={styles.h2}>What size?</Text>
      </View>
      <ScrollView contentContainerStyle={{ paddingHorizontal: 22 }}>
        <Text style={styles.sub}>Not sure? Medium fits most parcels — you can always ask the courier.</Text>
        {SIZES.map((s) => {
          const count = availability?.[s.key];
          const full = count === 0;
          return (
            <ChoiceCard
              key={s.key}
              title={s.label}
              subtitle={s.blurb}
              trailing={count === undefined ? undefined : full ? 'Full' : `${count} open`}
              selected={selected === s.key}
              disabled={full}
              onPress={() => setSelected(s.key)}
            />
          );
        })}
        {error ? <Text style={styles.error}>{error}</Text> : null}
      </ScrollView>
      <View style={{ padding: 22 }}>
        <Button title="Reserve this locker" onPress={confirm} loading={loading} />
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.paper, paddingTop: 50 },
  topbar: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingHorizontal: 22, marginBottom: 8 },
  back: { fontSize: 24, color: colors.ink, width: 24 },
  h2: { fontSize: 19, fontWeight: '800', color: colors.ink },
  sub: { color: colors.muted, fontSize: 13.5, marginTop: 4, lineHeight: 20 },
  error: { color: colors.alert, fontSize: 13, fontWeight: '600', marginTop: 14 },
});
