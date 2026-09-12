import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import HomeScreen from '../screens/HomeScreen';
import ParcelsScreen from '../screens/ParcelsScreen';
import ProfileScreen from '../screens/ProfileScreen';
import { colors } from '../theme/tokens';
import type { TabParamList } from './types';

const Tab = createBottomTabNavigator<TabParamList>();

const icons: Record<keyof TabParamList, { active: keyof typeof Ionicons.glyphMap; inactive: keyof typeof Ionicons.glyphMap }> = {
  Home: { active: 'home', inactive: 'home-outline' },
  Parcels: { active: 'cube', inactive: 'cube-outline' },
  Profile: { active: 'person-circle', inactive: 'person-circle-outline' },
};

export default function TabsNavigator() {
  // A fixed height here would clip the bar under the phone's own gesture/nav
  // bar — pad by the actual safe-area inset so it always sits above it.
  const insets = useSafeAreaInsets();

  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        headerShown: false,
        tabBarActiveTintColor: colors.ink,
        tabBarInactiveTintColor: colors.muted,
        tabBarStyle: {
          borderTopColor: colors.line,
          height: 58 + insets.bottom,
          paddingBottom: Math.max(insets.bottom, 8),
          paddingTop: 6,
        },
        tabBarIcon: ({ color, focused }) => {
          const name = icons[route.name as keyof TabParamList];
          return <Ionicons name={focused ? name.active : name.inactive} size={23} color={color} />;
        },
        tabBarLabelStyle: { fontSize: 11, fontWeight: '700' },
      })}
    >
      <Tab.Screen name="Home" component={HomeScreen} />
      <Tab.Screen name="Parcels" component={ParcelsScreen} />
      <Tab.Screen name="Profile" component={ProfileScreen} />
    </Tab.Navigator>
  );
}
