<?php

namespace Webmakerr\App\Models\WpModels;

use Webmakerr\App\Models\Concerns\CanSearch;
use Webmakerr\App\Models\Model;

class TermRelationship extends Model
{
    use CanSearch;
    protected $table = 'term_relationships';

    protected $primaryKey = 'term_taxonomy_id';


    public function taxonomy()
    {
        return $this->hasOne(TermTaxonomy::class, 'term_taxonomy_id');
    }

    public function products()
    {
        return $this->hasMany(\Webmakerr\App\Models\Product::class, 'ID', 'object_id');
    }
}
