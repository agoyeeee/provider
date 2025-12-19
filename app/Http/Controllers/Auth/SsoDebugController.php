<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SsoDebugController extends Controller
{
    public function debugConfig()
    {
        $config = [
            'client_id' => config('services.sakti_sso.client_id'),
            'client_secret' => substr(config('services.sakti_sso.client_secret'), 0, 5) . '...',
            'authorize_url' => config('services.sakti_sso.authorize_url'),
            'token_url' => config('services.sakti_sso.token_url'),
            'api_url_base' => config('services.sakti_sso.api_url_base'),
            'redirect_uri' => config('services.sakti_sso.redirect_uri'),
        ];

        return response()->json([
            'message' => 'SSO Configuration Debug',
            'config' => $config,
            'url_params_example' => [
                'response_type' => 'code',
                'client_id' => $config['client_id'],
                'redirect_uri' => $config['redirect_uri'],
                'scopes' => 'user',
                'state' => 'test_state_123'
            ]
        ]);
    }

    public function testRedirectUrl()
    {
        $state = 'test_state_' . time();

        // Test dengan 'scope' (singular)
        $params1 = [
            'response_type' => 'code',
            'client_id' => config('services.sakti_sso.client_id'),
            'redirect_uri' => config('services.sakti_sso.redirect_uri'),
            'scope' => 'user',
            'state' => $state
        ];

        // Test dengan 'scopes' (plural)
        $params2 = [
            'response_type' => 'code',
            'client_id' => config('services.sakti_sso.client_id'),
            'redirect_uri' => config('services.sakti_sso.redirect_uri'),
            'scopes' => 'user',
            'state' => $state
        ];

        $authUrl1 = config('services.sakti_sso.authorize_url') . '?' . http_build_query($params1);
        $authUrl2 = config('services.sakti_sso.authorize_url') . '?' . http_build_query($params2);

        return response()->json([
            'message' => 'Test SSO Redirect URLs',
            'version_1_scope_singular' => [
                'url' => $authUrl1,
                'parameters' => $params1
            ],
            'version_2_scopes_plural' => [
                'url' => $authUrl2,
                'parameters' => $params2
            ],
            'instructions' => 'Test both URLs manually in browser to see which one works'
        ]);
    }
}
