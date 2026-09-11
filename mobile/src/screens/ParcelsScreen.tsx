import React, { useCallback, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl, Pressable } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useFocusEffect, useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { ParcelsApi } from '../api';
import type { Parcel } from '../api/types';
import { EmptyState } from '../components/ui';
import { colors } from '../theme/tokens';
import type { AppStackParamList } from '../navigation/types';

const statusLabel: Record<Parcel['status'], string> = {
  awaiting_deposit: 'Reserved',
  awaiting_collection: 'Awaiting collection',
  collected: 'Collected',
};

export default function ParcelsScreen() {
  const navigation = useNavigation<NativeStackNavigationProp<AppStackParamList>>();
  const [parcels, setParcels] = useState<Parcel[]>([]);
  const [refreshing, setRefreshing] = useState(false);

  const load = useCallback(async () => {
    try {
      setParcels(await ParcelsApi.mine());
    } catch {
      // keep whatever was last loaded
    }
  }, []);

  useFocusEffect(useCallback(() => { load(); }, [load]));

  return (
    <SafeAreaView style={styles.screen} edges={['top']}>
      <Text style={styles.header}>Parcels</Text>
      <FlatList
        data={parcels}
        keyExtractor={(p) => String(p.id)}
        contentContainerStyle={{ padding: 22, paddingTop: 4 }}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={async () => { setRefreshing(true); await load(); setRefreshing(false); }}
            tintColor={colors.ink}
          />
        }
        ListEmptyComponent={<EmptyState title="No parcels yet" body="Once you reserve a locker and a courier drops something off, it'll show up here." />}
        renderItem={({ item }) => (
          <Pressable style={styles.row} onPress={() => navigation.navigate('ParcelDetail', { parcelId: item.id })}>
            <View style={{ flex: 1 }}>
              <Text style={styles.title}>From {item.sender_name ?? 'a courier'}</Text>
              <Text style={styles.sub}>{item.deposited_at ? new Date(item.deposited_at).toLocaleDateString() : 'Not deposited yet'}</Text>
            </View>
            <Text style={[styles.status, item.status === 'collected' && styles.statusOk]}>{statusLabel[item.status]}</Text>
          </Pressable>
        )}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.paper },
  header: { fontSize: 22, fontWeight: '800', letterSpacing: -0.5, color: colors.ink, paddingHorizontal: 22, paddingTop: 6 },
  row: { flexDirection: 'row', alignItems: 'center', paddingVertical: 14, borderTopWidth: 1, borderTopColor: colors.line },
  title: { fontSize: 14.5, fontWeight: '700', color: colors.ink },
  sub: { fontSize: 12, color: colors.muted, marginTop: 2 },
  status: { fontSize: 11.5, fontWeight: '700', color: colors.signalDeep },
  statusOk: { color: colors.ok },
});
