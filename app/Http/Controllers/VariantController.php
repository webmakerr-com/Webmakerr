<?php

namespace Webmakerr\App\Http\Controllers;

use Webmakerr\App\Models\ProductVariation;
use Webmakerr\Framework\Http\Request\Request;

class VariantController extends Controller
{
    public function index(Request $request): array
    {
        $parameters = $request->get('params');
        return ProductVariation::search([])->get()->toArray();
    }
}
