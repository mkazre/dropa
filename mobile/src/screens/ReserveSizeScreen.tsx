import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, Pressable, TextInput } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import { ChoiceCard, Button } from '../components/ui';
import { ReservationsApi, PropertiesApi } from '../api';
import { ApiError } from '../api/client';
import type { LockerAvailability, LockerSize, Resident } from '../api/types';
import { colors, radius } from '../theme/tokens';
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

  const [forSomeoneElse, setForSomeoneElse] = useState(false);
  const [recipientQuery, setRecipientQuery] = useState('');
  const [recipient, setRecipient] = useState<Resident | null>(null);
  const [suggestions, setSuggestions] = useState<Resident[]>([]);

  useEffect(() => {
    PropertiesApi.availability().then(setAvailability).catch(() => {});
  }, []);

  useEffect(() => {
    if (!forSomeoneElse || recipient || recipientQuery.trim().length < 1) {
      setSuggestions([]);
      return;
    }
    const timeout = setTimeout(() => {
      PropertiesApi.residents(recipientQuery.trim()).then(setSuggestions).catch(() => setSuggestions([]));
    }, 250);
    return () => clearTimeout(timeout);
  }, [recipientQuery, forSomeoneElse, recipient]);

  const confirm = async () => {
    if (forSomeoneElse && !recipient) {
      setError('Please pick who this parcel is for.');
      return;
    }
    setError(null);
    setLoading(true);
    try {
      const reservation = await ReservationsApi.create(selected, recipient?.unit_number);
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
        <Text style={styles.h2}>Expecting a parcel</Text>
      </View>
      <ScrollView contentContainerStyle={{ paddingHorizontal: 22 }} keyboardShouldPersistTaps="handled">
        <Text style={styles.label}>Who's it for?</Text>
        <View style={styles.switch}>
          <Pressable
            style={[styles.switchBtn, !forSomeoneElse && styles.switchBtnOn]}
            onPress={() => { setForSomeoneElse(false); setRecipient(null); setRecipientQuery(''); }}
          >
            <Text style={[styles.switchText, !forSomeoneElse && styles.switchTextOn]}>Me</Text>
          </Pressable>
          <Pressable style={[styles.switchBtn, forSomeoneElse && styles.switchBtnOn]} onPress={() => setForSomeoneElse(true)}>
            <Text style={[styles.switchText, forSomeoneElse && styles.switchTextOn]}>A neighbour</Text>
          </Pressable>
        </View>

        {forSomeoneElse && (
          <View style={{ marginBottom: 10 }}>
            {recipient ? (
              <View style={styles.recipientChip}>
                <Text style={styles.recipientChipText}>{recipient.full_name} · Unit {recipient.unit_number}</Text>
                <Pressable onPress={() => { setRecipient(null); setRecipientQuery(''); }}>
                  <Text style={styles.recipientChipClear}>Change</Text>
                </Pressable>
              </View>
            ) : (
              <>
                <TextInput
                  value={recipientQuery}
                  onChangeText={setRecipientQuery}
                  placeholder="Search by name or unit number"
                  placeholderTextColor={colors.muted}
                  style={styles.input}
                />
                {suggestions.map((r) => (
                  <Pressable key={r.id} style={styles.suggestionRow} onPress={() => { setRecipient(r); setSuggestions([]); }}>
                    <Text style={styles.suggestionText}>{r.full_name} · Unit {r.unit_number}</Text>
                  </Pressable>
                ))}
              </>
            )}
            <Text style={styles.hint}>You'll drop this off yourself — no code to share, they'll just be notified.</Text>
          </View>
        )}

        <Text style={[styles.label, { marginTop: 18 }]}>Size</Text>
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
  label: { fontSize: 12, fontWeight: '800', color: colors.inkSoft, marginBottom: 8 },
  sub: { color: colors.muted, fontSize: 13.5, marginBottom: 4, lineHeight: 20 },
  switch: { flexDirection: 'row', backgroundColor: colors.line, borderRadius: 999, padding: 4, marginBottom: 12 },
  switchBtn: { flex: 1, paddingVertical: 10, borderRadius: 999, alignItems: 'center' },
  switchBtnOn: { backgroundColor: colors.ink },
  switchText: { fontWeight: '700', fontSize: 13, color: colors.inkSoft },
  switchTextOn: { color: '#fff' },
  input: { borderWidth: 1.5, borderColor: colors.lineStrong, borderRadius: 13, padding: 14, fontSize: 15, color: colors.ink, backgroundColor: '#fff' },
  suggestionRow: { paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: colors.line },
  suggestionText: { fontSize: 14, fontWeight: '600', color: colors.ink },
  recipientChip: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    backgroundColor: '#FFFBEC', borderWidth: 1.5, borderColor: colors.signal, borderRadius: radius.md, padding: 14,
  },
  recipientChipText: { fontWeight: '700', color: colors.ink, fontSize: 14 },
  recipientChipClear: { fontWeight: '700', color: colors.signalDeep, fontSize: 13 },
  hint: { color: colors.muted, fontSize: 12, marginTop: 8, lineHeight: 17 },
  error: { color: colors.alert, fontSize: 13, fontWeight: '600', marginTop: 14 },
});
