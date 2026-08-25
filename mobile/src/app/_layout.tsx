import { Stack, Redirect } from 'expo-router';
import { StatusBar } from 'expo-status-bar';

import '../global.css';

import { AuthProvider, useAuth } from '@/lib/auth';

function Gate() {
  const { token } = useAuth();

  return (
    <>
      <StatusBar style="auto" />
      <Stack screenOptions={{ headerShown: false }}>
        <Stack.Screen name="(auth)" redirect={Boolean(token)} />
        <Stack.Screen name="(tabs)" redirect={!token} />
        <Stack.Screen
          name="enroll"
          options={{ headerShown: true, title: 'Enroll', headerTintColor: '#0f172a' }}
        />
        <Stack.Screen
          name="payment-new"
          options={{ headerShown: true, title: 'New Payment', headerTintColor: '#0f172a' }}
        />
        <Stack.Screen
          name="announcements/index"
          options={{ headerShown: true, title: 'Announcements', headerTintColor: '#0f172a' }}
        />
        <Stack.Screen
          name="announcements/[id]"
          options={{ headerShown: true, title: 'Announcement', headerTintColor: '#0f172a' }}
        />
      </Stack>
    </>
  );
}

export default function RootLayout() {
  return (
    <AuthProvider>
      <Gate />
    </AuthProvider>
  );
}
