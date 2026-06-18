<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Support;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 */
final class User extends Authenticatable
{
    public $timestamps = false;

    protected $table = 'users';

    protected $guarded = [];
}
