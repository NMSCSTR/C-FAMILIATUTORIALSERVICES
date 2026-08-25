import { Stack, Redirect } from 'expo-router';

import { useAuth } from '@/lib/auth';

export default function AuthLayout() {
  const { token } = useAuth();

  if (token) {
    return <Redirect href="/(tabs)" />;
  }

  return (
    <Stack screenOptions={{ headerShown: false, contentStyle: { backgroundColor: '#f8fafc' } }} />
  );
}
