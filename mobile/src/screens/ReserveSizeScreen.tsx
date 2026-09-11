import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, Pressable, TextInput } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import { ChoiceCard, Button } from '../components/ui';
import { ReservationsApi, PropertiesApi, PublicSitesApi } from '../api';
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

export default function ReserveSizeScreen({ navigation, route }: Props) {
  const publicSite = route.params?.publicSite;

  const [availability, setAvailability] = useState<LockerAvailability | null>(null);
  const [selected, setSelected] = useState<LockerSize>('M');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const [forSomeoneElse, setForSomeoneElse] = useState(false);
  const [recipientQuery, setRecipientQuery] = useState('');
  const [recipient, setRecipient] = useState<Resident | null>(null);
  const [suggestions, setSuggestions] = useState<Resident[]>([]);

  const [showDims, setShowDims] = useState(false);
  const [weight, setWeight] = useState('');
  const [length, setLength] = useState('');
  const [width, setWidth] = useState('');
  const [height, setHeight] = useState('');
  const [recoNote, setRecoNote] = useState<string | null>(null);

  useEffect(() => {
    const loadAvailability = publicSite
      ? PublicSitesApi.availability(publicSite.id)
      : PropertiesApi.availability();
    loadAvailability.then(setAvailability).catch(() => {});
  }, [publicSite]);

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

  const recommendSize = async () => {
    setRecoNote(null);
    try {
      const { size } = await PropertiesApi.recommendSize({
        weight_kg: weight ? Number(weight) : undefined,
        length_cm: length ? Number(length) : undefined,
        width_cm: width ? Number(width) : undefined,
        height_cm: height ? Number(height) : undefined,
      });
      setSelected(size);
      setRecoNote(`We suggest ${SIZES.find((s) => s.key === size)?.label} for that parcel.`);
    } catch (e) {
      setRecoNote(e instanceof ApiError ? e.message : "Couldn't work out a size for that.");
    }
  };

  const confirm = async () => {
    if (forSomeoneElse && !recipient) {
      setError('Please pick who this parcel is for.');
      return;
    }
    setError(null);
    setLoading(true);
    try {
      const reservation = await ReservationsApi.create(selected, {
        recipientUnitNumber: recipient?.unit_number,
        publicPropertyId: publicSite?.id,
      });
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
        <Text style={styles.h2}>{publicSite ? publicSite.name : 'Expecting a parcel'}</Text>
      </View>
      <ScrollView contentContainerStyle={{ paddingHorizontal: 22 }} keyboardShouldPersistTaps="handled">
        {!publicSite && (
          <>
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
          </>
        )}

        <Text style={[styles.label, { marginTop: 18 }]}>Size</Text>
        <Text style={styles.sub}>Not sure? Medium fits most parcels — you can always ask the courier.</Text>

        {showDims ? (
          <View style={styles.dimsBox}>
            <View style={styles.dimsRow}>
              <TextInput value={weight} onChangeText={setWeight} placeholder="Weight (kg)" keyboardType="decimal-pad" placeholderTextColor={colors.muted} style={[styles.dimsInput, { flex: 1 }]} />
            </View>
            <View style={styles.dimsRow}>
              <TextInput value={length} onChangeText={setLength} placeholder="Length (cm)" keyboardType="decimal-pad" placeholderTextColor={colors.muted} style={[styles.dimsInput, { flex: 1 }]} />
              <TextInput value={width} onChangeText={setWidth} placeholder="Width (cm)" keyboardType="decimal-pad" placeholderTextColor={colors.muted} style={[styles.dimsInput, { flex: 1 }]} />
              <TextInput value={height} onChangeText={setHeight} placeholder="Height (cm)" keyboardType="decimal-pad" placeholderTextColor={colors.muted} style={[styles.dimsInput, { flex: 1 }]} />
            </View>
            <Button title="Suggest a size" variant="ghost" onPress={recommendSize} style={{ marginTop: 8 }} />
            {recoNote ? <Text style={styles.recoNote}>{recoNote}</Text> : null}
          </View>
        ) : (
          <Pressable onPress={() => setShowDims(true)}>
            <Text style={styles.link}>Know the package's size? Let us suggest a locker.</Text>
          </Pressable>
        )}

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
  link: { color: colors.signalDeep, fontSize: 12.5, fontWeight: '700', marginBottom: 12 },
  dimsBox: { backgroundColor: colors.cream, borderWidth: 1.5, borderColor: colors.line, borderRadius: radius.md, padding: 14, marginBottom: 12 },
  dimsRow: { flexDirection: 'row', gap: 8, marginBottom: 8 },
  dimsInput: { borderWidth: 1.5, borderColor: colors.lineStrong, borderRadius: 11, padding: 11, fontSize: 13.5, color: colors.ink, backgroundColor: '#fff' },
  recoNote: { color: colors.ok, fontSize: 12.5, fontWeight: '700', marginTop: 8 },
  error: { color: colors.alert, fontSize: 13, fontWeight: '600', marginTop: 14 },
});
