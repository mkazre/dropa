import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { colors } from '../theme/tokens';

/**
 * The Dropa wordmark: a rounded yellow "dot" (echoing the locker-door
 * glyph in the app icon) plus the "Dropa" name. Mirrors the prototype's
 * `.wordmark` component so the app reads as the same brand.
 */
export default function Logo({ size = 28, dark = false }: { size?: number; dark?: boolean }) {
  return (
    <View style={styles.row}>
      <View style={[styles.dot, { width: size * 0.5, height: size * 0.5, borderRadius: size * 0.16 }]} />
      <Text style={[styles.word, { fontSize: size, color: dark ? colors.ink : '#fff' }]}>Dropa</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  row: { flexDirection: 'row', alignItems: 'center', gap: 10 },
  dot: { backgroundColor: colors.signal },
  word: { fontWeight: '800', letterSpacing: -1.2 },
});
