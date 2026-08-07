<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - EverythingEasy ServiceOS</title>

    <!-- Meta Tags for SEO and PWA -->
    <meta name="description" content="EverythingEasy ServiceOS - Premium Enterprise SaaS platform for Restaurants, Cafes, and Hotels. Manage operations, table systems, KDS, payments, and staff seamlessly.">
    <meta name="theme-color" content="#141C27">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">

    <!-- Styles and Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-bg text-ink font-sans antialiased">
    <!-- Main Alpine-driven App Container -->
    <div x-data="{ 
        sidebarOpen: false, 
        activeBranch: 'Restaurant Branch',
        quickActionsOpen: false,
        toggleQuickActions() { this.quickActionsOpen = !this.quickActionsOpen }
    }" 
    class="flex h-screen overflow-hidden bg-bg">
        
        <!-- Sidebar Component -->
        <x-sidebar />

        <!-- Content Area -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Header Component -->
            <x-header />

            <!-- Main Content Scroll Container -->
            <main class="flex-1 overflow-y-auto p-4 md:p-8 focus:outline-none">
                <div class="max-w-[1600px] mx-auto space-y-6 md:space-y-8 pb-12">
                    @yield('content')
                </div>
            </main>
        </div>

        <!-- Quick Actions Modal Overlay Component -->
        <x-quick-actions />
    </div>
</body>
</html>
