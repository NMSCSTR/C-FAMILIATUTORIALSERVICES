import { File, Paths } from 'expo-file-system';
import * as Sharing from 'expo-sharing';
import * as SecureStore from 'expo-secure-store';

const MIME_BY_EXT: Record<string, string> = {
  png: 'image/png',
  jpg: 'image/jpeg',
  jpeg: 'image/jpeg',
  gif: 'image/gif',
  webp: 'image/webp',
  pdf: 'application/pdf',
  doc: 'application/msword',
  docx: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  zip: 'application/zip',
  rar: 'application/vnd.rar',
};

export function isImageUrl(url: string): boolean {
  return /\.(png|jpe?g|gif|webp)$/i.test(url.split('?')[0] ?? '');
}

function mimeFor(name: string): string | undefined {
  const ext = name.split('.').pop()?.toLowerCase() ?? '';
  return MIME_BY_EXT[ext];
}

export async function authHeader(): Promise<Record<string, string>> {
  const token = await SecureStore.getItemAsync('cfts_token');
  return token ? { Authorization: `Bearer ${token}` } : {};
}

export async function downloadGatedFile(url: string): Promise<File> {
  const headers = await authHeader();

  return File.downloadFileAsync(url, Paths.cache, {
    idempotent: true,
    headers,
  });
}

export async function shareGatedFile(url: string): Promise<void> {
  const file = await downloadGatedFile(url);
  const available = await Sharing.isAvailableAsync();

  if (!available) {
    throw new Error('Sharing is not available on this device.');
  }

  await Sharing.shareAsync(file.uri, {
    mimeType: mimeFor(file.name) ?? 'application/octet-stream',
    dialogTitle: 'Open file',
  });
}
