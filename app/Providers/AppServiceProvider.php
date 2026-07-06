<?php

namespace App\Providers;

use App\Contracts\MovieRepositoryInterface;
use App\Models\Comment;
use App\Models\User;
use App\Repositories\OmdbRepository;
use GuzzleHttp\Psr7\HttpFactory;
use Http\Adapter\Guzzle7\Client;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует привязку репозитория внешнего источника данных
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
                config('services.omdb.key'),
                config('services.omdb.url')
            );
        });
    }

    /**
     * Регистрирует лимитер обращений к OMDb API и правила доступа к комментариям
     */
    public function boot(): void
    {
        RateLimiter::for('omdb', function (): Limit {
            return Limit::perMinute(config('services.omdb.rate_limit'));
        });

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
