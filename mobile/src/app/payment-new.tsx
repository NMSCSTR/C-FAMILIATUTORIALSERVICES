import { Redirect, useRouter } from 'expo-router';
import { getDocumentAsync } from 'expo-document-picker';
import React, { useState } from 'react';
import { Alert, KeyboardAvoidingView, Platform, Pressable, ScrollView, Text, View } from 'react-native';

import { Banner, Button, Field, SectionLabel, SelectField } from '@/components/ui';
import { apiErrorMessage, apiFieldErrors, api } from '@/lib/api';
import { useAuth } from '@/lib/auth';

type PaymentType = 'full' | 'installment' | 'other';

const MAX_RECEIPT_BYTES = 5 * 1024 * 1024;

export default function NewPaymentScreen() {
  const { token } = useAuth();
  const router = useRouter();

  const [amount, setAmount] = useState('');
  const [referenceNumber, setReferenceNumber] = useState('');
  const [paymentType, setPaymentType] = useState<PaymentType>('installment');
  const [paymentMethod, setPaymentMethod] = useState('');
  const [receipt, setReceipt] = useState<{ uri: string; name: string; size: number } | null>(null);

  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const [fields, setFields] = useState<Record<string, string>>({});

  if (!token) return <Redirect href="/(auth)/login" />;

  async function pickReceipt() {
    const result = await getDocumentAsync({
      type: ['image/png', 'image/jpeg', 'image/webp', 'application/pdf'],
      copyToCacheDirectory: true,
    });

    if (result.canceled || result.assets.length === 0) return;

    const asset = result.assets[0];

    if ((asset.size ?? 0) > MAX_RECEIPT_BYTES) {
      setFields({ receipt: 'File is too large. Maximum size is 5MB.' });
      return;
    }

    setFields({});
    setReceipt({ uri: asset.uri, name: asset.name ?? 'receipt', size: asset.size ?? 0 });
  }

  async function onSubmit() {
    setBusy(true);
    setError('');
    setFields({});

    const form = new FormData();
    form.append('amount', amount.trim());
    form.append('reference_number', referenceNumber.trim());
    form.append('payment_type', paymentType);
    form.append('payment_method', paymentMethod.trim());

    if (receipt) {
      form.append('receipt', {
        uri: receipt.uri,
        name: receipt.name,
        type: receipt.name.toLowerCase().endsWith('.pdf') ? 'application/pdf' : 'image/jpeg',
      } as unknown as Blob);
    }

    try {
      await api.post('/payments', form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      Alert.alert('Submitted', 'Your payment is pending verification.');
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
      <ScrollView contentContainerClassName="px-5 pb-10 pt-14" keyboardShouldPersistTaps="handled">
        <Text className="mb-1 text-2xl font-extrabold tracking-tight text-slate-900">
          Submit Payment
        </Text>
        <Text className="mb-6 text-sm font-medium text-slate-500">
          GCash / Maya / bank transfer. Our finance team reviews within 24 hours.
        </Text>

        <Banner kind="error" message={error} />

        <Field
          label="Amount (PHP)"
          value={amount}
          onChangeText={setAmount}
          error={fields.amount}
          keyboardType="numeric"
          placeholder="0.00"
        />
        <Field
          label="Reference Number"
          value={referenceNumber}
          onChangeText={setReferenceNumber}
          error={fields.reference_number}
          placeholder="Transaction ID from your receipt"
        />
        <SelectField<PaymentType>
          label="Payment Category"
          value={paymentType}
          onChange={setPaymentType}
          error={fields.payment_type}
          options={[
            { value: 'full', label: 'Full Program Fee' },
            { value: 'installment', label: 'Installment Payment' },
            { value: 'other', label: 'Other Fees' },
          ]}
        />
        <Field
          label="Method / Platform"
          value={paymentMethod}
          onChangeText={setPaymentMethod}
          error={fields.payment_method}
          placeholder="e.g. GCash, BPI, PayMaya"
        />

        <View className="mb-5">
          <SectionLabel>Proof of Transaction (required)</SectionLabel>
          <Pressable
            onPress={() => void pickReceipt()}
            className={`mt-2 items-center rounded-2xl border-2 border-dashed p-6 ${
              fields.receipt ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white'
            }`}
          >
            <Text className="text-2xl">🧾</Text>
            <Text className="mt-1 text-sm font-bold text-brand">
              {receipt ? receipt.name : 'Choose image or PDF'}
            </Text>
            {receipt ? (
              <Text className="text-xs font-medium text-slate-400">
                {(receipt.size / 1048576).toFixed(2)} MB · tap to replace
              </Text>
            ) : (
              <Text className="text-xs font-medium text-slate-400">JPG, PNG, WEBP or PDF · max 5MB</Text>
            )}
          </Pressable>
          {fields.receipt ? (
            <Text className="ml-1 mt-1 text-xs font-semibold text-rose-500">{fields.receipt}</Text>
          ) : null}
        </View>

        <Button
          title={busy ? 'Submitting…' : 'Submit for Verification'}
          onPress={onSubmit}
          busy={busy}
        />
      </ScrollView>
    </KeyboardAvoidingView>
  );
}
