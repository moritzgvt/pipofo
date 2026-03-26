<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Edit User') }}: {{ $user->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="role" :value="__('Role')" />
                        @if(count($availableRoles) > 0 && isset($availableRoles[$user->role]))
                            <select id="role" name="role" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                @foreach($availableRoles as $value => $label)
                                    <option value="{{ $value }}" {{ old('role', $user->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        @else
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->role_label }}</p>
                        @endif
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('users.show', $user) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">Cancel</a>
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                    </div>
                </form>
            </div>

            @if($user->id !== auth()->id())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ $user->is_suspended ? 'Reactivate User' : 'Suspend User' }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        {{ $user->is_suspended ? 'Reactivating this user will allow them to log in and use the application again.' : 'Suspending this user will prevent them from logging in and using the application.' }}
                    </p>
                    <form method="POST" action="{{ route('users.toggle-suspend', $user) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" onclick="return confirm('{{ $user->is_suspended ? 'Reactivate' : 'Suspend' }} this user?')" class="px-4 py-2 {{ $user->is_suspended ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }} text-white rounded-md transition text-sm">
                            {{ $user->is_suspended ? 'Reactivate User' : 'Suspend User' }}
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
