import { Redirect, useLocalSearchParams, useRouter } from 'expo-router';
import { ScrollView, Text, View } from 'react-native';

import { Card, SectionLabel, StatusBadge } from '@/components/ui';
import { useAuth } from '@/lib/auth';

export default function AnnouncementDetailScreen() {
  const { token } = useAuth();
  const router = useRouter();
  const params = useLocalSearchParams<{
    id: string;
    title?: string;
    message?: string;
    category?: string;
    created_at?: string;
  }>();

  if (!token) return <Redirect href="/(auth)/login" />;

  if (!params.title || !params.message) {
    router.back();
    return null;
  }

  return (
    <ScrollView className="flex-1 bg-slate-50" contentContainerClassName="px-5 pb-10 pt-6">
      <Card>
        <View className="flex-row items-start justify-between gap-x-3">
          <Text className="flex-1 text-xl font-extrabold leading-tight text-slate-900">
            {params.title}
          </Text>
          {params.category ? (
            <StatusBadge status={params.category.toLowerCase()} />
          ) : null}
        </View>
        <SectionLabel>{params.created_at?.slice(0, 16).replace('T', ' ')}</SectionLabel>
        <Text className="mt-4 text-base font-medium leading-relaxed text-slate-700">
          {params.message}
        </Text>
      </Card>
    </ScrollView>
  );
}
