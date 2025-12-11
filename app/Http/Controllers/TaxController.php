<?php

namespace Webmakerr\App\Http\Controllers;

use Webmakerr\Framework\Support\Arr;
use Webmakerr\App\Models\OrderTaxRate;
use Webmakerr\App\Services\Filter\TaxFilter;
use Webmakerr\App\Services\DateTime\DateTime;
use Webmakerr\Framework\Http\Request\Request;

class TaxController extends Controller
{
    public function index(Request $request)
    {
        return $this->sendSuccess([
            'taxes' => TaxFilter::fromRequest($request)->paginate(),
        ]);
    }

    public function markAsFiled(Request $request)
    {
        $idsToMark = Arr::get($request->getSafe(['ids.*' => 'intval']), 'ids', []);

        if (empty($idsToMark)) {
            return $this->sendError([
                'message' => __('No IDs provided to mark!', 'webmakerr-cart'),
            ], 400);
        }

        $result = OrderTaxRate::whereIn('id', $idsToMark)
            ->whereNull('filed_at')
            ->update([
                'filed_at' => DateTime::gmtNow(),
            ]);

        if (is_wp_error($result)) {
            return $result;
        }

        return $this->sendSuccess(['message' => __('Taxes marked as filed successfully', 'webmakerr-cart')]);
    }
}