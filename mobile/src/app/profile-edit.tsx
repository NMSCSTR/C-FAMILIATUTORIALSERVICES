import * as ImagePicker from 'expo-image-picker';
import { Redirect, useRouter } from 'expo-router';
import React, { useState } from 'react';
import { Alert, Image, KeyboardAvoidingView, Platform, Pressable, ScrollView, Text, View } from 'react-native';

import { Banner, Button, Field, SectionLabel } from '@/components/ui';
import { apiErrorMessage, apiFieldErrors, api } from '@/lib/api';
import { useAuth } from '@/lib/auth';

type Editable = {
  firstname: string;
  middlename: string;
  lastname: string;
  birthday: string;
  cellphone_no: string;
  address: string;
  parents_name_guardian: string;
  parents_phone_no: string;
  fb_messenger_account: string;
};

const MAX_AVATAR_BYTES = 5 * 1024 * 1024;

export default function ProfileEditScreen() {
  const { token, user, setUser } = useAuth();
  const router = useRouter();

  const [form, setForm] = useState<Editable>({
    firstname: user?.firstname ?? '',
    middlename: user?.middlename ?? '',
    lastname: user?.lastname ?? '',
    birthday: user?.birthday ?? '',
    cellphone_no: user?.cellphone_no ?? '',
    address: user?.address ?? '',
    parents_name_guardian: user?.parents_name_guardian ?? '',
    parents_phone_no: user?.parents_phone_no ?? '',
    fb_messenger_account: user?.fb_messenger_account ?? '',
  });
  const [avatar, setAvatar] = useState<{ uri: string; name: string; size: number; mimeType: string } | null>(null);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const [fields, setFields] = useState<Record<string, string>>({});

  if (!token || !user) return <Redirect href="/(auth)/login" />;

  function update(key: keyof Editable) {
    return (text: string) => setForm((prev) => ({ ...prev, [key]: text }));
  }

  async function pickAvatar() {
    const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();

    if (!permission.granted) {
      Alert.alert('Permission needed', 'Allow photo library access to change your avatar.');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ['images'],
      allowsEditing: true,
      aspect: [1, 1],
      quality: 0.8,
    });

    if (result.canceled || result.assets.length === 0) return;

    const asset = result.assets[0];

    if ((asset.fileSize ?? 0) > MAX_AVATAR_BYTES) {
      setFields({ profile_pic: 'File is too large. Maximum size is 5MB.' });
      return;
    }

    setFields({});
    setAvatar({
      uri: asset.uri,
      name: asset.fileName ?? 'avatar.jpg',
      size: asset.fileSize ?? 0,
      mimeType: asset.mimeType ?? 'image/jpeg',
    });
  }

  async function onSubmit() {
    setBusy(true);
    setError('');
    setFields({});

    const data = new FormData();
    Object.entries(form).forEach(([key, value]) => data.append(key, value.trim()));

    if (avatar) {
      data.append('profile_pic', {
        uri: avatar.uri,
        name: avatar.name,
        type: avatar.mimeType,
      } as unknown as Blob);
    }

    try {
      const response = await api.post<{ data: { user: typeof user } }>('/profile', data, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      if (response.data.data.user) {
        setUser(response.data.data.user);
      }

      Alert.alert('Saved', 'Profile updated successfully!');
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

        <View className="mb-6 items-center">
          <Pressable onPress={() => void pickAvatar()} className="items-center">
            {avatar ? (
              <Image source={{ uri: avatar.uri }} className="h-24 w-24 rounded-full" />
            ) : user.profile_pic_url ? (
              <Image source={{ uri: user.profile_pic_url }} className="h-24 w-24 rounded-full" />
            ) : (
              <View className="h-24 w-24 items-center justify-center rounded-full bg-blue-50">
                <Text className="text-3xl font-black text-brand">
                  {user.firstname.charAt(0).toUpperCase()}
                </Text>
              </View>
            )}
            <Text className="mt-2 text-xs font-bold text-brand">Change photo</Text>
          </Pressable>
          {fields.profile_pic ? (
            <Text className="mt-1 text-xs font-semibold text-rose-500">{fields.profile_pic}</Text>
          ) : null}
        </View>

        <Field label="First Name *" value={form.firstname} onChangeText={update('firstname')} error={fields.firstname} autoCapitalize="words" />
        <Field label="Middle Name" value={form.middlename} onChangeText={update('middlename')} error={fields.middlename} autoCapitalize="words" />
        <Field label="Last Name *" value={form.lastname} onChangeText={update('lastname')} error={fields.lastname} autoCapitalize="words" />
        <Field label="Birthday (YYYY-MM-DD)" value={form.birthday} onChangeText={update('birthday')} error={fields.birthday} placeholder="2000-01-31" />
        <Field label="Cellphone No." value={form.cellphone_no} onChangeText={update('cellphone_no')} error={fields.cellphone_no} keyboardType="phone-pad" />

        <SectionLabel>Guardian Details</SectionLabel>
        <View className="h-3" />
        <Field label="Parent / Guardian Name" value={form.parents_name_guardian} onChangeText={update('parents_name_guardian')} error={fields.parents_name_guardian} autoCapitalize="words" />
        <Field label="Guardian Contact No." value={form.parents_phone_no} onChangeText={update('parents_phone_no')} error={fields.parents_phone_no} keyboardType="phone-pad" />

        <Field label="Full Address" value={form.address} onChangeText={update('address')} error={fields.address} autoCapitalize="sentences" />
        <Field label="FB / Messenger Link" value={form.fb_messenger_account} onChangeText={update('fb_messenger_account')} error={fields.fb_messenger_account} placeholder="https://m.me/username" />

        <Button title={busy ? 'Saving…' : 'Save Changes'} onPress={onSubmit} busy={busy} />
        <Text className="mt-4 text-center text-xs font-medium text-slate-400">
          Email cannot be changed. Contact the admin for assistance.
        </Text>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}
