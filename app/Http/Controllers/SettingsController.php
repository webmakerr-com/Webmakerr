<?php

namespace Webmakerr\App\Http\Controllers;

use Webmakerr\App\App;
use Webmakerr\Api\Helper;
use Webmakerr\Api\Confirmation;
use Webmakerr\Api\StoreSettings;
use Webmakerr\Framework\Support\Arr;
use Webmakerr\Framework\Http\Request\Request;
use Webmakerr\App\Helpers\EditorShortCodeHelper;
use Webmakerr\App\Http\Requests\FluentMetaRequest;

class SettingsController extends Controller
{
    public function getStore(Request $request, StoreSettings $storeSettings)
    {
        $data = ($storeSettings->get());

        $tab = $request->get('settings_name');
        $currenttabFields = Arr::get($storeSettings->fields(), 'setting_tabs.schema.' . $tab);

        if (!App::isProActive()) {
            $data['show_email_footer'] = 'yes';
        }

        return [
            'settings' => $data,
            'fields'   => [
                $tab => $currenttabFields
            ]
        ];
    }

    public function saveStore(FluentMetaRequest $request, StoreSettings $storeSettings)
    {
        $storeLogo = $request->get('store_logo');
        if($storeLogo){
            $storeLogo = [
                'id'    => sanitize_text_field(Arr::get($storeLogo, 'id')),
                'url'   => sanitize_url(Arr::get($storeLogo, 'url')),
                'title' => sanitize_text_field(Arr::get($storeLogo, 'title')),
            ];
        }else{
            $storeLogo = '';
        }
        try {
//            $data = $request->getSafe($request->sanitize());
//            $data['store_logo'] = $storeLogo;
//            $data = array_merge(
//                $storeSettings->get(),
//                $data
//            );
            
            $sanitizeMap = $request->sanitize();
            $data = $request->all();
            $sanitizedData = [];


            foreach ($data as $key => $value) {
                $sanitizer = Arr::get($sanitizeMap, $key);
                if (!$sanitizer) {
                    continue;
                }
                $sanitizedData[$key] = call_user_func($sanitizer, $value);
            }

            $data = array_merge(
                $storeSettings->get(),
                $sanitizedData
            );

            // $data = Arr::get($data, 'settings', []);
            // $frontendTheme = $request->get('settings.frontend_theme');
            $frontendTheme = $request->get('frontend_theme');

            // Check if frontendTheme is an array before using it in foreach
            if (is_array($frontendTheme)) {
                foreach ($frontendTheme as $key => $value) {
                    $data['frontend_theme'][$key] = sanitize_hex_color($value);
                }
            }

            $data['require_logged_in'] = 'no';


            $saved = $storeSettings->save($data);


            return $this->sendSuccess([
                'data' => $saved
            ], 423);
        } catch (\Exception $e) {
            $this->sendError([
                'message' => $e->getMessage()
            ], 423);
        }
    }

    public function getConfirmation()
    {
        $confirmations = new Confirmation();
        return array(
            'settings' => $confirmations->get(null, [
                'confirmation_page_id' => (new StoreSettings())->getReceiptPageId()
            ]),
            'fields'   => $confirmations->fields()
        );
    }

    public function saveConfirmation(Request $request)
    {
        try {
            return (new Confirmation())->save($request->settings);
        } catch (\Exception $e) {
            $this->sendError([
                'message' => $e->getMessage()
            ], 423);
        }
    }

    public function getShortcode(): array
    {
        return [
            'data' => EditorShortCodeHelper::getEmailNotificationShortcodes()
        ];
    }

    public function getPermissions()
    {
        try {
            $roles = Helper::getPermittedRoles();
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage()
            ], 423);
        }

        return array('roles' => $roles);
    }

    public function savePermissions(Request $request)
    {
        try {
            return Helper::savePermissions($request->capability);
        } catch (\Exception $e) {
            $this->sendError([
                'message' => $e->getMessage()
            ], 423);
        }
    }
}
