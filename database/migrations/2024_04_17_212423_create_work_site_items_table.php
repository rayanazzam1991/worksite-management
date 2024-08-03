<?php

use App\Models\Item;
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
        Schema::create('work_site_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(WorkSite::class, 'work_site_id');
            $table->foreignIdFor(Item::class, 'item_id');
            $table->integer('quantity');
            $table->decimal('price', 8, 2);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_site_items');
    }
};
