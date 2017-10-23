<?php

use CDeep\Helpers\DB\{Blueprint, Migration};

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->create('pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('page_id')->nullable()->index();
            $table->unsignedBigInteger('back_page_id')->nullable()->index();

	        $table->unsignedBigInteger('owner_user_id')->nullable()->index();
	        $table->unsignedBigInteger('owner_group_id')->nullable()->index();

	        $table->unsignedTinyInteger('access_owner')->nullable();
	        $table->unsignedTinyInteger('access_group')->nullable();
	        $table->unsignedTinyInteger('access_all')->nullable();

            $table->string('uri',  128)->index();
	        $table->string('controller', 50)->nullable();

            $table->string('title', 128)->nullable();
            $table->string('topic', 128)->nullable();
            $table->string('menu',  128)->nullable();
            $table->string('keywords', 255)->nullable();
            $table->string('description', 255)->nullable();

            $table->string('template_resource',  30)->nullable();
            $table->string('template_env',      128)->nullable();
            $table->string('template_view',     128)->nullable();

            $table->boolean('show_in_menu')->default(0)->index();
            $table->unsignedInteger('menu_sort_order')->default(0)->index();

            $table->boolean('is_published')->default(1)->index();
            $table->boolean('is_enabled')->default(1)->index();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

	        /**
	         * нужен составной уникальный индекс page_id + uri
	         */
            $table->foreign('page_id')
                ->references('id')
                ->on('pages')
                ->onDelete('set null');

            $table->foreign('back_page_id')
                ->references('id')
                ->on('pages')
                ->onDelete('set null');

	        $table->foreign('owner_user_id')
	              ->references('id')
	              ->on('users')
	              ->onDelete('set null');

	        $table->foreign('owner_group_id')
	              ->references('id')
	              ->on('groups')
	              ->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropIfExists('pages');
    }
}
