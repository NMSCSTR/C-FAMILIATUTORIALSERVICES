import { Redirect, useRouter } from 'expo-router';
import { Image } from 'expo-image';
import React, { useCallback, useReducer, useState } from 'react';
import { Alert, Modal, Pressable, RefreshControl, ScrollView, Text, View } from 'react-native';

import { Banner, Button, EmptyState, SectionLabel, StatusBadge } from '@/components/ui';
import { api, apiErrorMessage } from '@/lib/api';
import { useAuth } from '@/lib/auth';
import { authHeader, isImageUrl, shareGatedFile } from '@/lib/files';
import type { Payment } from '@/lib/types';

function peso(amount: number | null | undefined): string {
  const value = Number(amount ?? 0);
  return `₱${value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function fmtDate(value: string | null): string {
  if (!value) return '—';
  return value.slice(0, 10);
}

type Action =
  | { type: 'replace'; id: number; payment: Payment }
  | { type: 'setAll'; payments: Payment[] };

function reducer(state: Payment[], action: Action): Payment[] {
  switch (action.type) {
    case 'setAll':
      return action.payments;
    case 'replace':
      return state.map((p) => (p.id === action.id ? action.payment : p));
  }
}

export default function PaymentsScreen() {
  const { token } = useAuth();
  const router = useRouter();

  const [payments, dispatch] = useReducer(reducer, [] as Payment[]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState('');
  const [busyId, setBusyId] = useState<number | null>(null);
  const [preview, setPreview] = useState<{ url: string; headers: Record<string, string> } | null>(
    null
  );

  const load = useCallback(async (showRefresh: boolean) => {
    if (showRefresh) setRefreshing(true);
    setError('');

    try {
      const response = await api.get<{ data: { payments: Payment[] } }>('/payments');
      dispatch({ type: 'setAll', payments: response.data.data.payments });
    } catch (err) {
      setError(apiErrorMessage(err));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  React.useEffect(() => {
    if (token) void load(false);
  }, [token, load]);

  if (!token) return <Redirect href="/(auth)/login" />;

  function confirmCancel(payment: Payment) {
    Alert.alert('Cancel payment', 'This pending payment submission will be cancelled.', [
      { text: 'Keep', style: 'cancel' },
      { text: 'Cancel it', style: 'destructive', onPress: () => void runCancel(payment) },
    ]);
  }

  async function runCancel(payment: Payment) {
    setBusyId(payment.id);

    try {
      const response = await api.post<{ data: { payment: Payment } }>(
        `/payments/${payment.id}/cancel`
      );
      dispatch({ type: 'replace', id: payment.id, payment: response.data.data.payment });
    } catch (err) {
      Alert.alert('Cannot cancel', apiErrorMessage(err));
    } finally {
      setBusyId(null);
    }
  }

  function confirmRefund(payment: Payment) {
    Alert.alert(
      'Request refund',
      `Ask the admin to review a refund for ${peso(payment.amount)}? An admin will verify your request.`,
      [
        { text: 'Not now', style: 'cancel' },
        { text: 'Request refund', onPress: () => void runRefund(payment) },
      ]
    );
  }

  async function runRefund(payment: Payment) {
    setBusyId(payment.id);

    try {
      const response = await api.post<{ data: { payment: Payment } }>(
        `/payments/${payment.id}/refund-request`
      );
      dispatch({ type: 'replace', id: payment.id, payment: response.data.data.payment });
    } catch (err) {
      Alert.alert('Cannot request refund', apiErrorMessage(err));
    } finally {
      setBusyId(null);
    }
  }

  async function openReceipt(payment: Payment) {
    if (!payment.receipt_url) return;

    if (isImageUrl(payment.receipt_url)) {
      setPreview({ url: payment.receipt_url, headers: await authHeader() });
      return;
    }

    try {
      await shareGatedFile(payment.receipt_url);
    } catch (err) {
      Alert.alert('Download failed', apiErrorMessage(err));
    }
  }

  return (
    <View className="flex-1 bg-slate-50">
      <ScrollView
        contentContainerClassName="px-5 pb-8 pt-14"
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => load(true)} />}
      >
        <View className="mb-4 flex-row items-center justify-between">
          <Text className="text-2xl font-extrabold tracking-tight text-slate-900">Payments</Text>
          <Pressable
            onPress={() => router.push('/payment-new')}
            className="rounded-xl bg-brand px-3.5 py-2 active:bg-brand-dark"
          >
            <Text className="text-xs font-bold text-white">+ New</Text>
          </Pressable>
        </View>

        <Banner kind="error" message={error} />
        {loading && payments.length === 0 ? (
          <Text className="text-center text-sm text-slate-500">Loading…</Text>
        ) : null}

        {!loading && payments.length === 0 && !error ? (
          <EmptyState
            glyph="🧾"
            title="No payments yet"
            hint="Submit your first payment with your GCash receipt."
          />
        ) : null}

        <View className="gap-y-3">
          {payments.map((payment) => (
            <View key={payment.id} className="rounded-3xl border border-slate-200 bg-white p-4">
              <View className="flex-row items-start justify-between">
                <View className="flex-1">
                  <Text className="text-lg font-extrabold text-slate-900">
                    {peso(payment.amount)}
                  </Text>
                  <Text className="mt-0.5 text-xs font-medium text-slate-500">
                    {fmtDate(payment.payment_date)} · {payment.payment_type}
                  </Text>
                </View>
                <StatusBadge status={payment.status} />
              </View>

              <Text className="mt-2 text-xs font-semibold text-slate-400">
                Ref: {payment.reference_number ?? '—'} · {payment.payment_method ?? '—'}
              </Text>

              <View className="mt-3 flex-row items-center gap-x-2">
                {payment.receipt_url ? (
                  <Pressable
                    onPress={() => void openReceipt(payment)}
                    className="rounded-xl border border-slate-200 px-3 py-1.5"
                  >
                    <Text className="text-xs font-bold text-slate-600">View receipt</Text>
                  </Pressable>
                ) : null}

                {payment.status === 'pending' ? (
                  <Pressable
                    disabled={busyId === payment.id}
                    onPress={() => confirmCancel(payment)}
                    className="rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5"
                  >
                    <Text className="text-xs font-bold text-rose-600">
                      {busyId === payment.id ? 'Working…' : 'Cancel'}
                    </Text>
                  </Pressable>
                ) : null}

                {payment.status === 'paid' ? (
                  <Pressable
                    disabled={busyId === payment.id}
                    onPress={() => confirmRefund(payment)}
                    className="rounded-xl border border-amber-200 bg-amber-50 px-3 py-1.5"
                  >
                    <Text className="text-xs font-bold text-amber-700">
                      {busyId === payment.id ? 'Working…' : 'Request refund'}
                    </Text>
                  </Pressable>
                ) : null}
              </View>
            </View>
          ))}
        </View>
      </ScrollView>

      <Modal visible={preview !== null} transparent onRequestClose={() => setPreview(null)}>
        <Pressable
          className="flex-1 items-center justify-center bg-black/90 p-6"
          onPress={() => setPreview(null)}
        >
          {preview ? (
            <Image
              source={{ uri: preview.url, headers: preview.headers }}
              className="h-3/4 w-full rounded-2xl"
              contentFit="contain"
            />
          ) : null}
          <Text className="mt-4 text-sm font-semibold text-white/80">Tap anywhere to close</Text>
        </Pressable>
      </Modal>
    </View>
  );
}
