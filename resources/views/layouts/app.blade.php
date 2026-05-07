<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1d4ed8">
    <meta name="description" content="Aplikasi Manajemen RT - BDI Apps">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="BDI Apps">

    <title>{{ $title ?? 'BDI Apps' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Firebase config untuk JS SDK --}}
    <script>
        window._firebase = {
            apiKey:            "{{ config('firebase.credentials.file') ? '' : env('FIREBASE_API_KEY') }}",
            authDomain:        "{{ env('FIREBASE_AUTH_DOMAIN') }}",
            projectId:         "{{ env('FIREBASE_PROJECT_ID') }}",
            storageBucket:     "{{ env('FIREBASE_STORAGE_BUCKET') }}",
            messagingSenderId: "{{ env('FIREBASE_MESSAGING_SENDER_ID') }}",
            appId:             "{{ env('FIREBASE_APP_ID') }}",
            vapidKey:          "{{ env('FIREBASE_VAPID_KEY') }}",
        };
    </script>
</head>
<body class="min-h-screen bg-gray-50">

    {{ $slot }}

    @livewireScripts
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
</body>
</html>
