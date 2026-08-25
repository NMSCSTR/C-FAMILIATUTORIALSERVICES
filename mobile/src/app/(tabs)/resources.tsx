import { Redirect } from 'expo-router';
import React, { useCallback, useState } from 'react';
import { Alert, Pressable, RefreshControl, ScrollView, Text, View } from 'react-native';

import { Banner, Button, EmptyState, SectionLabel } from '@/components/ui';
import { api, apiErrorMessage } from '@/lib/api';
import { useAuth } from '@/lib/auth';
import { shareGatedFile } from '@/lib/files';
import type { Post } from '@/lib/types';

function fmtDate(value: string | null): string {
  return value ? value.slice(0, 10) : '';
}

export default function ResourcesScreen() {
  const { token } = useAuth();

  const [posts, setPosts] = useState<Post[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState('');
  const [busyId, setBusyId] = useState<number | null>(null);

  const load = useCallback(
    async (showRefresh: boolean) => {
      if (!token) return;
      if (showRefresh) setRefreshing(true);
      setError('');

      try {
        const response = await api.get<{ data: { posts: Post[] } }>('/posts');
        setPosts(response.data.data.posts);
      } catch (err) {
        setError(apiErrorMessage(err));
      } finally {
        setLoading(false);
        setRefreshing(false);
      }
    },
    [token]
  );

  React.useEffect(() => {
    void load(false);
  }, [load]);

  if (!token) return <Redirect href="/(auth)/login" />;

  async function openFile(post: Post) {
    if (!post.file_url) return;

    setBusyId(post.id);

    try {
      await shareGatedFile(post.file_url);
    } catch (err) {
      Alert.alert('Download failed', apiErrorMessage(err));
    } finally {
      setBusyId(null);
    }
  }

  return (
    <ScrollView
      className="flex-1 bg-slate-50"
      contentContainerClassName="px-5 pb-8 pt-14"
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => load(true)} />}
    >
      <Text className="mb-4 text-2xl font-extrabold tracking-tight text-slate-900">Resources</Text>

      <Banner kind="error" message={error} />
      {loading && posts.length === 0 ? (
        <Text className="text-center text-sm text-slate-500">Loading…</Text>
      ) : null}

      {!loading && posts.length === 0 && !error ? (
        <EmptyState glyph="📚" title="No materials yet" hint="Uploaded review files appear here." />
      ) : null}

      <View className="gap-y-3">
        {posts.map((post) => (
          <View key={post.id} className="rounded-3xl border border-slate-200 bg-white p-5">
            <Text className="text-base font-bold text-slate-900">{post.title}</Text>
            <SectionLabel>
              {post.author} · {fmtDate(post.created_at)}
            </SectionLabel>
            <Text numberOfLines={4} className="mt-2 text-sm font-medium text-slate-600">
              {post.content}
            </Text>

            {post.has_file ? (
              <Pressable
                disabled={busyId === post.id}
                onPress={() => void openFile(post)}
                className="mt-3 self-start rounded-xl border border-slate-200 px-3 py-1.5"
              >
                <Text className="text-xs font-bold text-brand">
                  {busyId === post.id ? 'Preparing…' : '⬇ Open file'}
                </Text>
              </Pressable>
            ) : null}
          </View>
        ))}
      </View>
    </ScrollView>
  );
}
