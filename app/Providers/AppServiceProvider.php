<?php

namespace App\Providers;

use App\Contracts\MovieRepositoryInterface;
use App\Models\Comment;
use App\Models\User;
use App\Repositories\OmdbRepository;
use GuzzleHttp\Psr7\HttpFactory;
use Http\Adapter\Guzzle7\Client;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->bind(MovieRepositoryInterface::class, function () {
            $client = Client::createWithConfig([]);
            $requestFactory = new HttpFactory();

            return new OmdbRepository(
                $client,
                $requestFactory,
                config('services.omdb.key')
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('update-comment', function (User $user, Comment $comment) {
            return $user->isModerator() || $user->id === $comment->user_id;
        });

        Gate::define('destroy-comment', function (User $user, Comment $comment) {
            if ($user->isModerator()) {
                return true;
            }

            return $user->id === $comment->user_id && $comment->childComments->isEmpty();
        });
    }
}
