import React, { useState } from 'react';
import { View, Text, StyleSheet, Pressable, Modal } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { Ionicons } from '@expo/vector-icons';
import Logo from './Logo';
import { useAuth } from '../context/AuthContext';
import { colors, radius } from '../theme/tokens';
import type { AppStackParamList } from '../navigation/types';

type MenuLink = { label: string; icon: keyof typeof Ionicons.glyphMap; onPress: () => void };

/** Logo + hamburger menu, shared across every tab screen so the brand and a way to reach secondary screens is always on-screen. */
export default function AppHeader() {
  const navigation = useNavigation<NativeStackNavigationProp<AppStackParamList>>();
  const { logout } = useAuth();
  const [open, setOpen] = useState(false);

  const go = (screen: keyof AppStackParamList) => {
    setOpen(false);
    navigation.navigate(screen as never);
  };

  const links: MenuLink[] = [
    { label: 'Track a delivery', icon: 'navigate-outline', onPress: () => go('TrackDeliveries') },
    { label: 'Public lockers', icon: 'location-outline', onPress: () => go('PublicLockers') },
    { label: 'Change password', icon: 'key-outline', onPress: () => go('ChangePassword') },
    { label: 'Log out', icon: 'log-out-outline', onPress: () => { setOpen(false); logout(); } },
  ];

  return (
    <View style={styles.row}>
      <Logo size={22} dark />
      <Pressable hitSlop={12} onPress={() => setOpen(true)} accessibilityLabel="Open menu">
        <Ionicons name="menu" size={26} color={colors.ink} />
      </Pressable>

      <Modal visible={open} transparent animationType="fade" onRequestClose={() => setOpen(false)}>
        <Pressable style={styles.overlay} onPress={() => setOpen(false)}>
          <View style={styles.panel}>
            {links.map((link, i) => (
              <Pressable
                key={link.label}
                style={[styles.item, i < links.length - 1 && styles.itemBorder]}
                onPress={link.onPress}
              >
                <Ionicons name={link.icon} size={18} color={colors.ink} />
                <Text style={styles.itemText}>{link.label}</Text>
              </Pressable>
            ))}
          </View>
        </Pressable>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  row: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 22, paddingTop: 6, paddingBottom: 10 },
  overlay: { flex: 1, backgroundColor: 'rgba(33,30,23,0.35)' },
  panel: {
    position: 'absolute', top: 60, right: 20, minWidth: 210,
    backgroundColor: '#fff', borderRadius: radius.md, borderWidth: 1, borderColor: colors.line,
    paddingVertical: 6, shadowColor: '#000', shadowOpacity: 0.15, shadowRadius: 12, shadowOffset: { width: 0, height: 6 }, elevation: 6,
  },
  item: { flexDirection: 'row', alignItems: 'center', gap: 10, paddingHorizontal: 16, paddingVertical: 13 },
  itemBorder: { borderBottomWidth: 1, borderBottomColor: colors.line },
  itemText: { fontSize: 14, fontWeight: '700', color: colors.ink },
});
