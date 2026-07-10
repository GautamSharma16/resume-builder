<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $oldEmail = 'admin@resumebuilder.com';
        $newEmail = 'siddhartha.verma@cvbliss.in';

        $oldAdmin = DB::table('users')
            ->where('email', $oldEmail)
            ->where('role', 'admin')
            ->first();

        if (! $oldAdmin) {
            return;
        }

        $newAdminExists = DB::table('users')->where('email', $newEmail)->exists();

        if ($newAdminExists) {
            DB::table('users')
                ->where('email', $newEmail)
                ->where('role', 'admin')
                ->update([
                    'password' => $oldAdmin->password,
                    'email_verified_at' => $oldAdmin->email_verified_at,
                ]);

            return;
        }

        DB::table('users')
            ->where('id', $oldAdmin->id)
            ->update(['email' => $newEmail]);
    }

    public function down(): void
    {
        $oldEmail = 'admin@resumebuilder.com';
        $newEmail = 'siddhartha.verma@cvbliss.in';

        $oldAdminExists = DB::table('users')->where('email', $oldEmail)->exists();

        if (! $oldAdminExists) {
            DB::table('users')
                ->where('email', $newEmail)
                ->where('role', 'admin')
                ->update(['email' => $oldEmail]);
        }
    }
};
