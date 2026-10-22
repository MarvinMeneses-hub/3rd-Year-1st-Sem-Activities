
<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">Account settings</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">My Profile</h1>
            <p class="mt-1 text-sm text-slate-500">Manage your personal information and account security.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl space-y-6">

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 bg-slate-900 px-6 py-7 sm:flex-row sm:items-center sm:px-8">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border border-white/15 bg-white/10 text-2xl font-extrabold text-white">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-300">Your account</p>
                        <h2 class="mt-1 break-words text-xl font-extrabold text-white">{{ Auth::user()->name }}</h2>
                        <p class="mt-1 break-all text-sm text-slate-300">{{ Auth::user()->email }}</p>
                    </div>
                    <div class="sm:ml-auto">
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1.5 text-xs font-bold text-emerald-300">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            Account active
                        </span>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
                <div class="mb-6 flex items-start gap-3 border-b border-slate-100 pb-5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 21a8 8 0 0 0-16 0m8-10a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900">Personal information</h3>
                        <p class="mt-1 text-sm text-slate-500">Update the name and email associated with your account.</p>
                    </div>
                </div>
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
                <div class="mb-6 flex items-start gap-3 border-b border-slate-100 pb-5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2Zm12-11V7a6 6 0 0 0-12 0v2"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900">Password and security</h3>
                        <p class="mt-1 text-sm text-slate-500">Choose a strong password to help protect your account.</p>
                    </div>
                </div>
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </section>

            <section class="rounded-2xl border border-red-200 bg-white p-5 shadow-sm sm:p-8">
                <div class="mb-6 flex items-start gap-3 border-b border-red-100 pb-5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6h18m-2 0-.9 14H5.9L5 6m4 0V4h6v2m-5 4v6m4-6v6"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900">Delete account</h3>
                        <p class="mt-1 text-sm text-slate-500">Review the consequences before permanently deleting your account.</p>
                    </div>
                </div>
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </section>

        </div>
    </div>
</x-app-layout>
