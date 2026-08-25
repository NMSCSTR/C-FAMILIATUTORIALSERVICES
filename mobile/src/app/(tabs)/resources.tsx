import { Text, View } from 'react-native';

export default function ResourcesScreen() {
  return (
    <View className="flex-1 items-center justify-center bg-slate-50 px-6">
      <Text className="text-lg font-bold text-slate-900">📚 Learning Resources</Text>
      <Text className="mt-2 text-center text-sm font-medium text-slate-500">
        Review materials arrive in the next update.
      </Text>
    </View>
  );
}
