import * as FileSystem from 'expo-file-system/legacy';
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

function safeFileName(url: string): string {
  const raw = url.split('?')[0].split('/').pop() ?? 'download';
  const clean = /^[A-Za-z0-9._-]+$/.test(raw) ? raw : 'download.bin';
  return 'gated-' + Date.now().toString(36) + '-' + clean;
}

export async function downloadGatedFile(url: string): Promise<string> {
  const headers = await authHeader();
  const base = FileSystem.cacheDirectory ?? FileSystem.documentDirectory;

  if (!base) {
    throw new Error('No storage directory available on this device.');
  }

  const result = await FileSystem.downloadAsync(url, base + safeFileName(url), { headers });

  if (result.status < 200 || result.status >= 300) {
    throw new Error('Download failed.');
  }

  return result.uri;
}

export async function shareGatedFile(url: string): Promise<void> {
  const uri = await downloadGatedFile(url);
  const available = await Sharing.isAvailableAsync();

  if (!available) {
    throw new Error('Sharing is not available on this device.');
  }

  await Sharing.shareAsync(uri, {
    mimeType: mimeFor(uri) ?? 'application/octet-stream',
    dialogTitle: 'Open file',
  });
}
