<?php

use CDeep\Helpers\DB\{Blueprint, Migration};

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->create('users', function (Blueprint $table) {

	        $table->bigIncrements('id');
	        $table->unsignedBigInteger('group_id')->nullable()->index();

	        $table->string('email', 128)->unique();
	        $table->string('password')->nullable();
            $table->string('api_token', 60)->nullable()->unique();
	        $table->rememberToken();

	        $table->string('name',  128)->nullable();

	        $table->boolean('is_enabled')->default(1);
            $table->timestamps();
	        $table->softDeletes();

	        $table->foreign('group_id')
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
        $this->dropIfExists('users');
    }
}
