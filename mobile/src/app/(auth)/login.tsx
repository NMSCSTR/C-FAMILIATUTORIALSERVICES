import { Link } from 'expo-router';
import { KeyboardAvoidingView, Platform, ScrollView, Text, View } from 'react-native';
import { useState } from 'react';

import { Banner, Button, Field } from '@/components/ui';
import { apiErrorMessage } from '@/lib/api';
import { useAuth } from '@/lib/auth';

export default function LoginScreen() {
  const { login } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');

  async function onSubmit() {
    if (!email.trim() || !password) {
      setError('Enter your email and password.');
      return;
    }

    setBusy(true);
    setError('');

    try {
      await login(email.trim().toLowerCase(), password);
    } catch (err) {
      setError(apiErrorMessage(err, 'Invalid email or password.'));
    } finally {
      setBusy(false);
    }
  }

  return (
    <KeyboardAvoidingView
      className="flex-1 bg-slate-50"
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <ScrollView contentContainerClassName="flex-grow justify-center px-6 py-10" keyboardShouldPersistTaps="handled">
        <View className="mb-8 items-center">
          <View className="mb-3 h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-r from-brand to-brand-indigo">
            <Text className="text-2xl font-black text-white">C</Text>
          </View>
          <Text className="text-2xl font-extrabold tracking-tight text-slate-900">Welcome back</Text>
          <Text className="mt-1 text-sm font-medium text-slate-500">
            Log in to continue to your C-Familia portal.
          </Text>
        </View>

        <Banner kind="error" message={error} />

        <Field
          label="Email Address"
          value={email}
          onChangeText={setEmail}
          placeholder="you@example.com"
          keyboardType="email-address"
        />
        <Field
          label="Password"
          value={password}
          onChangeText={setPassword}
          secure
          placeholder="••••••••"
        />

        <Button title={busy ? 'Signing in…' : 'Sign In'} onPress={onSubmit} busy={busy} />

        <View className="mt-6 items-center">
          <Link href="/(auth)/register" asChild>
            <Text className="text-sm font-semibold text-brand">No account yet? Join the family</Text>
          </Link>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}
