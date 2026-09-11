import React, { useCallback, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, RefreshControl, Pressable, Image } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useFocusEffect, useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import Svg, { Path, Rect } from 'react-native-svg';
import { useAuth } from '../context/AuthContext';
import { ParcelsApi } from '../api';
import type { Parcel } from '../api/types';
import { attributionSuffix } from '../utils/attribution';
import { colors, radius } from '../theme/tokens';
import type { AppStackParamList } from '../navigation/types';

export default function HomeScreen() {
  const navigation = useNavigation<NativeStackNavigationProp<AppStackParamList>>();
  const { me } = useAuth();
  const [parcels, setParcels] = useState<Parcel[]>([]);
  const [refreshing, setRefreshing] = useState(false);

  const load = useCallback(async () => {
    try {
      const all = await ParcelsApi.mine();
      setParcels(all.filter((p) => p.status === 'awaiting_collection'));
    } catch {
      // best-effort — home screen still renders without the list
    }
  }, []);

  useFocusEffect(useCallback(() => { load(); }, [load]));

  const onRefresh = async () => {
    setRefreshing(true);
    await load();
    setRefreshing(false);
  };

  const firstName = me?.name?.split(' ')[0] ?? 'there';
  const brandColor = me?.property?.brand_color || colors.signal;

  return (
    <SafeAreaView style={styles.screen} edges={['top']}>
      <ScrollView
        contentContainerStyle={{ padding: 22, paddingBottom: 40 }}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.ink} />}
      >
        <View style={styles.headerRow}>
          <View style={{ flex: 1 }}>
            <Text style={styles.greeting}>Sawubona, {firstName}</Text>
            <Text style={styles.propertyLine}>{me?.property?.name ?? 'Your property'}{me?.unit ? ` · Unit ${me.unit.unit_number}` : ''}</Text>
          </View>
          {me?.property?.logo_url ? (
            <Image source={{ uri: me.property.logo_url }} style={styles.logo} resizeMode="contain" />
          ) : null}
        </View>

        <View style={styles.tiles}>
          <Pressable style={[styles.tile, styles.tilePrime]} onPress={() => navigation.navigate('ReserveSize')}>
            <View style={[styles.glyph, { backgroundColor: brandColor }]}>
              <Svg width={20} height={20} viewBox="0 0 24 24"><Path d="M12 19V5M6 11l6-6 6 6" stroke="#241f00" strokeWidth={2.2} fill="none" strokeLinecap="round" strokeLinejoin="round" /></Svg>
            </View>
            <Text style={styles.tileTitlePrime}>Expecting a parcel</Text>
            <Text style={styles.tileSubPrime}>Reserve a locker</Text>
          </Pressable>
          <Pressable style={styles.tile} onPress={() => navigation.navigate('Collect')}>
            <View style={styles.glyph}>
              <Svg width={20} height={20} viewBox="0 0 24 24"><Path d="M12 5v14M6 13l6 6 6-6" stroke="#241f00" strokeWidth={2.2} fill="none" strokeLinecap="round" strokeLinejoin="round" /></Svg>
            </View>
            <Text style={styles.tileTitle}>Collect</Text>
            <Text style={styles.tileSub}>Pick a parcel up</Text>
          </Pressable>
        </View>

        <View style={styles.linksRow}>
          <Pressable onPress={() => navigation.navigate('TrackDeliveries')}>
            <Text style={styles.linkText}>Track a delivery ›</Text>
          </Pressable>
          <Pressable onPress={() => navigation.navigate('PublicLockers')}>
            <Text style={styles.linkText}>Public lockers ›</Text>
          </Pressable>
        </View>

        <Text style={styles.sectionLabel}>Awaiting collection</Text>
        {parcels.length === 0 ? (
          <View style={styles.emptyCard}>
            <Text style={styles.emptyText}>No parcels waiting right now.</Text>
          </View>
        ) : (
          parcels.map((p) => (
            <Pressable key={p.id} style={styles.parcelRow} onPress={() => navigation.navigate('ParcelDetail', { parcelId: p.id })}>
              <View style={styles.parcelBox}>
                <Svg width={18} height={18} viewBox="0 0 24 24"><Rect x="3" y="8" width="18" height="10" rx="1.5" stroke="#E0A500" strokeWidth={2} fill="none" /></Svg>
              </View>
              <View style={{ flex: 1 }}>
                <Text style={styles.parcelTitle}>From {p.sender_name ?? 'a courier'}</Text>
                <Text style={styles.parcelSub}>
                  Pickup PIN {p.pickup_pin}{attributionSuffix(me?.name, p.reserved_by, p.sent_by)}
                </Text>
              </View>
              <Text style={styles.parcelStatus}>Awaiting{'\n'}collection</Text>
            </Pressable>
          ))
        )}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.paper },
  headerRow: { flexDirection: 'row', alignItems: 'center', gap: 12 },
  logo: { width: 44, height: 44, borderRadius: 10 },
  greeting: { fontSize: 22, fontWeight: '800', letterSpacing: -0.5, color: colors.ink },
  propertyLine: { color: colors.muted, fontSize: 13.5, marginTop: 4 },
  tiles: { flexDirection: 'row', gap: 12, marginTop: 22 },
  linksRow: { flexDirection: 'row', justifyContent: 'space-between', marginTop: 16 },
  linkText: { color: colors.signalDeep, fontWeight: '700', fontSize: 12.5 },
  tile: { flex: 1, borderRadius: radius.lg - 2, padding: 16, borderWidth: 1.5, borderColor: colors.line, backgroundColor: '#fff' },
  tilePrime: { backgroundColor: colors.ink, borderColor: colors.ink },
  glyph: { width: 36, height: 36, borderRadius: 10, backgroundColor: colors.signal, alignItems: 'center', justifyContent: 'center', marginBottom: 36 },
  tileTitle: { fontSize: 15, fontWeight: '800', color: colors.ink },
  tileSub: { fontSize: 11.5, color: colors.muted, marginTop: 2 },
  tileTitlePrime: { fontSize: 15, fontWeight: '800', color: '#fff' },
  tileSubPrime: { fontSize: 11.5, color: '#B9B4A2', marginTop: 2 },
  sectionLabel: { fontSize: 12, fontWeight: '800', color: colors.inkSoft, marginTop: 28, marginBottom: 6 },
  emptyCard: { paddingVertical: 24, alignItems: 'center' },
  emptyText: { color: colors.muted, fontSize: 13.5 },
  parcelRow: { flexDirection: 'row', alignItems: 'center', gap: 13, paddingVertical: 14, borderTopWidth: 1, borderTopColor: colors.line },
  parcelBox: { width: 40, height: 40, borderRadius: 10, backgroundColor: '#FFFBEC', borderWidth: 1.5, borderColor: colors.signal, alignItems: 'center', justifyContent: 'center' },
  parcelTitle: { fontSize: 14.5, fontWeight: '700', color: colors.ink },
  parcelSub: { fontSize: 12, color: colors.muted, marginTop: 2 },
  parcelStatus: { fontSize: 11, fontWeight: '700', color: colors.signalDeep, textAlign: 'right' },
});
