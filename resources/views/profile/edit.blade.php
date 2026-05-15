<x-app-layout>
    <x-slot name="header">
        <div class="profile-headerbar">
            <div>
                <p class="profile-eyebrow">Account Center</p>
                <h2>{{ __('Profile') }}</h2>
            </div>
            <div class="profile-chip">{{ auth()->user()->email }}</div>
        </div>
    </x-slot>

    <style>
        .profile-headerbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            width: 100%;
        }
        .profile-headerbar h2 {
            margin: 0;
            font-size: 1.45rem;
            font-weight: 800;
            color: #0f172a;
        }
        .profile-eyebrow {
            margin: 0 0 0.2rem;
            font-size: 0.72rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 800;
            color: #0f766e;
        }
        .profile-chip {
            max-width: 100%;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 999px;
            padding: 0.45rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 700;
            overflow-wrap: anywhere;
        }
        .profile-shell {
            background:
                radial-gradient(circle at 10% 0%, rgba(37,99,235,0.10), transparent 32%),
                linear-gradient(180deg, #f8fafc, #ffffff);
            min-height: calc(100vh - 84px);
            padding: 3rem 1rem 4rem;
        }
        .profile-grid {
            max-width: 1180px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(220px, 320px) minmax(0, 1fr);
            gap: 1.25rem;
            align-items: start;
        }
        .profile-summary {
            background: #0f172a;
            color: #fff;
            border-radius: 24px;
            padding: 1.5rem;
            box-shadow: 0 22px 60px rgba(15, 23, 42, 0.18);
            position: sticky;
            top: 96px;
        }
        .profile-avatar {
            width: 68px;
            height: 68px;
            border-radius: 20px;
            background: linear-gradient(135deg, #14b8a6, #2563eb);
            display: grid;
            place-items: center;
            font-size: 1.6rem;
            font-weight: 900;
            margin-bottom: 1rem;
        }
        .profile-summary h3 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 900;
            overflow-wrap: anywhere;
        }
        .profile-summary p {
            margin: 0.4rem 0 0;
            color: #cbd5e1;
            font-size: 0.9rem;
            overflow-wrap: anywhere;
        }
        .profile-stack {
            display: grid;
            gap: 1rem;
            min-width: 0;
        }
        .profile-card {
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(15,23,42,0.08);
            border-radius: 24px;
            padding: clamp(1.25rem, 2.5vw, 2rem);
            box-shadow: 0 18px 48px rgba(15,23,42,0.07);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .profile-card:hover {
            transform: translateY(-2px);
            border-color: rgba(20,184,166,0.22);
            box-shadow: 0 24px 62px rgba(15,23,42,0.1);
        }
        .profile-card section > header h2 {
            font-size: 1.1rem !important;
            font-weight: 900 !important;
            color: #0f172a !important;
        }
        .profile-card section > header p {
            color: #64748b !important;
        }
        .profile-card input {
            border-radius: 14px !important;
            border-color: #dbe3ef !important;
            min-height: 46px;
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .profile-card input:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 4px rgba(37,99,235,0.12) !important;
        }
        @media (max-width: 860px) {
            .profile-headerbar,
            .profile-grid { grid-template-columns: 1fr; }
            .profile-headerbar { align-items: flex-start; flex-direction: column; }
            .profile-summary { position: relative; top: auto; }
        }
    </style>

    <div class="profile-shell">
        <div class="profile-grid">
            <aside class="profile-summary">
                <div class="profile-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <h3>{{ auth()->user()->name }}</h3>
                <p>{{ auth()->user()->email }}</p>
                <p style="margin-top:1rem;">Manage account details, password security, and account removal from one focused place.</p>
            </aside>

            <div class="profile-stack">
            <div class="profile-card">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="profile-card">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="profile-card">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
            </div>
        </div>
    </div>
</x-app-layout>
