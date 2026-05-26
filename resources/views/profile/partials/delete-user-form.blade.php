<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" maxWidth="md" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-5 sm:p-6">
            @csrf
            @method('delete')

            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h2 class="text-lg font-bold text-slate-950">
                        {{ __('Delete your account?') }}
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ __('This permanently removes your account and all related data. Enter your password to confirm this action.') }}
                    </p>
                </div>
            </div>

            <div class="mt-6">
                <x-input-label for="delete_account_password" value="{{ __('Password') }}" class="text-sm font-semibold text-slate-700" />

                <x-text-input
                    id="delete_account_password"
                    name="password"
                    type="password"
                    class="mt-2 block w-full rounded-xl border-slate-300 text-slate-950 shadow-sm focus:border-red-500 focus:ring-red-500"
                    placeholder="{{ __('Password') }}"
                    autocomplete="current-password"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-secondary-button type="button" class="justify-center rounded-xl px-5 py-2.5" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="justify-center rounded-xl bg-red-600 px-5 py-2.5 hover:bg-red-700 sm:ms-0">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
