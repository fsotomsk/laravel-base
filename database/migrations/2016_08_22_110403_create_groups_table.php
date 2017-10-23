<?php

use CDeep\Helpers\DB\{Blueprint, Migration};

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->create('groups', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->string('name', 128)->unique();
            $table->boolean('is_enabled')->default(1);

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
        $this->dropIfExists('groups');
    }
}
