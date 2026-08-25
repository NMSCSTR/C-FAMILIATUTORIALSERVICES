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
