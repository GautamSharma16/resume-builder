@extends('layouts.admin')

@section('title', 'Edit Team Member - Admin')

@section('content')
<div class="p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:underline flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Back to Team
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Edit Team Member</h1>
            <p class="text-sm text-gray-500">Update account details for {{ $user->name }}</p>
        </div>

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-6">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition @error('name') border-red-500 @enderror">
                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition @error('email') border-red-500 @enderror">
                @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select name="role" id="role" required
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition @error('role') border-red-500 @enderror">
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" {{ old('role', $user->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div id="permissions-container" class="pt-4 border-t border-gray-100 {{ old('role', $user->role) === 'admin' ? 'hidden' : '' }}">
                <p class="text-sm font-medium text-gray-900 mb-4">Permissions</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($permissions as $key => $label)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition">
                        <input type="checkbox" name="permissions[]" value="{{ $key }}" {{ in_array($key, old('permissions', $user->permissions ?? [])) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 font-medium">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
                @error('permissions') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <script>
                document.getElementById('role').addEventListener('change', function() {
                    const container = document.getElementById('permissions-container');
                    if (this.value === 'admin') {
                        container.classList.add('hidden');
                    } else {
                        container.classList.remove('hidden');
                    }
                });
            </script>

            <div class="pt-4 border-t border-gray-100">
                <p class="text-sm font-medium text-gray-900 mb-4">Change Password (leave blank to keep current)</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" name="password" id="password"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition @error('password') border-red-500 @enderror">
                        @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition">
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                    Update Team Member
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
