<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('visitor_logs', 'visitor_id')) {
                $table->uuid('visitor_id')->nullable()->after('id');
            }

            if (! Schema::hasColumn('visitor_logs', 'visitor_hash')) {
                $table->char('visitor_hash', 64)->nullable()->after('visitor_id');
            }

            if (! Schema::hasColumn('visitor_logs', 'ip_hash')) {
                $table->char('ip_hash', 64)->nullable()->after('ip_address');
            }

            if (! Schema::hasColumn('visitor_logs', 'user_agent_hash')) {
                $table->char('user_agent_hash', 64)->nullable()->after('user_agent');
            }

            if (! Schema::hasColumn('visitor_logs', 'device_hash')) {
                $table->char('device_hash', 64)->nullable()->after('user_agent_hash');
            }

            if (! Schema::hasColumn('visitor_logs', 'first_visited_at')) {
                $table->timestamp('first_visited_at')->nullable()->after('device_hash');
            }

            if (! Schema::hasColumn('visitor_logs', 'last_visited_at')) {
                $table->timestamp('last_visited_at')->nullable()->after('first_visited_at');
            }
        });

        DB::table('visitor_logs')
            ->whereNull('visitor_hash')
            ->orderBy('id')
            ->cursor()
            ->each(function ($log) {
                $visitorId = Str::isUuid((string) $log->session_id) ? $log->session_id : (string) Str::uuid();
                $visitorHash = hash_hmac('sha256', 'visitor|'.$visitorId, config('app.key'));

                DB::table('visitor_logs')
                    ->where('id', $log->id)
                    ->update([
                        'visitor_id' => $visitorId,
                        'session_id' => $visitorId,
                        'visitor_hash' => $visitorHash,
                        'ip_hash' => $log->ip_address ? hash_hmac('sha256', (string) $log->ip_address, config('app.key')) : null,
                        'user_agent_hash' => $log->user_agent ? hash_hmac('sha256', (string) $log->user_agent, config('app.key')) : null,
                        'device_hash' => hash_hmac('sha256', implode('|', [$log->ip_address, $log->user_agent]), config('app.key')),
                        'first_visited_at' => $log->created_at,
                        'last_visited_at' => $log->updated_at ?: $log->created_at,
                    ]);
            });

        DB::statement('
            DELETE older_logs FROM visitor_logs older_logs
            INNER JOIN visitor_logs newer_logs
                ON older_logs.visitor_hash = newer_logs.visitor_hash
                AND older_logs.path = newer_logs.path
                AND older_logs.id < newer_logs.id
        ');

        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->index(['visitor_hash', 'last_visited_at'], 'visitor_logs_visitor_last_idx');
            $table->index(['path', 'last_visited_at'], 'visitor_logs_path_last_idx');
            $table->unique(['visitor_hash', 'path'], 'visitor_logs_visitor_path_unique');
        });
    }

    public function down(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->dropUnique('visitor_logs_visitor_path_unique');
            $table->dropIndex('visitor_logs_visitor_last_idx');
            $table->dropIndex('visitor_logs_path_last_idx');
            $table->dropColumn([
                'visitor_id',
                'visitor_hash',
                'ip_hash',
                'user_agent_hash',
                'device_hash',
                'first_visited_at',
                'last_visited_at',
            ]);
        });
    }
};
