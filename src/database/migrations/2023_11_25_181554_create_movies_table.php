<?php

use App\Enums\MovieType;
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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string("imdb_id")->unique();
            $table->string("title");
            $table->enum('type', MovieType::values());
            $table->date('release_date');
            $table->year('year');
            $table->string('poster_url');
            $table->string('genre');
            $table->integer('runtime');
            $table->string('country');
            $table->decimal('imdb_rating', 3, 1);
            $table->integer('imdb_votes');
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
        Schema::dropIfExists('movies');
    }
};
