import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, Pressable } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import { PublicSitesApi } from '../api';
import { EmptyState } from '../components/ui';
import type { PublicSite } from '../api';
import { colors } from '../theme/tokens';
import type { AppStackParamList } from '../navigation/types';

type Props = NativeStackScreenProps<AppStackParamList, 'PublicLockers'>;

export default function PublicLockersScreen({ navigation }: Props) {
  const [sites, setSites] = useState<PublicSite[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    PublicSitesApi.list().then(setSites).catch(() => {}).finally(() => setLoading(false));
  }, []);

  return (
    <SafeAreaView style={styles.screen}>
      <View style={styles.topbar}>
        <Pressable onPress={() => navigation.goBack()}><Text style={styles.back}>‹</Text></Pressable>
        <Text style={styles.h2}>Public lockers</Text>
      </View>
      <Text style={styles.sub}>Pay-per-use lockers at malls and business centres — no residency needed.</Text>
      <FlatList
        data={sites}
        keyExtractor={(s) => String(s.id)}
        contentContainerStyle={{ padding: 22 }}
        ListEmptyComponent={!loading ? <EmptyState title="No public sites yet" body="Check back later — more locations are being added." /> : null}
        renderItem={({ item }) => (
          <Pressable
            style={styles.row}
            onPress={() => navigation.navigate('ReserveSize', { publicSite: { id: item.id, name: item.name } })}
          >
            <View style={{ flex: 1 }}>
              <Text style={styles.title}>{item.name}</Text>
              {item.address ? <Text style={styles.address}>{item.address}</Text> : null}
            </View>
            <Text style={styles.arrow}>›</Text>
          </Pressable>
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
  row: { flexDirection: 'row', alignItems: 'center', paddingVertical: 16, borderTopWidth: 1, borderTopColor: colors.line },
  title: { fontSize: 15, fontWeight: '700', color: colors.ink },
  address: { fontSize: 12.5, color: colors.muted, marginTop: 2 },
  arrow: { fontSize: 20, color: colors.muted },
});
