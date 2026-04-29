<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'downloads_allowed')) {
                $table->unsignedInteger('downloads_allowed')->nullable()->after('price_paise');
            }

            if (! Schema::hasColumn('plans', 'duration_days')) {
                $table->unsignedInteger('duration_days')->default(14)->after('downloads_allowed');
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('subscriptions', 'plan_slug')) {
                $table->string('plan_slug')->nullable()->after('plan_id')->index();
            }

            if (! Schema::hasColumn('subscriptions', 'downloads_allowed')) {
                $table->unsignedInteger('downloads_allowed')->nullable()->after('status');
            }

            if (! Schema::hasColumn('subscriptions', 'downloads_used')) {
                $table->unsignedInteger('downloads_used')->default(0)->after('downloads_allowed');
            }

            if (! Schema::hasColumn('subscriptions', 'plan_started_at')) {
                $table->timestamp('plan_started_at')->nullable()->after('downloads_used');
            }

            if (! Schema::hasColumn('subscriptions', 'expiry_date')) {
                $table->timestamp('expiry_date')->nullable()->after('plan_started_at');
            }
        });

        Schema::table('purchases', function (Blueprint $table) {
            if (! Schema::hasColumn('purchases', 'plan_slug')) {
                $table->string('plan_slug')->nullable()->after('plan_id')->index();
            }

            if (! Schema::hasColumn('purchases', 'razorpay_payment_link_id')) {
                $table->string('razorpay_payment_link_id')->nullable()->after('status')->index();
            }

            if (! Schema::hasColumn('purchases', 'razorpay_payment_link_reference_id')) {
                $table->string('razorpay_payment_link_reference_id')->nullable()->after('razorpay_payment_link_id')->index();
            }

            if (! Schema::hasColumn('purchases', 'razorpay_payment_link_url')) {
                $table->text('razorpay_payment_link_url')->nullable()->after('razorpay_payment_link_reference_id');
            }

            if (! Schema::hasColumn('purchases', 'webhook_event_id')) {
                $table->string('webhook_event_id')->nullable()->after('razorpay_signature')->unique();
            }

            if (! Schema::hasColumn('purchases', 'notes')) {
                $table->json('notes')->nullable()->after('webhook_event_id');
            }
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('mobile')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->string('plan_slug')->index();
            $table->unsignedInteger('amount_paise');
            $table->string('currency', 3)->default('INR');
            $table->string('status')->default('paid');
            $table->string('razorpay_payment_id')->unique();
            $table->string('webhook_event_id')->nullable()->unique();
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('contact_messages');

        Schema::table('purchases', function (Blueprint $table) {
            foreach ([
                'notes',
                'webhook_event_id',
                'razorpay_payment_link_url',
                'razorpay_payment_link_reference_id',
                'razorpay_payment_link_id',
                'plan_slug',
            ] as $column) {
                if (Schema::hasColumn('purchases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            foreach ([
                'expiry_date',
                'plan_started_at',
                'downloads_used',
                'downloads_allowed',
                'plan_slug',
            ] as $column) {
                if (Schema::hasColumn('subscriptions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('plans', function (Blueprint $table) {
            foreach (['duration_days', 'downloads_allowed'] as $column) {
                if (Schema::hasColumn('plans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
