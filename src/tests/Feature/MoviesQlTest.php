<?php

namespace Tests\Feature;

use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MoviesQlTest extends TestCase
{

    public function testSyncMovies()
    {
        $this->graphQL(
        /** @lang GraphQL */ '
                mutation {
                  syncMovies(search: "batman",type: MOVIE, page: 1)
                }
            '
        )->assertJson([
            'data' => [
                'syncMovies' => true
            ],
        ]);
        $this->assertGreaterThan(0, Movie::count());
    }

    public function testSyncMoviesError()
    {
        $beforeCount = Movie::count();
        // setting search as a random string - so there will be no movies found
        $this->graphQL(
        /** @lang GraphQL */ '
                mutation {
                  syncMovies(search: "vfg",type: MOVIE, page: 1)
                }
            '
        )->assertJson([
            'data' => [
                'syncMovies' => false
            ],
        ]);
        $this->assertEquals($beforeCount, Movie::count());
    }


    public function testGetMovies()
    {
        $this->graphQL(
        /** @lang GraphQL */ '
            {
                movies(first:5, page:1){
                    data{
                        id
                        title
                        imdb_id
                        poster_url
                        type
                        genre
                        ratings{
                            imdb_id
                            id
                            source
                            value
                        }
                    }
                }
            }
            '
        )->assertJsonStructure([
            'data' => [
                'movies' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'imdb_id',
                            'poster_url',
                            'type',
                            'genre',
                            'ratings' => [
                                '*' => [
                                    'imdb_id',
                                    'id',
                                    'source',
                                    'value',
                                ]
                            ],
                        ]
                    ]
                ]
            ],
        ])->assertJsonCount(5, 'data.movies.data');
    }

    public function testGetMovie()
    {
        $movieId = Movie::first()->id;
        $this->graphQL(
        /** @lang GraphQL */ "
            {
                movie(id: $movieId) {
                    id
                    title
                    imdb_id
                    poster_url
                    type
                    genre
                    ratings{
                        imdb_id
                        id
                        source
                        value
                    }
                }
            }
            "
        )->assertJsonStructure([
            'data' => [
                'movie' => [
                    'id',
                    'title',
                    'imdb_id',
                    'poster_url',
                    'type',
                    'genre',
                    'ratings' => [
                        '*' => [
                            'imdb_id',
                            'id',
                            'source',
                            'value',
                        ]
                    ],
                ]
            ],
        ]);
    }

    public function testUpdateMovie()
    {
        $movieId = Movie::first()->id;
        $newTitle = 'Test title';
        $this->graphQL(
        /** @lang GraphQL */ "
            mutation {
                updateMovie(id: $movieId, title: \"$newTitle\"){
                      id
                      title
                  }
                }
            "
        )->assertJson([
            'data' => [
                'updateMovie' => [
                    'id' => $movieId,
                    'title' => $newTitle,
                ]
            ],
        ]);
    }
}
