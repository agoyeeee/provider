<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SsoController extends Controller
{
    private $ssoClientId;
    private $ssoClientSecret;
    private $authorizeURL;
    private $tokenURL;
    private $apiURLBase;
    private $redirectUri;

    public function __construct()
    {
        $this->ssoClientId = config('services.sakti_sso.client_id');
        $this->ssoClientSecret = config('services.sakti_sso.client_secret');
        $this->authorizeURL = config('services.sakti_sso.authorize_url');
        $this->tokenURL = config('services.sakti_sso.token_url');
        $this->apiURLBase = config('services.sakti_sso.api_url_base');
        $this->redirectUri = config('services.sakti_sso.redirect_uri');
    }

    /**
     * Redirect to SSO login page
     */
    public function redirectToProvider(): RedirectResponse
    {
        // Debug: Log konfigurasi SSO
        Log::info('SSO Redirect: Starting SSO redirect', [
            'client_id' => $this->ssoClientId,
            'authorize_url' => $this->authorizeURL,
            'redirect_uri' => $this->redirectUri
        ]);

        // Generate state untuk CSRF protection
        $state = Str::random(32);
        session(['sso_state' => $state]);

        // Pastikan menggunakan parameter yang sama dengan file referensi
        $params = [
            'response_type' => 'code',
            'client_id' => $this->ssoClientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'user', // Ubah dari 'scopes' ke 'scope'
            'state' => $state
        ];

        $authUrl = $this->authorizeURL . '?' . http_build_query($params);

        Log::info('SSO Redirect: Full URL generated', ['url' => $authUrl]);
        Log::info('SSO Redirect: Parameters', $params);

        Log::info('SSO Redirect: Redirecting to', ['url' => $authUrl]);

        // Ensure this is a full page redirect, not an Inertia request
        $response = redirect($authUrl);
        $response->headers->set('X-Inertia-Location', $authUrl);

        return $response;
    }

    /**
     * Handle callback from SSO
     */
    public function handleProviderCallback(Request $request): RedirectResponse
    {
        // Verify state untuk CSRF protection
        if (!$request->has('state') || $request->state !== session('sso_state')) {
            Log::error('SSO: Invalid state parameter');
            return redirect()->route('login')->withErrors(['sso' => 'Invalid state parameter']);
        }

        if (!$request->has('code')) {
            Log::error('SSO: No authorization code received');
            return redirect()->route('login')->withErrors(['sso' => 'Authorization failed']);
        }

        try {
            // Exchange authorization code for access token
            $tokenResponse = $this->getAccessToken($request->code);

            if (!$tokenResponse || !isset($tokenResponse['access_token'])) {
                Log::error('SSO: Failed to get access token', ['response' => $tokenResponse]);
                return redirect()->route('login')->withErrors(['sso' => 'Failed to get access token']);
            }

            // Get user data from SSO API
            $userData = $this->getUserData($tokenResponse['access_token']);

            if (!$userData) {
                Log::error('SSO: Failed to get user data');
                return redirect()->route('login')->withErrors(['sso' => 'Failed to get user data']);
            }

            // Find or create user
            $user = $this->findOrCreateUser($userData);

            // Login user
            Auth::login($user);

            // Clear SSO state
            session()->forget('sso_state');

            return redirect()->intended(route('admin.dashboard'));

        } catch (\Exception $e) {
            Log::error('SSO: Exception during callback', ['error' => $e->getMessage()]);
            return redirect()->route('login')->withErrors(['sso' => 'Authentication failed']);
        }
    }

    /**
     * Get access token from SSO
     */
    private function getAccessToken(string $code): ?array
    {
        try {
            $response = Http::withOptions([
                'verify' => false, // Disable SSL verification (like in original code)
            ])->asForm()->post($this->tokenURL, [
                'grant_type' => 'authorization_code',
                'client_id' => $this->ssoClientId,
                'client_secret' => $this->ssoClientSecret,
                'redirect_uri' => $this->redirectUri,
                'code' => $code
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('SSO: Failed to get access token', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('SSO: Exception getting access token', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get user data from SSO API
     */
    private function getUserData(string $accessToken): ?array
    {
        try {
            $response = Http::withOptions([
                'verify' => false, // Disable SSL verification
            ])->withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
                'User-Agent' => config('app.url')
            ])->get($this->apiURLBase . 'user');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('SSO: Failed to get user data', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('SSO: Exception getting user data', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Find or create user from SSO data
     */
    private function findOrCreateUser(array $userData): User
    {
        // Asumsi struktur data dari SSO, sesuaikan dengan response API sebenarnya
        $email = $userData['email'] ?? null;
        $name = $userData['name'] ?? $userData['username'] ?? 'SSO User';
        $ssoId = $userData['id'] ?? $userData['user_id'] ?? null;

        if (!$email && !$ssoId) {
            throw new \Exception('No email or SSO ID found in user data');
        }

        // Cari user berdasarkan email atau SSO ID
        $user = null;
        if ($email) {
            $user = User::where('email', $email)->first();
        }

        if (!$user && $ssoId) {
            $user = User::where('sso_id', $ssoId)->first();
        }

        if ($user) {
            // Update existing user with SSO data
            $user->update([
                'sso_id' => $ssoId,
                'name' => $name,
                'email' => $email,
                'email_verified_at' => now(), // Auto verify SSO users
            ]);
        } else {
            // Create new user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'sso_id' => $ssoId,
                'password' => bcrypt(Str::random(32)), // Random password for SSO users
                'email_verified_at' => now(), // Auto verify SSO users
            ]);

            event(new Registered($user));
        }

        return $user;
    }

    /**
     * Logout and redirect to SSO logout (optional)
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Optional: redirect to SSO logout URL
        // return redirect($this->ssoLogoutUrl);

        return redirect()->route('landing');
    }
}
