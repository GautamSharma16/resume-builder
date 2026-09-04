@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-8 flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600">Account Registrations</p>
            <h1 class="mt-2 text-3xl font-black text-slate-950">Registered users</h1>
        </div>
        <form method="GET" action="{{ route('admin.registrations') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_1fr_auto]">
            <input type="hidden" name="scope" value="period">
            <label class="text-sm font-semibold text-slate-700">
                From
                <input type="date" name="from" value="{{ $from->toDateString() }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            </label>
            <label class="text-sm font-semibold text-slate-700">
                To
                <input type="date" name="to" value="{{ $to->toDateString() }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            </label>
            <button class="self-end rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-700">Apply</button>
        </form>
    </div>

    <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-3">
        <a href="{{ route('admin.registrations', ['scope' => 'all']) }}" class="rounded-2xl border p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $scope === 'all' ? 'border-blue-500 bg-blue-50' : 'border-slate-200 bg-white' }}">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">All users</p>
            <p class="mt-2 text-3xl font-black text-slate-950">{{ $allUsersCount }}</p>
        </a>
        <a href="{{ route('admin.registrations', ['scope' => 'today']) }}" class="rounded-2xl border p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $scope === 'today' ? 'border-blue-500 bg-blue-50' : 'border-slate-200 bg-white' }}">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Today users</p>
            <p class="mt-2 text-3xl font-black text-slate-950">{{ $todayUsersCount }}</p>
        </a>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Selected period</p>
            <p class="mt-2 text-3xl font-black text-slate-950">{{ $selectedUsersCount }}</p>
        </div>
    </div>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h2 class="text-lg font-bold text-slate-950">User list</h2>
            <span class="text-sm text-slate-500">{{ $selectedUsersCount }} users</span>
        </div>
        @if($registeredUsers->isEmpty())
            <p class="p-10 text-center font-semibold text-slate-500">No users found for this period.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-3">#</th>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Registered date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($registeredUsers as $index => $registeredUser)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-slate-400">{{ $registeredUsers->firstItem() + $index }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $registeredUser->name ?: 'Unnamed user' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $registeredUser->email }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $registeredUser->created_at->timezone($timezone)->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $registeredUsers->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
