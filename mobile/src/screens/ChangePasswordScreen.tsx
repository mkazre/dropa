import React, { useState } from 'react';
import { View, Text, StyleSheet, Pressable } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import { Button, Field } from '../components/ui';
import { AuthApi } from '../api';
import { ApiError } from '../api/client';
import { colors } from '../theme/tokens';
import type { AppStackParamList } from '../navigation/types';

type Props = NativeStackScreenProps<AppStackParamList, 'ChangePassword'>;

export default function ChangePasswordScreen({ navigation }: Props) {
  const [password, setPassword] = useState('');
  const [confirm, setConfirm] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [done, setDone] = useState(false);
  const [loading, setLoading] = useState(false);

  const submit = async () => {
    if (password !== confirm) {
      setError('Passwords do not match.');
      return;
    }
    setError(null);
    setLoading(true);
    try {
      await AuthApi.changePassword(password);
      setDone(true);
    } catch (e) {
      setError(e instanceof ApiError ? e.message : 'Could not update your password.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.screen}>
      <View style={styles.topbar}>
        <Pressable onPress={() => navigation.goBack()}><Text style={styles.back}>‹</Text></Pressable>
        <Text style={styles.h2}>Change password</Text>
      </View>
      <View style={{ padding: 22 }}>
        {done ? (
          <Text style={styles.success}>Password updated ✓</Text>
        ) : (
          <>
            <Field label="New password" value={password} onChangeText={setPassword} secureTextEntry placeholder="At least 8 characters" />
            <Field label="Confirm new password" value={confirm} onChangeText={setConfirm} secureTextEntry placeholder="Retype your new password" />
            {error ? <Text style={styles.error}>{error}</Text> : null}
            <Button title="Update password" onPress={submit} loading={loading} style={{ marginTop: 20 }} />
          </>
        )}
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.paper, paddingTop: 50 },
  topbar: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingHorizontal: 22 },
  back: { fontSize: 24, color: colors.ink, width: 24 },
  h2: { fontSize: 19, fontWeight: '800', color: colors.ink },
  error: { color: colors.alert, fontSize: 13, fontWeight: '600', marginTop: 14 },
  success: { color: colors.ok, fontWeight: '700', fontSize: 15 },
});
