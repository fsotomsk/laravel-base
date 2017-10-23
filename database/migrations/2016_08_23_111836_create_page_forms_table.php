<?php

use CDeep\Helpers\DB\{Blueprint, Migration};

class CreatePageFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->create('page_forms', function (Blueprint $table) {

            $table->unsignedBigInteger('page_id');
            $table->longText('data')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->foreign('page_id')
                ->references('id')
                ->on('pages')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropIfExists('page_forms');
    }
}
