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

        DB::table('users')
            ->where('email', $newEmail)
            ->where('role', 'admin')
            ->update([
                'password' => $oldAdmin->password,
                'email_verified_at' => $oldAdmin->email_verified_at,
            ]);
    }

    public function down(): void
    {
        //
    }
};
