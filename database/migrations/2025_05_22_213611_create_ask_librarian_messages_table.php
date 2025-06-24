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
        Schema::create('ask_librarian_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('session_id');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('References the question this message is answering');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('sender'); // 'visitor' or 'librarian'
            $table->text('message');
            $table->string('file_url')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index('session_id');
            $table->index('parent_id');

            // Foreign key constraint (self-referencing)
            $table->foreign('parent_id')
                ->references('id')
                ->on('ask_librarian_messages')
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
        Schema::table('ask_librarian_messages', function (Blueprint $table) {
            // First drop the foreign key constraint
            $table->dropForeign(['parent_id']);
        });

        Schema::dropIfExists('ask_librarian_messages');
    }
};
