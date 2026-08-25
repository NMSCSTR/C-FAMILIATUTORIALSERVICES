import { Text, View } from 'react-native';

export default function PaymentsScreen() {
  return (
    <View className="flex-1 items-center justify-center bg-slate-50 px-6">
      <Text className="text-lg font-bold text-slate-900">💳 Payments</Text>
      <Text className="mt-2 text-center text-sm font-medium text-slate-500">
        Payment history and receipt submission arrive in the next update.
      </Text>
    </View>
  );
}
