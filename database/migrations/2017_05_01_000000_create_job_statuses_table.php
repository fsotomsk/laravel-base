<?php

use CDeep\Helpers\DB\{Blueprint, Migration};

class CreateJobStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->create('job_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('job_id')->index()->nullable();
            $table->string('type')->index();
            $table->string('queue')->index()->nullable();
            $table->integer('attempts')->default(0);
            $table->integer('progress_now')->default(0);
            $table->integer('progress_max')->default(0);
            $table->string('status', 16)->default('queued')->index();
            $table->longText('input')->nullable();
            $table->longText('output')->nullable();
            $table->timestamps();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropIfExists('job_statuses');
    }
}
