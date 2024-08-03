<?php

use App\Enums\WorkSiteCompletionStatusEnum;
use App\Enums\WorkSiteReceptionStatusEnum;
use App\Models\Contractor;
use App\Models\Customer;
use App\Models\WorkSite;
use App\Models\WorkSiteCategory;
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
        Schema::create('work_sites', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->foreignIdFor(Customer::class)->nullable();
            $table->foreignIdFor(WorkSiteCategory::class, 'category_id')->nullable();
            $table->foreignIdFor(WorkSite::class, 'parent_work_site_id')->nullable();
            $table->foreignIdFor(Contractor::class)->nullable();
            $table->decimal('starting_budget', 8, 2)->nullable()->default(0);
            $table->decimal('cost', 8, 2)->nullable()->default(0);
            $table->foreignIdFor(\App\Models\Address::class)->nullable();
            $table->integer('workers_count')->nullable()->default(0);
            $table->date('receipt_date')->nullable();
            $table->date('starting_date')->nullable();
            $table->date('deliver_date')->nullable();
            $table->tinyInteger('reception_status')->nullable()->default(WorkSiteReceptionStatusEnum::SCRATCH->value);
            $table->tinyInteger('completion_status')->nullable()->default(WorkSiteCompletionStatusEnum::STARTED->value);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_sites');
    }
};
