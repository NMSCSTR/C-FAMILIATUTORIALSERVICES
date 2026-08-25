import { Redirect, useRouter } from 'expo-router';
import React, { useCallback, useEffect, useState } from 'react';
import { KeyboardAvoidingView, Platform, ScrollView, Text, View } from 'react-native';

import { Banner, Button, Card, SectionLabel, SelectField, StatusBadge } from '@/components/ui';
import { api, apiErrorMessage, apiFieldErrors } from '@/lib/api';
import { useAuth } from '@/lib/auth';
import type {
  EnrollmentOptions,
  EnrollmentPayload,
  ProgramOption,
} from '@/lib/types';

function peso(amount: number | null | undefined): string {
  const value = Number(amount ?? 0);
  return `₱${value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export default function EnrollScreen() {
  const { token } = useAuth();
  const router = useRouter();

  const [loading, setLoading] = useState(true);
  const [options, setOptions] = useState<EnrollmentOptions | null>(null);
  const [existing, setExisting] = useState<EnrollmentPayload['enrollment']>(null);

  const [program, setProgram] = useState<ProgramOption | ''>('');
  const [batch, setBatch] = useState<string>('');
  const [locationValue, setLocationValue] = useState<string>('');

  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const [fields, setFields] = useState<Record<string, string>>({});

  const load = useCallback(async () => {
    try {
      const [optionsRes, currentRes] = await Promise.all([
        api.get<{ data: EnrollmentOptions }>('/enrollment/options'),
        api.get<{ data: EnrollmentPayload }>('/enrollment'),
      ]);
      setOptions(optionsRes.data.data);
      setExisting(currentRes.data.data.enrollment);
    } catch (err) {
      setError(apiErrorMessage(err));
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    if (token) void load();
  }, [token, load]);

  if (!token) return <Redirect href="/(auth)/login" />;

  async function onSubmit() {
    setBusy(true);
    setError('');
    setFields({});

    try {
      await api.post('/enrollment', {
        program_type: program === '' ? '' : program.name,
        batch,
        enrolled_at: locationValue,
      });
      router.back();
    } catch (err) {
      setFields(apiFieldErrors(err));
      setError(apiErrorMessage(err));
    } finally {
      setBusy(false);
    }
  }

  const selectedFee = program === '' ? null : program.fee;

  return (
    <KeyboardAvoidingView
      className="flex-1 bg-slate-50"
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <ScrollView contentContainerClassName="px-5 pb-10 pt-14" keyboardShouldPersistTaps="handled">
        <Text className="mb-1 text-2xl font-extrabold tracking-tight text-slate-900">
          Enroll Now
        </Text>
        <Text className="mb-6 text-sm font-medium text-slate-500">
          Secure your slot. Pick your program, batch, and review location.
        </Text>

        <Banner kind="error" message={error} />

        {loading ? (
          <Text className="text-center text-sm text-slate-500">Loading options…</Text>
        ) : existing ? (
          <Card>
            <SectionLabel>Current application</SectionLabel>
            <View className="mt-2 flex-row items-center justify-between">
              <Text className="flex-1 text-base font-bold text-slate-900">
                {existing.program_type}
              </Text>
              <StatusBadge status={existing.status} />
            </View>
            <Text className="mt-3 text-sm font-medium text-slate-500">
              You already have an active application. The admin will review it shortly.
            </Text>
          </Card>
        ) : (
          options && (
            <>
              <SelectField<ProgramOption>
                label="Program"
                value={program}
                onChange={setProgram}
                error={fields.program_type}
                options={options.programs.map((p) => ({
                  value: p,
                  label: `${p.name} — ${peso(p.fee)}`,
                }))}
              />
              <SelectField<string>
                label="Batch"
                value={batch}
                onChange={setBatch}
                error={fields.batch}
                options={options.batches.map((b) => ({ value: b, label: b }))}
              />
              <SelectField<string>
                label="Review Location"
                value={locationValue}
                onChange={setLocationValue}
                error={fields.enrolled_at}
                options={options.locations}
              />

              <Card className="mt-2 mb-6">
                <SectionLabel>Total Fee</SectionLabel>
                <Text className="mt-1 text-3xl font-black tracking-tight text-brand">
                  {selectedFee === null ? '—' : peso(selectedFee)}
                </Text>
                {program !== '' && program.desc ? (
                  <Text className="mt-1 text-xs font-medium text-slate-500">{program.desc}</Text>
                ) : null}
              </Card>

              <Button
                title={busy ? 'Submitting…' : 'Confirm Enrollment'}
                onPress={onSubmit}
                busy={busy}
              />
            </>
          )
        )}
      </ScrollView>
    </KeyboardAvoidingView>
  );
}
