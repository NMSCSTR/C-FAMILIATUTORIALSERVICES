import React, { useState } from 'react';
import { ActivityIndicator, Modal, Pressable, Text, TextInput, View, ViewStyle } from 'react-native';

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
        multiline={false}
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
        <Text className={`text-sm font-bold tracking-wide ${isPrimary ? 'text-white' : 'text-brand'}`}>
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

const BADGE_STYLES: Record<string, string> = {
  paid: 'bg-emerald-100 text-emerald-700',
  pending: 'bg-amber-100 text-amber-700',
  enrolled: 'bg-emerald-100 text-emerald-700',
  failed: 'bg-rose-100 text-rose-700',
  refund_requested: 'bg-rose-100 text-rose-700',
  refunded: 'bg-slate-200 text-slate-600',
  cancelled: 'bg-slate-200 text-slate-600',
};

export function StatusBadge({ status }: { status: string }) {
  const cls = BADGE_STYLES[status] ?? 'bg-slate-200 text-slate-600';

  return (
    <View className={`rounded-lg px-2 py-0.5 ${cls}`}>
      <Text className="text-[10px] font-bold uppercase tracking-wide">
        {status.replace(/_/g, ' ')}
      </Text>
    </View>
  );
}

export function Card({ children, className = '' }: { children: React.ReactNode; className?: string }) {
  return (
    <View className={`rounded-3xl border border-slate-200 bg-white p-5 ${className}`}>
      {children}
    </View>
  );
}

export function SectionLabel({ children }: { children: React.ReactNode }) {
  return (
    <Text className="text-[10px] font-black uppercase tracking-widest text-slate-400">
      {children}
    </Text>
  );
}

export function EmptyState({ glyph, title, hint }: { glyph: string; title: string; hint?: string }) {
  return (
    <View className="items-center py-10">
      <Text className="text-4xl">{glyph}</Text>
      <Text className="mt-3 text-base font-bold text-slate-800">{title}</Text>
      {hint ? (
        <Text className="mt-1 text-center text-sm font-medium text-slate-500">{hint}</Text>
      ) : null}
    </View>
  );
}

type PickerProps<T> = {
  label: string;
  value: T | '';
  options: { value: T; label: string }[];
  onChange: (value: T) => void;
  error?: string;
};

export function SelectField<T>({ label, value, options, onChange, error }: PickerProps<T>) {
  const [open, setOpen] = useState(false);
  const selected = options.find((option) => option.value === value);

  return (
    <View className="mb-4">
      <Text className="ml-1 mb-2 text-[11px] font-bold uppercase tracking-widest text-slate-500">
        {label}
      </Text>
      <Pressable
        onPress={() => setOpen(true)}
        className={`rounded-2xl border px-4 py-3.5 ${
          error ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-slate-50'
        }`}
      >
        <Text className={`text-base ${selected ? 'text-slate-900' : 'text-slate-400'}`}>
          {selected ? selected.label : 'Select…'}
        </Text>
      </Pressable>
      {error ? <Text className="ml-1 mt-1 text-xs font-semibold text-rose-500">{error}</Text> : null}

      <Modal visible={open} transparent animationType="fade" onRequestClose={() => setOpen(false)}>
        <Pressable className="flex-1 justify-end bg-black/40" onPress={() => setOpen(false)}>
          <Pressable className="rounded-t-3xl bg-white px-5 pb-10 pt-5" onPress={() => {}}>
            <Text className="mb-3 text-sm font-bold uppercase tracking-widest text-slate-400">
              {label}
            </Text>
            {options.map((option, index) => (
              <Pressable
                key={index}
                onPress={() => {
                  onChange(option.value);
                  setOpen(false);
                }}
                className={`rounded-xl px-4 py-3.5 ${option.value === value ? 'bg-blue-50' : ''}`}
              >
                <Text
                  className={`text-base ${
                    option.value === value ? 'font-bold text-brand' : 'text-slate-700'
                  }`}
                >
                  {option.label}
                </Text>
              </Pressable>
            ))}
          </Pressable>
        </Pressable>
      </Modal>
    </View>
  );
}
