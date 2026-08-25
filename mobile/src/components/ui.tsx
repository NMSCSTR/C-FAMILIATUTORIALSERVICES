import React from 'react';
import { ActivityIndicator, Pressable, Text, TextInput, View, ViewStyle } from 'react-native';

type FieldProps = {
  label: string;
  value: string;
  onChangeText: (text: string) => void;
  error?: string;
  secure?: boolean;
  placeholder?: string;
  keyboardType?: 'default' | 'email-address' | 'numeric' | 'phone-pad';
  autoCapitalize?: 'none' | 'sentences' | 'words';
};

export function Field({
  label,
  value,
  onChangeText,
  error,
  secure = false,
  placeholder,
  keyboardType = 'default',
  autoCapitalize = 'none',
}: FieldProps) {
  return (
    <View className="mb-4">
      <Text className="ml-1 mb-2 text-[11px] font-bold uppercase tracking-widest text-slate-500">
        {label}
      </Text>
      <TextInput
        className={`rounded-2xl border px-4 py-3.5 text-base text-slate-900 ${
          error ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-slate-50'
        }`}
        value={value}
        onChangeText={onChangeText}
        secureTextEntry={secure}
        placeholder={placeholder}
        placeholderTextColor="#94a3b8"
        keyboardType={keyboardType}
        autoCapitalize={autoCapitalize}
        autoCorrect={false}
      />
      {error ? <Text className="ml-1 mt-1 text-xs font-semibold text-rose-500">{error}</Text> : null}
    </View>
  );
}

type ButtonProps = {
  title: string;
  onPress: () => void;
  busy?: boolean;
  disabled?: boolean;
  variant?: 'primary' | 'outline';
  style?: ViewStyle;
};

export function Button({ title, onPress, busy, disabled, variant = 'primary', style }: ButtonProps) {
  const isPrimary = variant === 'primary';
  const isBlocked = Boolean(busy || disabled);

  return (
    <Pressable
      onPress={onPress}
      disabled={isBlocked}
      className={`items-center justify-center rounded-2xl py-4 ${
        isPrimary
          ? 'bg-brand active:bg-brand-dark'
          : 'border border-slate-300 bg-white active:bg-slate-100'
      } ${isBlocked ? 'opacity-60' : ''}`}
      style={style}
    >
      {busy ? (
        <ActivityIndicator color={isPrimary ? '#ffffff' : '#2563eb'} />
      ) : (
        <Text
          className={`text-sm font-bold tracking-wide ${
            isPrimary ? 'text-white' : 'text-brand'
          }`}
        >
          {title}
        </Text>
      )}
    </Pressable>
  );
}

export function Banner({ kind, message }: { kind: 'error' | 'success'; message: string }) {
  if (!message) return null;

  const isError = kind === 'error';

  return (
    <View
      className={`mb-5 rounded-2xl border p-4 ${
        isError ? 'border-rose-100 bg-rose-50' : 'border-emerald-100 bg-emerald-50'
      }`}
    >
      <Text className={`text-sm font-semibold ${isError ? 'text-rose-600' : 'text-emerald-700'}`}>
        {message}
      </Text>
    </View>
  );
}

export function StatusBadge({ status }: { status: string }) {
  const styles: Record<string, string> = {
    paid: 'bg-emerald-100 text-emerald-700',
    pending: 'bg-amber-100 text-amber-700',
    enrolled: 'bg-emerald-100 text-emerald-700',
    failed: 'bg-rose-100 text-rose-700',
    refund_requested: 'bg-rose-100 text-rose-700',
    refunded: 'bg-slate-200 text-slate-600',
    cancelled: 'bg-slate-200 text-slate-600',
  };

  const cls = styles[status] ?? 'bg-slate-200 text-slate-600';

  return (
    <View className={`rounded-lg px-2 py-0.5 ${cls}`}>
      <Text className="text-[10px] font-bold uppercase tracking-wide">
        {status.replace('_', ' ')}
      </Text>
    </View>
  );
}
