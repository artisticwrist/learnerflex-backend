<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tx_ref')->unique();
            $table->string('transaction_id')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('affiliate_id');
            $table->integer('product_id');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10);
            $table->string('status')->default('pending');
            $table->integer('org_company')->default(0);
            $table->integer('org_aff')->default(0);;
            $table->integer('org_vendor')->default(0);
            $table->boolean('is_onboard')->default(0);
            $table->string('email');
            $table->json('meta')->nullable();  // To store any additional data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
