import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useAuth } from '../context/AuthContext';
import { Button, Card } from '../components/ui';
import { colors } from '../theme/tokens';
import type { AppStackParamList } from '../navigation/types';

export default function ProfileScreen() {
  const navigation = useNavigation<NativeStackNavigationProp<AppStackParamList>>();
  const { me, logout } = useAuth();

  return (
    <SafeAreaView style={styles.screen} edges={['top']}>
      <View style={{ padding: 22 }}>
        <Text style={styles.header}>Profile</Text>

        <Card style={{ marginTop: 20 }}>
          <Row label="Name" value={me?.name ?? '—'} />
          <Row label="Email" value={me?.email ?? '—'} />
          <Row label="Phone" value={me?.phone ?? '—'} />
          <Row label="Property" value={me?.property?.name ?? '—'} />
          <Row label="Unit" value={me?.unit?.unit_number ?? '—'} last />
        </Card>

        <Button title="Change password" variant="ghost" onPress={() => navigation.navigate('ChangePassword')} style={{ marginTop: 20 }} />
        <Button title="Log out" variant="dark" onPress={logout} style={{ marginTop: 10 }} />
      </View>
    </SafeAreaView>
  );
}

function Row({ label, value, last = false }: { label: string; value: string; last?: boolean }) {
  return (
    <View style={[styles.row, !last && styles.rowBorder]}>
      <Text style={styles.rowLabel}>{label}</Text>
      <Text style={styles.rowValue}>{value}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.paper },
  header: { fontSize: 22, fontWeight: '800', letterSpacing: -0.5, color: colors.ink },
  row: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 10 },
  rowBorder: { borderBottomWidth: 1, borderBottomColor: colors.line },
  rowLabel: { color: colors.muted, fontSize: 13.5 },
  rowValue: { color: colors.ink, fontSize: 13.5, fontWeight: '700' },
});
