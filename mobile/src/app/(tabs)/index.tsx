import { Link, useFocusEffect } from 'expo-router';
import React, { useCallback, useState } from 'react';
import { Pressable, RefreshControl, ScrollView, Text, View } from 'react-native';

import { Banner, Button, StatusBadge } from '@/components/ui';
import { api, apiErrorMessage } from '@/lib/api';
import { useAuth } from '@/lib/auth';
import type { DashboardPayload } from '@/lib/types';

function peso(amount: number | null | undefined): string {
  const value = Number(amount ?? 0);
  return `₱${value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function EnrollButton() {
  return (
    <Link href="/enroll" asChild>
      <Pressable className="mt-3 items-center rounded-2xl bg-brand py-3.5 active:bg-brand-dark">
        <Text className="text-sm font-bold text-white">Enroll now</Text>
      </Pressable>
    </Link>
  );
}

function ExamScores({
  scores,
}: {
  scores: { diagnostic_exam: number | null; preboard_exam: number | null; compre_exam: number | null };
}) {
  const rows = [
    { label: 'Diagnostic', value: scores.diagnostic_exam },
    { label: 'Pre-Board', value: scores.preboard_exam },
    { label: 'Comprehensive', value: scores.compre_exam },
  ];

  return (
    <View className="mb-4 rounded-3xl border border-slate-200 bg-white p-5">
      <Text className="text-[10px] font-black uppercase tracking-widest text-slate-400">
        Exam Results
      </Text>
      <View className="mt-2 gap-y-2">
        {rows.map((row) => (
          <View key={row.label} className="flex-row items-center justify-between">
            <Text className="text-sm font-semibold text-slate-600">{row.label}</Text>
            <Text
              className={`text-base font-extrabold ${
                row.value === null ? 'text-slate-300' : row.value >= 75 ? 'text-emerald-600' : 'text-rose-500'
              }`}
            >
              {row.value === null ? 'Not taken' : `${row.value}%`}
            </Text>
          </View>
        ))}
      </View>
    </View>
  );
}

export default function DashboardScreen() {
  const { user } = useAuth();
  const [data, setData] = useState<DashboardPayload | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState('');

  const load = useCallback(async (showRefresh: boolean) => {
    if (showRefresh) setRefreshing(true);
    setError('');

    try {
      const response = await api.get<{ data: DashboardPayload }>('/dashboard');
      setData(response.data.data);
    } catch (err) {
      setError(apiErrorMessage(err));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      void load(false);
    }, [load])
  );

  return (
    <ScrollView
      className="flex-1 bg-slate-50"
      contentContainerClassName="px-5 pb-8 pt-12"
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => load(true)} />}
    >
      <View className="mb-6">
        <Text className="text-[11px] font-bold uppercase tracking-widest text-slate-400">Dashboard</Text>
        <Text className="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">
          Hi, {user?.firstname ?? 'Student'} 👋
        </Text>
      </View>

      <Banner kind="error" message={error} />
      {loading && !data ? <Text className="text-center text-sm text-slate-500">Loading…</Text> : null}

      {data ? (
        <>
          <View className="mb-4 rounded-3xl border border-slate-200 bg-white p-5">
            <Text className="text-[10px] font-black uppercase tracking-widest text-slate-400">
              Enrollment
            </Text>
            {data.enrollment ? (
              <>
                <View className="mt-2 flex-row items-center justify-between">
                  <Text className="flex-1 text-base font-bold text-slate-900">
                    {data.enrollment.program_type}
                  </Text>
                  <StatusBadge status={data.enrollment.status} />
                </View>
                {data.enrollment.batch ? (
                  <Text className="mt-1 text-xs font-medium text-slate-500">
                    {data.enrollment.batch} · {data.enrollment.enrolled_at}
                  </Text>
                ) : null}
                {data.balance !== null ? (
                  <View className="mt-3 flex-row items-end justify-between">
                    <View>
                      <Text className="text-[10px] font-bold uppercase tracking-widest text-slate-400">Paid</Text>
                      <Text className="text-lg font-extrabold text-emerald-600">{peso(data.total_paid)}</Text>
                    </View>
                    <View className="items-end">
                      <Text className="text-[10px] font-bold uppercase tracking-widest text-slate-400">Balance</Text>
                      <Text
                        className={`text-lg font-extrabold ${
                          data.balance > 0 ? 'text-rose-500' : 'text-emerald-600'
                        }`}
                      >
                        {peso(data.balance)}
                      </Text>
                    </View>
                  </View>
                ) : null}
              </>
            ) : (
              <>
                <Text className="mt-2 text-sm font-medium text-slate-500">
                  You have not enrolled yet. Pick your program and batch to get started.
                </Text>
                <EnrollButton />
              </>
            )}
          </View>

          {data.exam_result ? <ExamScores scores={data.exam_result} /> : null}

          <View className="mb-4 rounded-3xl border border-slate-200 bg-white p-5">
            <View className="flex-row items-center justify-between">
              <Text className="text-[10px] font-black uppercase tracking-widest text-slate-400">
                Recent Payments
              </Text>
              <Link href="/(tabs)/payments" asChild>
                <Text className="text-xs font-bold text-brand">See all</Text>
              </Link>
            </View>
            {data.recent_payments.length === 0 ? (
              <Text className="mt-2 text-sm font-medium text-slate-500">No payments yet.</Text>
            ) : (
              data.recent_payments.slice(0, 3).map((payment) => (
                <View key={payment.id} className="mt-3 flex-row items-center justify-between">
                  <View className="flex-1">
                    <Text className="text-sm font-bold text-slate-900">{peso(payment.amount)}</Text>
                    <Text className="text-xs font-medium text-slate-400">
                      {payment.payment_method} · Ref {payment.reference_number ?? '—'}
                    </Text>
                  </View>
                  <StatusBadge status={payment.status} />
                </View>
              ))
            )}
          </View>

          <View className="rounded-3xl border border-slate-200 bg-white p-5">
            <View className="flex-row items-center justify-between">
              <Text className="text-[10px] font-black uppercase tracking-widest text-slate-400">
                Announcements
              </Text>
              <Link href="/announcements" asChild>
                <Text className="text-xs font-bold text-brand">See all</Text>
              </Link>
            </View>
            {data.announcements.length === 0 ? (
              <Text className="mt-2 text-sm font-medium text-slate-500">Nothing new right now.</Text>
            ) : (
              data.announcements.map((announcement) => (
                <View key={announcement.id} className="mt-3">
                  <Text className="text-sm font-bold text-slate-900">{announcement.title}</Text>
                  <Text numberOfLines={2} className="text-xs font-medium text-slate-500">
                    {announcement.message}
                  </Text>
                </View>
              ))
            )}
          </View>
        </>
      ) : null}
    </ScrollView>
  );
}
