import { Image } from 'expo-image';
import { Link } from 'expo-router';
import { ScrollView, Text, View } from 'react-native';

import { Button } from '@/components/ui';
import { useAuth } from '@/lib/auth';

function Row({ label, value }: { label: string; value?: string | null }) {
  return (
    <View className="flex-row items-start justify-between">
      <Text className="w-28 text-xs font-bold uppercase tracking-wide text-slate-400">{label}</Text>
      <Text className="flex-1 text-right text-sm font-semibold text-slate-700">
        {value ? value : '—'}
      </Text>
    </View>
  );
}

export default function ProfileScreen() {
  const { user, logout } = useAuth();

  const initial = user?.firstname?.charAt(0)?.toUpperCase() ?? '?';

  return (
    <ScrollView className="flex-1 bg-slate-50" contentContainerClassName="px-5 pb-10 pt-14">
      <Text className="mb-5 text-[11px] font-bold uppercase tracking-widest text-slate-400">
        Profile
      </Text>

      <View className="rounded-3xl border border-slate-200 bg-white p-6">
        <View className="items-center">
          {user?.profile_pic_url ? (
            <Image source={{ uri: user.profile_pic_url }} className="h-20 w-20 rounded-full" />
          ) : (
            <View className="h-20 w-20 items-center justify-center rounded-full bg-blue-50">
              <Text className="text-3xl font-black text-brand">{initial}</Text>
            </View>
          )}
          <Text className="mt-3 text-xl font-extrabold text-slate-900">{user?.name}</Text>
          <Text className="text-sm font-medium text-slate-500">{user?.email}</Text>
        </View>

        <View className="mt-6 gap-y-3">
          <Row label="Cellphone" value={user?.cellphone_no} />
          <Row label="Address" value={user?.address} />
          <Row label="Guardian" value={user?.parents_name_guardian} />
          <Row label="Member since" value={user?.created_at?.slice(0, 10)} />
        </View>
      </View>

      <View className="mt-6 gap-y-3">
        <Link href="/profile-edit" asChild>
          <Button title="Edit profile" variant="outline" onPress={() => {}} />
        </Link>
        <Link href="/change-password" asChild>
          <Button title="Change password" variant="outline" onPress={() => {}} />
        </Link>
        <Button title="Log out" variant="outline" onPress={() => void logout()} />
      </View>
    </ScrollView>
  );
}
