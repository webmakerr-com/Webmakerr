<?php

namespace Webmakerr\App\Modules\Shipping\Http\Controllers;

use Webmakerr\App\App;
use Webmakerr\App\Http\Controllers\Controller;
use Webmakerr\App\Models\ShippingZone;
use Webmakerr\App\Models\ShippingMethod;
use Webmakerr\App\Modules\Shipping\Services\Filter\ShippingZoneFilter;
use Webmakerr\App\Services\Localization\LocalizationManager;
use Webmakerr\Framework\Http\Request\Request;
use Webmakerr\Framework\Support\Arr;
use Webmakerr\App\Modules\Shipping\Http\Requests\ShippingMethodRequest;
use Webmakerr\App\Modules\Shipping\Http\Requests\ShippingZoneRequest;

class ShippingZoneController extends Controller
{
    public function index(Request $request)
    {
        return $this->sendSuccess([
            'shipping_zones' => ShippingZoneFilter::fromRequest($request)->paginate()
        ]);
    }

    public function store(ShippingZoneRequest $request)
    {
        $data = $request->getSafe($request->sanitize());


        $shippingZone = ShippingZone::query()->create($data);

        return $this->sendSuccess([
            'shipping_zone' => $shippingZone,
            'message'       => __('Shipping zone has been created successfully', 'webmakerr-cart')
        ]);
    }

    public function show($id)
    {
        $shippingZone = ShippingZone::with('methods')->findOrFail($id);

        return $this->sendSuccess([
            'shipping_zone' => $shippingZone
        ]);
    }

    public function update(ShippingZoneRequest $request, $id)
    {
        $data = $request->getSafe($request->sanitize());
        $shippingZone = ShippingZone::query()->findOrFail($id);
        if (Arr::get($data, 'region') !== $shippingZone->region) {
            ShippingMethod::where('zone_id', $id)->update(['states' => []]);
        }
        $shippingZone->update($data);

        return $this->sendSuccess([
            'shipping_zone' => $shippingZone,
            'message'       => __('Shipping zone has been updated successfully', 'webmakerr-cart'),
        ]);
    }

    public function destroy($id)
    {
        $shippingZone = ShippingZone::findOrFail($id);

        // Delete associated shipping methods
        ShippingMethod::where('zone_id', $id)->delete();

        // Delete the shipping zone
        $shippingZone->delete();

        return $this->sendSuccess([
            'message' => __('Shipping zone has been deleted successfully', 'webmakerr-cart')
        ]);
    }

    public function updateOrder(Request $request)
    {
        $zones = $request->get('zones', []);

        if (!$zones || !is_array($zones)) {
            return $this->sendError([
                'message' => __('Invalid data provided', 'webmakerr-cart')
            ]);
        }

        foreach ($zones as $index => $zoneId) {
            ShippingZone::where('id', $zoneId)->update(['order' => $index]);
        }

        return $this->sendSuccess([
            'message' => __('Shipping zones order has been updated', 'webmakerr-cart')
        ]);
    }

    public function getZoneStates(Request $request)
    {
        $country_code = sanitize_text_field(Arr::get($request->all(), 'country_code', ''));

        $countryInfo = LocalizationManager::getCountryInfoFromRequest(null, $country_code);

        return $this->sendSuccess([
            'data' => $countryInfo
        ]);
    }
}