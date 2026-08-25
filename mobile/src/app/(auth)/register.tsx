import { Link, useRouter } from 'expo-router';
import { KeyboardAvoidingView, Platform, ScrollView, Text, View } from 'react-native';
import { useState } from 'react';

import { Banner, Button, Field } from '@/components/ui';
import { apiErrorMessage, apiFieldErrors } from '@/lib/api';
import { useAuth } from '@/lib/auth';

type FormState = {
  firstname: string;
  middlename: string;
  lastname: string;
  email: string;
  cellphone_no: string;
  address: string;
  birthday: string;
  password: string;
  password_confirmation: string;
};

const EMPTY: FormState = {
  firstname: '',
  middlename: '',
  lastname: '',
  email: '',
  cellphone_no: '',
  address: '',
  birthday: '',
  password: '',
  password_confirmation: '',
};

export default function RegisterScreen() {
  const { register } = useAuth();
  const router = useRouter();
  const [form, setForm] = useState<FormState>(EMPTY);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

  function update(key: keyof FormState) {
    return (text: string) => setForm((prev) => ({ ...prev, [key]: text }));
  }

  async function onSubmit() {
    if (form.password !== form.password_confirmation) {
      setFieldErrors({ password_confirmation: 'Passwords do not match.' });
      setError('Please fix the highlighted fields.');
      return;
    }

    setBusy(true);
    setError('');
    setFieldErrors({});

    try {
      await register({
        firstname: form.firstname.trim(),
        middlename: form.middlename.trim() || undefined,
        lastname: form.lastname.trim(),
        email: form.email.trim().toLowerCase(),
        password: form.password,
        password_confirmation: form.password_confirmation,
        birthday: form.birthday.trim() || undefined,
        cellphone_no: form.cellphone_no.trim() || undefined,
        address: form.address.trim() || undefined,
      });
      router.replace('/(tabs)');
    } catch (err) {
      setFieldErrors(apiFieldErrors(err));
      setError(apiErrorMessage(err, 'Registration failed. Please try again.'));
    } finally {
      setBusy(false);
    }
  }

  return (
    <KeyboardAvoidingView
      className="flex-1 bg-slate-50"
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <ScrollView
        contentContainerClassName="flex-grow px-6 py-10"
        keyboardShouldPersistTaps="handled"
      >
        <View className="mb-6">
          <Text className="text-2xl font-extrabold tracking-tight text-slate-900">
            Create student account
          </Text>
          <Text className="mt-1 text-sm font-medium text-slate-500">
            Join the C-Familia review program.
          </Text>
        </View>

        <Banner kind="error" message={error} />

        <Field label="First Name *" value={form.firstname} onChangeText={update('firstname')} error={fieldErrors.firstname} autoCapitalize="words" />
        <Field label="Middle Name" value={form.middlename} onChangeText={update('middlename')} error={fieldErrors.middlename} autoCapitalize="words" />
        <Field label="Last Name *" value={form.lastname} onChangeText={update('lastname')} error={fieldErrors.lastname} autoCapitalize="words" />
        <Field label="Email Address *" value={form.email} onChangeText={update('email')} error={fieldErrors.email} keyboardType="email-address" placeholder="juan@example.com" />
        <Field label="Cellphone No." value={form.cellphone_no} onChangeText={update('cellphone_no')} error={fieldErrors.cellphone_no} keyboardType="phone-pad" placeholder="09XXXXXXXXX" />
        <Field label="Address" value={form.address} onChangeText={update('address')} error={fieldErrors.address} autoCapitalize="sentences" />
        <Field label="Birthday (YYYY-MM-DD)" value={form.birthday} onChangeText={update('birthday')} error={fieldErrors.birthday} placeholder="2000-01-31" />
        <Field label="Password *" value={form.password} onChangeText={update('password')} secure error={fieldErrors.password} placeholder="At least 8 characters" />
        <Field label="Confirm Password *" value={form.password_confirmation} onChangeText={update('password_confirmation')} secure error={fieldErrors.password_confirmation} />

        <Button title={busy ? 'Creating account…' : 'Register Now'} onPress={onSubmit} busy={busy} style={{ marginTop: 8 }} />

        <View className="mt-6 items-center pb-6">
          <Link href="/(auth)/login" asChild>
            <Text className="text-sm font-semibold text-brand">Already registered? Login here</Text>
          </Link>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}
