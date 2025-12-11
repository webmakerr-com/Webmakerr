<?php

namespace Webmakerr\App\Models;

use Webmakerr\App\Models\Concerns\CanSearch;

class DynamicModel extends Model
{
    use CanSearch;

    public function __construct($attributes = [], $table = null)
    {
        parent::__construct($attributes);
        $this->table = $table;
    }

    protected $guarded = [];
}