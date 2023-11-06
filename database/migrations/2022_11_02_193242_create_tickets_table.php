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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('content');
            $table->boolean('is_repeat')->default(false);
            $table->integer('rating')->nullable();
            $table->text('review_comment')->nullable();
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('responsible_id')->nullable()->constrained('users');
            $table->foreignId('status_id')->constrained('ticket_statuses');
            $table->foreignId('project_id')->constrained('projects');
            $table->timestamp('target_date')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('tickets');
    }
};
