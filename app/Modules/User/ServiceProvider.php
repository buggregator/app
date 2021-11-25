<?php

declare(strict_types=1);

namespace Modules\User;

use App\Providers\DomainServiceProvider;
use Cycle\ORM\ORMInterface;
use Modules\User\Domain\User;
use Modules\User\Domain\UserRepository;
use Modules\User\Interfaces\Console\Commands\CreateUser;

final class ServiceProvider extends DomainServiceProvider
{
    public function boot()
    {
        $this->app->bind(UserRepository::class, function () {
            return clone $this->app[ORMInterface::class]->getRepository(User::class);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateUser::class,
            ]);
        }
    }
}
