<?php

use App\Enums\WareHouseMovementsTypeEnum;
use App\Models\Item;
use App\Models\User;
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
        Schema::create('warehouse_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Warehouse::class, 'warehouse_id');
            $table->foreignIdFor(Item::class, 'item_id');
            $table->morphs('movable'); // from supplier or to other wareHouse ...
            $table->unique(['warehouse_id', 'item_id']);

            $table->decimal('price', 8, 2);
            $table->float('quantity');
            $table->tinyInteger('type')->default(WareHouseMovementsTypeEnum::ADD_STOCK->value);
            $table->dateTime('moved_at')->nullable();
            $table->foreignIdFor(User::class, 'moved_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_movements');
    }
};
