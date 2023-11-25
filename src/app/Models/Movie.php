<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'imdb_id',
        'title',
        'type',
        'release_date',
        'year',
        'poster_url',
        'genre',
        'runtime',
        'country',
        'imdb_rating',
        'imdb_votes',
    ];
}
