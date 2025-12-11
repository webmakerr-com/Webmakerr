<?php

namespace Webmakerr\App\Http\Requests;

use Webmakerr\App\App;
use Webmakerr\App\Models\Customer;
use Webmakerr\App\Models\User;
use Webmakerr\Framework\Foundation\RequestGuard;

class AttachUserRequest extends RequestGuard
{

    /**
     * @return array
     */
    public function rules(): array
    {
        $userId = absint(App::request()->get('user_id'));
        $user = User::query()->with('customer')->find($userId);


        return [
            'user_id' => [
                'required', 'string', 'maxLength:50',
                function ($attribute, $value) use ($user) {

                    if (empty($user)) {
                        return __('User not found.', 'webmakerr-cart');
                    }

                    if (!empty($user->customer)) {
                        return (__('User already linked to a customer.', 'webmakerr-cart'));
                    }
                    return null;
                }
            ],
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [
            'user_id.required' => esc_html__('User is required.', 'webmakerr-cart'),
            'user_id.exists'   => esc_html__('User not found.', 'webmakerr-cart'),
        ];
    }

    /**
     * @return array
     */
    public function sanitize(): array
    {
        return [
            'user_id' => 'absint',
        ];
    }
}