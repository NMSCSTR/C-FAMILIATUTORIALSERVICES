import { Redirect } from 'expo-router';

import { useAuth } from '@/lib/auth';

export default function IndexRoute() {
  const { ready, token } = useAuth();

  if (!ready) {
    return null;
  }

  return <Redirect href={token ? '/(tabs)' : '/(auth)/login'} />;
}
