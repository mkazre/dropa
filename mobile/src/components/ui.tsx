import React from 'react';
import {
  ActivityIndicator,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  TextInputProps,
  View,
  ViewStyle,
} from 'react-native';
import { colors, radius } from '../theme/tokens';

export function Button({
  title,
  onPress,
  variant = 'primary',
  loading = false,
  disabled = false,
  style,
}: {
  title: string;
  onPress: () => void;
  variant?: 'primary' | 'dark' | 'ghost';
  loading?: boolean;
  disabled?: boolean;
  style?: ViewStyle;
}) {
  const isDisabled = disabled || loading;
  return (
    <Pressable
      onPress={onPress}
      disabled={isDisabled}
      style={[styles.btn, variants[variant], isDisabled && styles.btnDisabled, style]}
    >
      {loading ? (
        <ActivityIndicator color={variant === 'primary' ? '#241f00' : '#fff'} />
      ) : (
        <Text style={[styles.btnText, variant === 'primary' ? styles.btnTextDark : styles.btnTextLight, variant === 'ghost' && styles.btnTextGhost]}>
          {title}
        </Text>
      )}
    </Pressable>
  );
}

export function Field({ label, ...props }: { label: string } & TextInputProps) {
  return (
    <View style={{ marginTop: 14 }}>
      <Text style={styles.label}>{label}</Text>
      <TextInput
        placeholderTextColor={colors.muted}
        style={styles.input}
        {...props}
      />
    </View>
  );
}

export function ChoiceCard({
  title,
  subtitle,
  trailing,
  selected = false,
  disabled = false,
  onPress,
}: {
  title: string;
  subtitle?: string;
  trailing?: string;
  selected?: boolean;
  disabled?: boolean;
  onPress?: () => void;
}) {
  return (
    <Pressable
      onPress={onPress}
      disabled={disabled}
      style={[styles.choice, selected && styles.choiceSelected, disabled && styles.choiceDisabled]}
    >
      <View style={{ flex: 1 }}>
        <Text style={styles.choiceTitle}>{title}</Text>
        {subtitle ? <Text style={styles.choiceSubtitle}>{subtitle}</Text> : null}
      </View>
      {trailing ? <Text style={styles.choiceTrailing}>{trailing}</Text> : null}
    </Pressable>
  );
}

export function Pill({ label, tone = 'signal' }: { label: string; tone?: 'signal' | 'ok' | 'alert' }) {
  return (
    <View style={[styles.pill, pillTones[tone]]}>
      <Text style={[styles.pillText, pillTextTones[tone]]}>{label}</Text>
    </View>
  );
}

export function Card({ children, style }: { children: React.ReactNode; style?: ViewStyle }) {
  return <View style={[styles.card, style]}>{children}</View>;
}

export function EmptyState({ title, body }: { title: string; body: string }) {
  return (
    <View style={styles.empty}>
      <Text style={styles.emptyTitle}>{title}</Text>
      <Text style={styles.emptyBody}>{body}</Text>
    </View>
  );
}

const variants = StyleSheet.create({
  primary: { backgroundColor: colors.signal },
  dark: { backgroundColor: colors.ink },
  ghost: { backgroundColor: 'transparent', borderWidth: 1.5, borderColor: colors.lineStrong },
});

const pillTones = StyleSheet.create({
  signal: { backgroundColor: colors.okSoft },
  ok: { backgroundColor: colors.okSoft },
  alert: { backgroundColor: '#FBEAEA' },
});
const pillTextTones = StyleSheet.create({
  signal: { color: colors.ok },
  ok: { color: colors.ok },
  alert: { color: colors.alert },
});

const styles = StyleSheet.create({
  btn: { borderRadius: radius.sm + 3, paddingVertical: 16, alignItems: 'center', justifyContent: 'center' },
  btnDisabled: { opacity: 0.45 },
  btnText: { fontSize: 16, fontWeight: '700' },
  btnTextDark: { color: '#241f00' },
  btnTextLight: { color: '#fff' },
  btnTextGhost: { color: colors.ink },
  label: { fontSize: 12.5, fontWeight: '700', color: colors.inkSoft, marginBottom: 6 },
  input: {
    borderWidth: 1.5, borderColor: colors.lineStrong, borderRadius: 13, padding: 14,
    fontSize: 16, color: colors.ink, backgroundColor: '#fff',
  },
  choice: {
    flexDirection: 'row', alignItems: 'center', gap: 12, borderWidth: 1.5, borderColor: colors.line,
    borderRadius: radius.md, padding: 15, marginTop: 12, backgroundColor: '#fff',
  },
  choiceSelected: { borderColor: colors.ink, backgroundColor: '#FFFBEC' },
  choiceDisabled: { opacity: 0.5 },
  choiceTitle: { fontSize: 15.5, fontWeight: '700', color: colors.ink },
  choiceSubtitle: { fontSize: 12.5, color: colors.muted, marginTop: 2 },
  choiceTrailing: { fontWeight: '800', fontSize: 15, color: colors.ink },
  pill: { paddingHorizontal: 8, paddingVertical: 3, borderRadius: 999, alignSelf: 'flex-start' },
  pillText: { fontSize: 11, fontWeight: '700' },
  card: { borderWidth: 1.5, borderColor: colors.line, borderRadius: radius.md, padding: 16, backgroundColor: '#FCFBF7' },
  empty: { alignItems: 'center', paddingVertical: 60, paddingHorizontal: 24 },
  emptyTitle: { fontSize: 17, fontWeight: '800', color: colors.ink, marginBottom: 6 },
  emptyBody: { fontSize: 13.5, color: colors.muted, textAlign: 'center', lineHeight: 20 },
});
