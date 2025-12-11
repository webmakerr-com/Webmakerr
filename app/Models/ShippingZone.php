<?php

namespace Webmakerr\App\Models;

use Webmakerr\App\Helpers\AddressHelper;
use Webmakerr\App\Models\Concerns\CanSearch;
use Webmakerr\App\Services\Localization\LocalizationManager;
use Webmakerr\Framework\Database\Orm\Relations\HasMany;

/**
 * Shipping Zone Model - DB Model for Shipping Zones
 *
 * @package Webmakerr\App\Models
 * @version 1.0.0
 */
class ShippingZone extends Model
{
    use CanSearch;

    protected $table = 'fct_shipping_zones';

    protected $appends = ['formatted_region'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'region',
        'order'
    ];

    /**
     * Get the shipping methods for this zone.
     */
    public function methods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class, 'zone_id', 'id')
            ->orderBy('id', 'DESC');
    }

    public function getFormattedRegionAttribute()
    {
        if ($this->region === 'all') {
            return __('Whole World', 'webmakerr-cart');
        }
        if (!empty($this->region)) {
            return AddressHelper::getCountryNameByCode($this->region);
        }
        return '';
    }
}