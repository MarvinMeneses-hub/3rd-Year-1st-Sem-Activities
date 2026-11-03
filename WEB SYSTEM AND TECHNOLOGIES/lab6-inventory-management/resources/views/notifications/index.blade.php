<x-app-layout>
    <div class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Inventory alerts and stock updates.
                    </p>
                </div>

                @if(auth()->user()->unreadNotifications()->exists())
                    <form method="POST" action="{{ route('notifications.markAllAsRead') }}">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                            Mark all as read
                        </button>
                    </form>
                @endif
            </div>

            @if(session('success'))
                <div class="mb-5 p-4 rounded-lg bg-green-50 text-green-700 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

                @forelse($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $isUnread = is_null($notification->read_at);
                    @endphp

                    <div class="p-5 sm:p-6 border-b border-gray-100 last:border-b-0
                        {{ $isUnread ? 'bg-blue-50/50' : 'bg-white' }}">

                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-11 h-11 rounded-xl flex items-center justify-center
                                {{ ($data['status'] ?? '') === 'out of stock'
                                    ? 'bg-red-100 text-red-600'
                                    : 'bg-amber-100 text-amber-600' }}">

                                <svg viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="w-6 h-6">
                                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                                    <path d="M10 21h4"/>
                                </svg>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="font-bold text-gray-900">
                                        {{ $data['title'] ?? 'Inventory Notification' }}
                                    </h2>

                                    @if($isUnread)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                                            New
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-1 text-sm text-gray-600">
                                    {{ $data['message'] ?? 'You have a new inventory notification.' }}
                                </p>

                                <p class="mt-2 text-xs text-gray-400">
                                    {{ $notification->created_at->format('M d, Y · h:i A') }}
                                </p>

                                @if($isUnread)
                                    <form method="POST"
                                        action="{{ route('notifications.read', $notification->id) }}"
                                        class="mt-3">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                            class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                                            Mark as read
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-16 px-6 text-center">
                        <div class="mx-auto w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center">
                            <svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8"
                                class="w-7 h-7 text-gray-400">
                                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                                <path d="M10 21h4"/>
                            </svg>
                        </div>

                        <h2 class="mt-4 font-bold text-gray-800">
                            No notifications yet
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Inventory alerts will appear here when generated.
                        </p>
                    </div>
                @endforelse

            </div>

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
