import { Redirect, useRouter } from 'expo-router';
import React, { useCallback, useState } from 'react';
import { Pressable, RefreshControl, ScrollView, Text, View } from 'react-native';

import { Banner, EmptyState, SectionLabel, StatusBadge } from '@/components/ui';
import { api, apiErrorMessage } from '@/lib/api';
import { useAuth } from '@/lib/auth';
import type { Announcement, PaginationMeta } from '@/lib/types';

const PER_PAGE = 10;

export default function AnnouncementsScreen() {
  const { token } = useAuth();
  const router = useRouter();

  const [items, setItems] = useState<Announcement[]>([]);
  const [meta, setMeta] = useState<PaginationMeta | null>(null);
  const [loadingMore, setLoadingMore] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState('');

  const loadPage = useCallback(
    async (page: number, showRefresh: boolean) => {
      if (!token) return;
      if (showRefresh) setRefreshing(true);
      setError('');

      try {
        const response = await api.get<{
          data: { announcements: Announcement[]; meta: PaginationMeta };
        }>('/announcements', { params: { page, per_page: PER_PAGE } });

        const payload = response.data.data;
        setMeta(payload.meta);
        setItems((prev) =>
          page === 1 ? payload.announcements : [...prev, ...payload.announcements]
        );
      } catch (err) {
        setError(apiErrorMessage(err));
      } finally {
        setRefreshing(false);
        setLoadingMore(false);
      }
    },
    [token]
  );

  React.useEffect(() => {
    void loadPage(1, false);
  }, [loadPage]);

  if (!token) return <Redirect href="/(auth)/login" />;

  function openDetail(announcement: Announcement) {
    router.push({
      pathname: '/announcements/[id]',
      params: {
        id: String(announcement.id),
        title: announcement.title,
        message: announcement.message,
        category: announcement.category,
        created_at: announcement.created_at ?? '',
      },
    });
  }

  const hasMore = meta !== null && meta.page < meta.total_pages;

  return (
    <View className="flex-1 bg-slate-50">
      <ScrollView
        contentContainerClassName="px-5 pb-8 pt-14"
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => loadPage(1, true)} />}
      >
        <Text className="mb-4 text-2xl font-extrabold tracking-tight text-slate-900">
          Announcements
        </Text>

        <Banner kind="error" message={error} />

        {!refreshing && items.length === 0 && !error ? (
          <EmptyState glyph="📣" title="Nothing yet" hint="Student announcements appear here." />
        ) : null}

        <View className="gap-y-3">
          {items.map((announcement) => (
            <Pressable
              key={announcement.id}
              onPress={() => openDetail(announcement)}
              className="rounded-3xl border border-slate-200 bg-white p-5 active:bg-slate-100"
            >
              <View className="flex-row items-center justify-between">
                <Text className="flex-1 text-base font-bold text-slate-900">
                  {announcement.title}
                </Text>
                <StatusBadge status={announcement.category.toLowerCase()} />
              </View>
              <Text numberOfLines={2} className="mt-2 text-sm font-medium text-slate-500">
                {announcement.message}
              </Text>
              <SectionLabel>{announcement.created_at?.slice(0, 16).replace('T', ' ')}</SectionLabel>
            </Pressable>
          ))}
        </View>

        {hasMore ? (
          <Pressable
            disabled={loadingMore}
            onPress={() => {
              setLoadingMore(true);
              void loadPage((meta?.page ?? 1) + 1, false);
            }}
            className="mt-4 items-center rounded-2xl border border-slate-200 bg-white py-3.5"
          >
            <Text className="text-sm font-bold text-brand">
              {loadingMore ? 'Loading…' : 'Load more'}
            </Text>
          </Pressable>
        ) : null}
      </ScrollView>
    </View>
  );
}
