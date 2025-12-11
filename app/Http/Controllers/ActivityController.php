<?php

namespace Webmakerr\App\Http\Controllers;

use Webmakerr\Api\Resource\ActivityResource;
use Webmakerr\Framework\Http\Request\Request;
use Webmakerr\App\Services\Filter\LogFilter;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        return $this->sendSuccess([
            'activities' => LogFilter::fromRequest($request)
                ->paginate()
        ]);
    }
    public function delete($id)
    {
        $response = ActivityResource::delete($id);
       if (is_wp_error($response)) {
           return $this->sendError([
               'message'=> $response->get_error_message()
           ]);
       }
        return $this->sendSuccess([
            'message' => __('Activity Deleted Successfully', 'webmakerr-cart')
        ]);
    }

    public function markReadUnread($id, Request $request): \WP_REST_Response
    {
        $status = $request->getSafe('status', 'sanitize_text_field');

        $response = ActivityResource::update(['read_status' => $status], $id);
        if (is_wp_error($response)) {
            return $this->sendError([
                'message'=> $response->get_error_message()
            ]);
        }
        if ($status === 'read'){
            return $this->sendSuccess([
                'message' => __('Activity Marked as Read', 'webmakerr-cart')
            ]);
        }else{
            return $this->sendSuccess([
                'message' => __('Activity Marked as Unread', 'webmakerr-cart')
            ]);
        }

    }

}