
<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-[72px] items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-8">
                <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z M3.3 7.7 12 12l8.7-4.3M12 21v-9"/>
                        </svg>
                    </div>
                    <div>
                        <span class="block text-lg font-extrabold tracking-tight text-slate-900">Inventory<span class="text-blue-600">Pro</span></span>
                        <span class="hidden text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400 sm:block">Management system</span>
                    </div>
                </a>

                <div class="hidden items-center gap-2 md:flex">
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition
                       {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z"/>
                        </svg>
                        Dashboard
                    </a>

                    <a href="{{ route('products.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition
                       {{ request()->routeIs('products.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M20 7 12 3 4 7m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Products
                    </a>
                </div>
            </div>

            <div class="hidden items-center gap-3 md:flex">
                <div class="hidden text-right lg:block">
                    <p class="text-sm font-bold text-slate-800">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-slate-400">{{ Auth::user()->email }}</p>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 transition hover:border-blue-200 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-sm font-extrabold text-blue-700">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="text-sm font-bold text-slate-800">{{ Auth::user()->name }}</p>
                            <p class="mt-1 break-all text-xs text-slate-500">{{ Auth::user()->email }}</p>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('My Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-start text-sm leading-5 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus:bg-slate-100 focus:outline-none">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <button @click="open = !open" type="button"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 p-2.5 text-slate-600 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/30 md:hidden"
                aria-label="Toggle navigation">
                <svg x-show="!open" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 6 12 12M18 6 6 18"/>
                </svg>
            </button>
        </div>
    </div>

    <div x-show="open" x-cloak x-transition class="border-t border-slate-100 bg-white md:hidden">
        <div class="space-y-1 px-4 py-4">
            <a href="{{ route('dashboard') }}"
               class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">
                Dashboard
            </a>
            <a href="{{ route('products.index') }}"
               class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('products.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">
                Products
            </a>
            <a href="{{ route('profile.edit') }}"
               class="flex items-center rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('profile.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">
                My Profile
            </a>
        </div>

        <div class="border-t border-slate-100 px-4 py-4">
            <p class="font-semibold text-slate-800">{{ Auth::user()->name }}</p>
            <p class="mt-1 break-all text-sm text-slate-500">{{ Auth::user()->email }}</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-left text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Log Out
                </button>
            </form>
        </div>
    </div>
</nav>
