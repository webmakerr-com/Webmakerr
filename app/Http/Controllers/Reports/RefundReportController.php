<?php

namespace Webmakerr\App\Http\Controllers\Reports;

use Webmakerr\App\Helpers\Status;
use Webmakerr\App\Http\Controllers\Controller;
use Webmakerr\App\Services\Report\RefundReportService;
use Webmakerr\Framework\Http\Request\Request;
use Webmakerr\App\Services\DateTime\DateTime;
use Webmakerr\App\Services\Report\ReportHelper;

class RefundReportController extends Controller
{
    protected $params = [
        'orderTypes',
        'paymentStatus',
    ];

    public function getRefundChart(Request $request): array
    {
        $params = ReportHelper::processParams($request->get('params'), $this->params);

        $service = RefundReportService::make();

        $currentMetrics = $service->getRefundData($params);

        $fluctuations = [];
        $previousMetrics = ['grouped' => [], 'summary' => []];

        if ($params['comparePeriod']) {
            $params['startDate'] = $params['comparePeriod'][0];
            $params['endDate'] = $params['comparePeriod'][1];

            $previousMetrics = $service->getRefundData($params);

            $fluctuations = $service->calculateFluctuations(
                $currentMetrics['summary'], $previousMetrics['summary']
            );
        }

        return [
            'summary'         => $currentMetrics['summary'],
            'previousSummary' => $previousMetrics['summary'],
            'chartData'       => $currentMetrics['grouped'],
            'fluctuations'    => $fluctuations,
            'previousMetrics' => $previousMetrics['grouped'],
        ];
    }

    public function getWeeksBetweenRefund(Request $request): array
    {
        $params = ReportHelper::processParams($request->get('params'), $this->params);

        $service = RefundReportService::make();

        return [
            'data' => $service->weeksBetweenRefund($params)
        ];
    }

    public function getRefundDataByGroup(Request $request): array
    {
        $params = ReportHelper::processParams($request->get('params'), $this->params);

        $service = RefundReportService::make();

        return [
            'data' => $service->getRefundDataGroupedBy($params)
        ];
    }
}
