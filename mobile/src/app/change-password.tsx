import { Redirect, useRouter } from 'expo-router';
import React, { useState } from 'react';
import { Alert, KeyboardAvoidingView, Platform, ScrollView } from 'react-native';

import { Banner, Button, Field } from '@/components/ui';
import { apiErrorMessage, apiFieldErrors, api } from '@/lib/api';
import { useAuth } from '@/lib/auth';

export default function ChangePasswordScreen() {
  const { token } = useAuth();
  const router = useRouter();

  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmNewPassword, setConfirmNewPassword] = useState('');
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const [fields, setFields] = useState<Record<string, string>>({});

  if (!token) return <Redirect href="/(auth)/login" />;

  async function onSubmit() {
    if (newPassword !== confirmNewPassword) {
      setFields({ confirm_new_password: 'New passwords do not match.' });
      setError('Please fix the highlighted fields.');
      return;
    }

    setBusy(true);
    setError('');
    setFields({});

    try {
      await api.post('/profile/password', {
        current_password: currentPassword,
        new_password: newPassword,
        confirm_new_password: confirmNewPassword,
      });

      Alert.alert('Success', 'Password updated. Other devices have been logged out.');
      router.back();
    } catch (err) {
      setFields(apiFieldErrors(err));
      setError(apiErrorMessage(err));
    } finally {
      setBusy(false);
    }
  }

  return (
    <KeyboardAvoidingView
      className="flex-1 bg-slate-50"
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <ScrollView contentContainerClassName="px-5 pb-10 pt-6" keyboardShouldPersistTaps="handled">
        <Banner kind="error" message={error} />

        <Field
          label="Current Password"
          value={currentPassword}
          onChangeText={setCurrentPassword}
          secure
          error={fields.current_password}
        />
        <Field
          label="New Password"
          value={newPassword}
          onChangeText={setNewPassword}
          secure
          error={fields.new_password}
          placeholder="At least 8 characters"
        />
        <Field
          label="Confirm New Password"
          value={confirmNewPassword}
          onChangeText={setConfirmNewPassword}
          secure
          error={fields.confirm_new_password}
        />

        <Button title={busy ? 'Updating…' : 'Update Password'} onPress={onSubmit} busy={busy} />
      </ScrollView>
    </KeyboardAvoidingView>
  );
}
