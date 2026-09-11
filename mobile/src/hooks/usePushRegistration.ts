import { useEffect } from 'react';
import * as Notifications from 'expo-notifications';
import { AccountApi } from '../api';

/**
 * Once a tenant is signed in, ask for notification permission and hand our
 * Expo push token to the backend so deposit/collect notifications can reach
 * this device. Silently does nothing if permission is denied — the app
 * still works, the tenant just relies on email/SMS instead.
 */
export function usePushRegistration(enabled: boolean) {
  useEffect(() => {
    if (!enabled) return;

    (async () => {
      try {
        const { status: existing } = await Notifications.getPermissionsAsync();
        let status = existing;
        if (status !== 'granted') {
          const req = await Notifications.requestPermissionsAsync();
          status = req.status;
        }
        if (status !== 'granted') return;

        const { data: token } = await Notifications.getExpoPushTokenAsync();
        await AccountApi.registerPushToken(token);
      } catch {
        // No push hardware (simulator), no project id configured, or the
        // request failed — none of that should block using the app.
      }
    })();
  }, [enabled]);
}
