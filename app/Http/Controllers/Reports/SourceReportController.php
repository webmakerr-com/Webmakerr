<?php

namespace Webmakerr\App\Http\Controllers\Reports;

use Webmakerr\Framework\Http\Request\Request;
use Webmakerr\App\Http\Controllers\Controller;
use Webmakerr\App\Services\Report\ReportHelper;
use Webmakerr\App\Services\Report\SourceReportService;

class SourceReportController extends Controller
{
    protected $params = [
        'orderTypes',
        'paymentStatus',
    ];

    public function index(Request $request): array
    {
        $params = ReportHelper::processParams($request->get('params'), $this->params);

        $service = SourceReportService::make();

        $currentMetrics = $service->getSourceReportData($params);

        $fluctuations = [];

        if ($params['comparePeriod']) {
            $params['startDate'] = $params['comparePeriod'][0];
            $params['endDate'] = $params['comparePeriod'][1];

            $previousMetrics = $service->getSourceReportData($params);

            $fluctuations = $service->calculateFluctuations(
                $currentMetrics, $previousMetrics
            );
        }

        return [
            'sourceReportData' => $currentMetrics,
            'fluctuations'     => $fluctuations,
        ];
    }
}
