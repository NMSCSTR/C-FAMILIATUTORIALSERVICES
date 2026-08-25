# C-Familia Student App

React Native (Expo SDK 57) + NativeWind v4 client for C-Familia Tutorial Services.
Consumes the student REST API under `/api` of the main PHP project.

## Setup

```bash
cd mobile
npm install
cp .env.example .env   # then set EXPO_PUBLIC_API_BASE_URL
npm start
```

## API base URL

| Environment | Value |
|---|---|
| Android emulator → local XAMPP | `http://10.0.2.2/cfts/api` |
| Physical device via Expo Go | `http://<your-PC-LAN-IP>/cfts/api` |
| Production | `https://c-familia.online/api` |

Set `EXPO_PUBLIC_API_BASE_URL` in `.env` (never committed).

## Structure

```
src/app/(auth)/    login, register (redirects to tabs when a token exists)
src/app/(tabs)/    home dashboard, payments*, resources*, profile
src/lib/           axios client (Bearer interceptor), auth context (SecureStore), types
```

\* placeholder screens — feature screens land in the next milestone.
