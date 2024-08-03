<?php

use App\Enums\WareHouseItemStatusEnum;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Warehouse;
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
        Schema::create('warehouse_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Warehouse::class, 'warehouse_id');
            $table->foreignIdFor(Item::class, 'item_id');
            $table->foreignIdFor(Supplier::class, 'supplier_id')->nullable();

            $table->unique(['warehouse_id', 'item_id']);

            $table->decimal('price', 8, 2);
            $table->float('quantity');
            $table->tinyInteger('status')->default(WareHouseItemStatusEnum::IN_STOCK->value);
            $table->dateTime('date')->nullable()->useCurrent();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_items');
    }
};
