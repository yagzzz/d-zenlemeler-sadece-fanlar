<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY invoice_type ENUM('subscription','ppv','tip') NOT NULL");
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');

            Schema::create('invoices_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payer_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('payee_id')->constrained('users')->cascadeOnDelete();
                $table->enum('invoice_type', ['subscription', 'ppv', 'tip']);
                $table->enum('status', ['pending', 'paid', 'expired', 'cancelled'])->default('pending');
                $table->unsignedBigInteger('amount_atomic');
                $table->string('currency', 10)->default('XMR');
                $table->string('address', 255);
                $table->string('payment_reference', 255)->unique();
                $table->timestamp('expires_at');
                $table->timestamp('paid_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['payer_id', 'status']);
                $table->index(['payee_id', 'status']);
            });

            DB::statement('INSERT INTO invoices_new (id, payer_id, payee_id, invoice_type, status, amount_atomic, currency, address, payment_reference, expires_at, paid_at, metadata, created_at, updated_at) SELECT id, payer_id, payee_id, invoice_type, status, amount_atomic, currency, address, payment_reference, expires_at, paid_at, metadata, created_at, updated_at FROM invoices');
            Schema::drop('invoices');
            Schema::rename('invoices_new', 'invoices');

            DB::statement('PRAGMA foreign_keys=ON');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY invoice_type ENUM('subscription','ppv') NOT NULL");
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');

            Schema::create('invoices_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payer_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('payee_id')->constrained('users')->cascadeOnDelete();
                $table->enum('invoice_type', ['subscription', 'ppv']);
                $table->enum('status', ['pending', 'paid', 'expired', 'cancelled'])->default('pending');
                $table->unsignedBigInteger('amount_atomic');
                $table->string('currency', 10)->default('XMR');
                $table->string('address', 255);
                $table->string('payment_reference', 255)->unique();
                $table->timestamp('expires_at');
                $table->timestamp('paid_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['payer_id', 'status']);
                $table->index(['payee_id', 'status']);
            });

            DB::statement("INSERT INTO invoices_new (id, payer_id, payee_id, invoice_type, status, amount_atomic, currency, address, payment_reference, expires_at, paid_at, metadata, created_at, updated_at) SELECT id, payer_id, payee_id, CASE WHEN invoice_type = 'tip' THEN 'subscription' ELSE invoice_type END, status, amount_atomic, currency, address, payment_reference, expires_at, paid_at, metadata, created_at, updated_at FROM invoices");
            Schema::drop('invoices');
            Schema::rename('invoices_new', 'invoices');

            DB::statement('PRAGMA foreign_keys=ON');
        }
    }
};
