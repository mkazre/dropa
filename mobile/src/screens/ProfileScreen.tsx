import React from 'react';
import { View, Text, StyleSheet, Switch } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useAuth } from '../context/AuthContext';
import { useAccessibility } from '../context/AccessibilityContext';
import { Button, Card } from '../components/ui';
import { colors } from '../theme/tokens';
import type { AppStackParamList } from '../navigation/types';

export default function ProfileScreen() {
  const navigation = useNavigation<NativeStackNavigationProp<AppStackParamList>>();
  const { me, logout } = useAuth();
  const { largeText, toggleLargeText, scale } = useAccessibility();

  return (
    <SafeAreaView style={styles.screen} edges={['top']}>
      <View style={{ padding: 22 }}>
        <Text style={[styles.header, { fontSize: scale(22) }]}>Profile</Text>

        <Card style={{ marginTop: 20 }}>
          <Row label="Name" value={me?.name ?? '—'} scale={scale} />
          <Row label="Email" value={me?.email ?? '—'} scale={scale} />
          <Row label="Phone" value={me?.phone ?? '—'} scale={scale} />
          <Row label="Property" value={me?.property?.name ?? '—'} scale={scale} />
          <Row label="Unit" value={me?.unit?.unit_number ?? '—'} scale={scale} last={!me?.household?.length} />
          {!!me?.household?.length && (
            <Row label="Also on this unit" value={me.household.join(', ')} scale={scale} last />
          )}
        </Card>

        <View style={styles.accessRow}>
          <View style={{ flex: 1 }}>
            <Text style={[styles.accessLabel, { fontSize: scale(14.5) }]}>Large text mode</Text>
            <Text style={[styles.accessSub, { fontSize: scale(12) }]}>Bigger PINs, codes and buttons — handy at the locker.</Text>
          </View>
          <Switch value={largeText} onValueChange={toggleLargeText} trackColor={{ true: colors.signal }} />
        </View>

        <Button title="Change password" variant="ghost" onPress={() => navigation.navigate('ChangePassword')} style={{ marginTop: 20 }} />
        <Button title="Log out" variant="dark" onPress={logout} style={{ marginTop: 10 }} />
      </View>
    </SafeAreaView>
  );
}

function Row({ label, value, last = false, scale }: { label: string; value: string; last?: boolean; scale: (n: number) => number }) {
  return (
    <View style={[styles.row, !last && styles.rowBorder]}>
      <Text style={[styles.rowLabel, { fontSize: scale(13.5) }]}>{label}</Text>
      <Text style={[styles.rowValue, { fontSize: scale(13.5) }]}>{value}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.paper },
  header: { fontWeight: '800', letterSpacing: -0.5, color: colors.ink },
  row: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 10, gap: 12 },
  rowBorder: { borderBottomWidth: 1, borderBottomColor: colors.line },
  rowLabel: { color: colors.muted },
  rowValue: { color: colors.ink, fontWeight: '700', flexShrink: 1, textAlign: 'right' },
  accessRow: {
    flexDirection: 'row', alignItems: 'center', gap: 14, marginTop: 20,
    borderWidth: 1.5, borderColor: colors.line, borderRadius: 16, padding: 14,
  },
  accessLabel: { fontWeight: '700', color: colors.ink },
  accessSub: { color: colors.muted, marginTop: 2 },
});
