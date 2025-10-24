<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_cities', function (Blueprint $table) {
            $table->id('general_city_id');
            $table->unsignedInteger('general_country_id')->nullable();
            $table->unsignedInteger('general_state_id');
            $table->string('city_name', 60);
            $table->decimal('city_latitude', 10, 6)->nullable();
            $table->decimal('city_longitude', 10, 6)->nullable();
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('general_cities');
    }
};
