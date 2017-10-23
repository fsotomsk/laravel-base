<?php

use CDeep\Helpers\DB\{Blueprint, Migration};

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->create('menus', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name',        128)->nullable();
            $table->string('description', 255)->nullable();

            $table->text('page_ids')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropIfExists('menus');
    }
}
