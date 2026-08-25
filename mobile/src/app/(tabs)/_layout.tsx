import { Redirect, Tabs } from 'expo-router';
import { Text } from 'react-native';

import { useAuth } from '@/lib/auth';

function TabIcon({ glyph, focused }: { glyph: string; focused: boolean }) {
  return (
    <Text className={`text-lg ${focused ? 'text-brand' : 'text-slate-400'}`} style={{ lineHeight: 24 }}>
      {glyph}
    </Text>
  );
}

export default function TabsLayout() {
  const { token } = useAuth();

  if (!token) {
    return <Redirect href="/(auth)/login" />;
  }

  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: '#2563eb',
        tabBarInactiveTintColor: '#94a3b8',
      }}
    >
      <Tabs.Screen
        name="index"
        options={{
          title: 'Home',
          tabBarIcon: ({ focused }) => <TabIcon glyph="🏠" focused={focused} />,
        }}
      />
      <Tabs.Screen
        name="payments"
        options={{
          title: 'Payments',
          tabBarIcon: ({ focused }) => <TabIcon glyph="💳" focused={focused} />,
        }}
      />
      <Tabs.Screen
        name="resources"
        options={{
          title: 'Resources',
          tabBarIcon: ({ focused }) => <TabIcon glyph="📚" focused={focused} />,
        }}
      />
      <Tabs.Screen
        name="profile"
        options={{
          title: 'Profile',
          tabBarIcon: ({ focused }) => <TabIcon glyph="👤" focused={focused} />,
        }}
      />
    </Tabs>
  );
}
