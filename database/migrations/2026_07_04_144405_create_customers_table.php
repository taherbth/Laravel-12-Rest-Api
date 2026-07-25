<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('gender_id')->unsigned()->nullable();
            $table->integer('country_id')->unsigned()->nullable();
            $table->text('customer_no')->comment('Customer_no =  Current Year+This user id');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('cell_phone', 30)->nullable();
            $table->string('work_phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->text('address2')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('zip', 12)->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('gender_id')->references('id')->on('genders');
            $table->foreign('country_id')->references('id')->on('countries');

            $table->index('first_name');
            $table->index('last_name');
            $table->index('email');

            // $table->fullText(['first_name', 'last_name', 'email']); // If you must support matching text anywhere inside the fields (e.g., searching "gmail" matches "user@gmail.com")


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
