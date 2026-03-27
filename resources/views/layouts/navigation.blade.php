<!-- Desktop Sidebar -->
<aside class="hidden sm:flex sm:flex-col sm:w-64 sm:fixed sm:inset-y-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
    <!-- Logo -->
    <div class="flex items-center h-16 px-6 border-b border-gray-200 dark:border-gray-700 shrink-0">
        <a href="{{ route('dashboard') }}" class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
            pipofo
        </a>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <svg class="shrink-0 h-5 w-5 me-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1" /></svg>
            {{ __('Dashboard') }}
        </x-nav-link>

        @if(auth()->user()->isRequester())
            <x-nav-link :href="route('requester.available-forms')" :active="request()->routeIs('requester.available-forms')">
                <svg class="shrink-0 h-5 w-5 me-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                {{ __('Available Forms') }}
            </x-nav-link>
            <x-nav-link :href="route('requester.my-forms')" :active="request()->routeIs('requester.my-forms')">
                <svg class="shrink-0 h-5 w-5 me-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8" /></svg>
                {{ __('My Forms') }}
            </x-nav-link>
            <x-nav-link :href="route('requester.pending-corrections')" :active="request()->routeIs('requester.pending-corrections')">
                <svg class="shrink-0 h-5 w-5 me-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ __('Pending Corrections') }}
            </x-nav-link>
        @endif

        @if(auth()->user()->isEmployee())
            <x-nav-link :href="route('employee.forms')" :active="request()->routeIs('employee.forms')">
                <svg class="shrink-0 h-5 w-5 me-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                {{ __('Forms') }}
            </x-nav-link>
            @if(auth()->user()->isManagerOrAdmin())
                <x-nav-link :href="route('form-templates.index')" :active="request()->routeIs('form-templates.*')">
                    <svg class="shrink-0 h-5 w-5 me-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1v-2zm0 8a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1v-2z" /></svg>
                    {{ __('Templates') }}
                </x-nav-link>
                <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    <svg class="shrink-0 h-5 w-5 me-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197" /></svg>
                    {{ __('Users') }}
                </x-nav-link>
            @endif
        @endif
    </nav>

    <!-- User Section -->
    <div class="border-t border-gray-200 dark:border-gray-700 p-3 space-y-1">
        <!-- Dark Mode Toggle -->
        <div
            x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
            class="flex items-center justify-between px-3 py-2 rounded-md text-sm text-gray-600 dark:text-gray-400"
        >
            <span class="flex items-center">
                <svg class="shrink-0 h-5 w-5 me-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.005 9.005 0 0012 21a9.005 9.005 0 008.354-5.646z" /></svg>
                {{ __('Dark Mode') }}
            </span>
            <button
                @click="dark = !dark; document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', dark ? 'dark' : 'light')"
                :class="dark ? 'bg-indigo-600' : 'bg-gray-200'"
                class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none"
                role="switch"
                :aria-checked="dark.toString()"
                aria-label="Toggle dark mode"
            >
                <span
                    :class="dark ? 'translate-x-5' : 'translate-x-0.5'"
                    class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform"
                ></span>
            </button>
        </div>

        <!-- Profile Link -->
        <a href="{{ route('profile.edit') }}" class="flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition duration-150 ease-in-out">
            <svg class="shrink-0 h-5 w-5 me-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            {{ __('Profile') }}
        </a>

        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition duration-150 ease-in-out">
                <svg class="shrink-0 h-5 w-5 me-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                {{ __('Log Out') }}
            </a>
        </form>

        <!-- User Info -->
        <div class="px-3 py-2 mt-2 border-t border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">{{ Auth::user()->name }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->role_label }}</div>
        </div>
    </div>
</aside>

<!-- Mobile Sidebar Overlay -->
<div x-show="sidebarOpen" x-cloak class="sm:hidden fixed inset-0 z-40">
    <!-- Backdrop -->
    <div
        class="fixed inset-0 bg-gray-600/75"
        @click="sidebarOpen = false"
    ></div>

    <!-- Mobile Sidebar -->
    <aside
        x-show="sidebarOpen"
        x-transition:enter="transition ease-in-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-gray-800 z-50 flex flex-col"
    >
        <!-- Logo & Close -->
        <div class="flex items-center justify-between h-14 px-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
            <a href="{{ route('dashboard') }}" class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                pipofo
            </a>
            <button
                @click="sidebarOpen = false"
                class="p-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                aria-label="Close sidebar"
            >
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if(auth()->user()->isRequester())
                <x-responsive-nav-link :href="route('requester.available-forms')" :active="request()->routeIs('requester.available-forms')">{{ __('Available Forms') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('requester.my-forms')" :active="request()->routeIs('requester.my-forms')">{{ __('My Forms') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('requester.pending-corrections')" :active="request()->routeIs('requester.pending-corrections')">{{ __('Pending Corrections') }}</x-responsive-nav-link>
            @endif

            @if(auth()->user()->isEmployee())
                <x-responsive-nav-link :href="route('employee.forms')" :active="request()->routeIs('employee.forms')">{{ __('Forms') }}</x-responsive-nav-link>
                @if(auth()->user()->isManagerOrAdmin())
                    <x-responsive-nav-link :href="route('form-templates.index')" :active="request()->routeIs('form-templates.*')">{{ __('Templates') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">{{ __('Users') }}</x-responsive-nav-link>
                @endif
            @endif
        </nav>

        <!-- User Section -->
        <div class="border-t border-gray-200 dark:border-gray-700 p-3 space-y-1">
            <!-- Dark Mode Toggle -->
            <div
                x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
                class="flex items-center justify-between px-4 py-2"
            >
                <span class="text-base font-medium text-gray-600 dark:text-gray-400">{{ __('Dark Mode') }}</span>
                <button
                    @click="dark = !dark; document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', dark ? 'dark' : 'light')"
                    :class="dark ? 'bg-indigo-600' : 'bg-gray-200'"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                    role="switch"
                    :aria-checked="dark.toString()"
                    aria-label="Toggle dark mode"
                >
                    <span
                        :class="dark ? 'translate-x-6' : 'translate-x-1'"
                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                    ></span>
                </button>
            </div>

            <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-responsive-nav-link>
            </form>

            <div class="px-4 py-2 mt-2 border-t border-gray-200 dark:border-gray-600">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
        </div>
    </aside>
</div>
