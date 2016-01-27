<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('funds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id')->unsigned();
            $table->string('name');
            $table->string('type',10);
            $table->decimal('cost_price', 12, 2);
            $table->decimal('units_held', 12, 4);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->string('broker',4);
            $table->string('currency',4);
            $table->string('url');
            $table->smallInteger('price_units');
            $table->string('chart_code')->nullable();
            $table->date('purchased_at')->nullable();
            $table->date('disposed_at')->nullable();
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
        Schema::drop('funds');
    }
}
