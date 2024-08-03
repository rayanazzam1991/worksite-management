<?php

use App\Enums\OrderPriorityEnum;
use App\Enums\OrderStatusEnum;
use App\Models\User;
use App\Models\WorkSite;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(WorkSite::class)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->tinyInteger('status')->default(OrderStatusEnum::PENDING->value);
            $table->integer('priority')->nullable()->default(OrderPriorityEnum::NORMAL->value);
            $table->foreignIdFor(User::class, 'created_by');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
