<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('mfg_name');
            $table->string('mfg_item_number');
            $table->string('item_number');
            $table->integer('available')->default(0);
            $table->boolean('ltl')->default(false);
            $table->integer('mfg_qty_available')->nullable();
            $table->string('stocking')->nullable();
            $table->string('special_order')->nullable();
            $table->string('oversize')->nullable();
            $table->string('addtl_handling_charge')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
