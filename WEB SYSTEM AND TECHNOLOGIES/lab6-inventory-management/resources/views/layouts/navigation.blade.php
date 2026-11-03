<nav x-data="{ open: false }" class="inventory-nav">

    <div class="nav-container">

        {{-- Brand --}}
        <a href="{{ route('dashboard') }}" class="nav-brand">
            <span class="brand-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5z"/>
                    <path d="M3 7.5 12 12l9-4.5M12 12v9M7.5 5.25l9 4.5"/>
                </svg>
            </span>

            <span class="brand-copy">
                <strong>StockPilot</strong>
                <small>INVENTORY MANAGEMENT</small>
            </span>
        </a>

        {{-- Desktop navigation --}}
        <div class="nav-links">

            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('products.index') }}"
               class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 3 9 4.5v9L12 21l-9-4.5v-9z"/>
                    <path d="m3 7.5 9 4.5 9-4.5M12 12v9"/>
                </svg>
                <span>Products</span>
            </a>

            <a href="{{ route('transactions.index') }}"
               class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 5h16v14H4z"/>
                    <path d="M8 9h8M8 13h8M8 17h4"/>
                </svg>
                <span>Transactions</span>
            </a>

        </div>

        {{-- Desktop notifications --}}
        <a href="{{ route('notifications.index') }}"
           class="notification-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}"
           aria-label="Notifications">

            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                <path d="M10 21h4"/>
            </svg>

            <span>Notifications</span>

            @if(Auth::user()->unreadNotifications()->count() > 0)
                <span class="notification-count">
                    {{ Auth::user()->unreadNotifications()->count() }}
                </span>
            @endif
        </a>

        {{-- User menu --}}
        <div class="nav-user-area">

            <x-dropdown align="right" width="48">

                <x-slot name="trigger">
                    <button type="button" class="user-trigger">

                        <span class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>

                        <span class="user-details">
                            <strong>{{ Auth::user()->name }}</strong>
                            <small>Account settings</small>
                        </span>

                        <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z"
                                  clip-rule="evenodd"/>
                        </svg>

                    </button>
                </x-slot>

                <x-slot name="content">

                    <div class="user-menu-heading">
                        <strong>{{ Auth::user()->name }}</strong>
                        <small>{{ Auth::user()->email }}</small>
                    </div>

                    <x-dropdown-link :href="route('profile.edit')">
                        My Profile
                    </x-dropdown-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit" class="logout-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 17l5-5-5-5M15 12H3"/>
                                <path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/>
                            </svg>
                            Sign out
                        </button>
                    </form>

                </x-slot>

            </x-dropdown>

        </div>

        {{-- Mobile menu button --}}
        <button type="button"
                class="mobile-menu-button"
                @click="open = !open"
                :aria-expanded="open.toString()"
                aria-label="Toggle navigation menu">

            <svg x-show="!open" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>

            <svg x-show="open" x-cloak viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/>
            </svg>

        </button>

    </div>

    {{-- Mobile navigation --}}
    <div x-show="open" x-cloak x-transition class="mobile-nav">

        <a href="{{ route('dashboard') }}"
           class="mobile-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            Dashboard
        </a>

        <a href="{{ route('products.index') }}"
           class="mobile-nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
            Products
        </a>

        <a href="{{ route('transactions.index') }}"
           class="mobile-nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
            Transactions
        </a>

        {{-- Mobile notifications --}}
        <a href="{{ route('notifications.index') }}"
           class="mobile-nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">

            Notifications

            @if(Auth::user()->unreadNotifications()->count() > 0)
                <span class="notification-count">
                    {{ Auth::user()->unreadNotifications()->count() }}
                </span>
            @endif

        </a>

        <div class="mobile-user">
            <strong>{{ Auth::user()->name }}</strong>
            <small>{{ Auth::user()->email }}</small>
        </div>

        <a href="{{ route('profile.edit') }}"
           class="mobile-nav-link">
            My Profile
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="mobile-logout">
                Sign out
            </button>
        </form>

    </div>

</nav>

<style>
    [x-cloak] {
        display: none !important;
    }

    .inventory-nav {
        position: relative;
        z-index: 40;
        background: #fff;
        border-bottom: 1px solid #e7edf5;
        font-family: Inter, ui-sans-serif, system-ui, -apple-system,
                     BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .nav-container {
        max-width: 1440px;
        min-height: 76px;
        margin: auto;
        padding: 0 32px;
        display: flex;
        align-items: center;
        gap: 48px;
    }

    .nav-brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        color: #12213a;
        text-decoration: none;
        flex-shrink: 0;
    }

    .brand-icon {
        width: 43px;
        height: 43px;
        display: grid;
        place-items: center;
        color: white;
        background: linear-gradient(145deg, #3979f6, #2455d6);
        border-radius: 13px;
        box-shadow: 0 5px 12px #2455d625;
    }

    .brand-icon svg {
        width: 25px;
        height: 25px;
    }

    .brand-copy {
        display: grid;
        gap: 3px;
    }

    .brand-copy strong {
        font-size: 17px;
        font-weight: 800;
        letter-spacing: -.5px;
    }

    .brand-copy small {
        color: #8a96a9;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 1.3px;
    }

    .nav-links {
        display: flex;
        align-items: stretch;
        align-self: stretch;
        gap: 8px;
        flex: 1;
    }

    .nav-item {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 0 18px;
        color: #66748a;
        font-size: 13px;
        font-weight: 650;
        text-decoration: none;
        transition: color .18s ease, background .18s ease;
    }

    .nav-item svg {
        width: 19px;
        height: 19px;
    }

    .nav-item:hover,
    .nav-item.active {
        color: #245bd7;
        background: #f7f9ff;
    }

    .nav-item.active::after {
        content: "";
        position: absolute;
        bottom: -1px;
        left: 18px;
        right: 18px;
        height: 3px;
        background: #3475f6;
        border-radius: 5px 5px 0 0;
    }

    /* Desktop notification link */
    .notification-link {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 10px 14px;
        color: #66748a;
        font-size: 13px;
        font-weight: 650;
        text-decoration: none;
        border-radius: 10px;
        transition: color .18s ease, background .18s ease;
        flex-shrink: 0;
    }

    .notification-link svg {
        width: 20px;
        height: 20px;
    }

    .notification-link:hover,
    .notification-link.active {
        color: #245bd7;
        background: #f7f9ff;
    }

    /* Unread notification counter */
    .notification-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 19px;
        height: 19px;
        padding: 0 5px;
        border-radius: 20px;
        background: #e5484d;
        color: white;
        font-size: 10px;
        font-weight: 800;
        line-height: 1;
    }

    .nav-user-area {
        margin-left: auto;
    }

    .user-trigger {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 7px 10px;
        border: 1px solid transparent;
        border-radius: 12px;
        background: white;
        cursor: pointer;
        transition: background .2s, border-color .2s;
    }

    .user-trigger:hover {
        background: #f8faff;
        border-color: #e8edf6;
    }

    .user-avatar {
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        color: #245bd7;
        background: #eaf1ff;
        font-size: 15px;
        font-weight: 800;
    }

    .user-details {
        display: grid;
        gap: 3px;
        text-align: left;
    }

    .user-details strong {
        max-width: 155px;
        overflow: hidden;
        color: #23334d;
        font-size: 12px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .user-details small {
        color: #8b97aa;
        font-size: 10px;
    }

    .chevron {
        width: 16px;
        height: 16px;
        color: #8793a7;
    }

    .user-menu-heading {
        padding: 12px 16px;
        display: grid;
        gap: 5px;
        border-bottom: 1px solid #edf0f5;
    }

    .user-menu-heading strong {
        color: #1d2c44;
        font-size: 13px;
    }

    .user-menu-heading small {
        color: #7b879a;
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .logout-link {
        display: flex;
        align-items: center;
        gap: 9px;
        width: 100%;
        padding: 9px 16px;
        color: #d33e4b;
        background: white;
        border: 0;
        text-align: left;
        font: inherit;
        font-size: 13px;
        cursor: pointer;
    }

    .logout-link:hover {
        background: #fff3f4;
    }

    .logout-link svg {
        width: 17px;
        height: 17px;
    }

    .mobile-menu-button,
    .mobile-nav {
        display: none;
    }

    @media (max-width: 760px) {
        .nav-container {
            min-height: 68px;
            padding: 0 16px;
            gap: 12px;
        }

        .brand-icon {
            width: 39px;
            height: 39px;
        }

        .brand-copy strong {
            font-size: 15px;
        }

        .brand-copy small {
            font-size: 8px;
            letter-spacing: .8px;
        }

        .nav-links,
        .nav-user-area,
        .notification-link {
            display: none;
        }

        .mobile-menu-button {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            margin-left: auto;
            color: #31415b;
            background: #f4f7fc;
            border: 1px solid #e6ebf3;
            border-radius: 12px;
            cursor: pointer;
        }

        .mobile-menu-button svg {
            width: 21px;
            height: 21px;
        }

        .mobile-nav {
            display: block;
            padding: 10px 16px 18px;
            border-top: 1px solid #edf0f5;
            background: white;
        }

        .mobile-nav-link {
            display: block;
            padding: 12px 14px;
            margin: 4px 0;
            color: #53627a;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 650;
            text-decoration: none;
        }

        .mobile-nav-link:hover,
        .mobile-nav-link.active {
            color: #245bd7;
            background: #edf3ff;
        }

        .mobile-nav-link .notification-count {
            float: right;
        }

        .mobile-user {
            display: grid;
            gap: 4px;
            padding: 16px 14px 10px;
            margin-top: 8px;
            border-top: 1px solid #edf0f5;
        }

        .mobile-user strong {
            color: #23334d;
            font-size: 13px;
        }

        .mobile-user small {
            color: #8490a4;
            font-size: 11px;
            overflow-wrap: anywhere;
        }

        .mobile-logout {
            width: 100%;
            padding: 12px 14px;
            color: #d33e4b;
            background: #fff3f4;
            border: 0;
            border-radius: 10px;
            text-align: left;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .inventory-nav *,
        .inventory-nav *::before,
        .inventory-nav *::after {
            transition: none !important;
        }
    }
</style>
