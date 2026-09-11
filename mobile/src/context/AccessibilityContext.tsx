import React, { createContext, useContext, useEffect, useState, useCallback } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';

const LARGE_TEXT_KEY = 'dropa.largeText';
const SCALE = 1.3;

type AccessibilityState = {
  largeText: boolean;
  toggleLargeText: () => void;
  /** Multiplies a base font size by the large-text scale when enabled. */
  scale: (baseSize: number) => number;
};

const AccessibilityContext = createContext<AccessibilityState | null>(null);

export function AccessibilityProvider({ children }: { children: React.ReactNode }) {
  const [largeText, setLargeText] = useState(false);

  useEffect(() => {
    AsyncStorage.getItem(LARGE_TEXT_KEY).then((v) => setLargeText(v === 'true'));
  }, []);

  const toggleLargeText = useCallback(() => {
    setLargeText((prev) => {
      const next = !prev;
      AsyncStorage.setItem(LARGE_TEXT_KEY, String(next)).catch(() => {});
      return next;
    });
  }, []);

  const scale = useCallback((baseSize: number) => (largeText ? Math.round(baseSize * SCALE) : baseSize), [largeText]);

  return (
    <AccessibilityContext.Provider value={{ largeText, toggleLargeText, scale }}>
      {children}
    </AccessibilityContext.Provider>
  );
}

export function useAccessibility(): AccessibilityState {
  const ctx = useContext(AccessibilityContext);
  if (!ctx) throw new Error('useAccessibility must be used within AccessibilityProvider');
  return ctx;
}
