<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use Illuminate\Validation\ValidationException;

class IvaoController extends Controller
{
    /**
     * Handles IVAO OAuth2 callback
     */
    public function handleCallback(Request $request)
    {
        // Validate required parameters
        try {
            $auth_callback = $request->validate([
                'code' => 'required|string',
                'state' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return $this->redirectWithErrorToast('You need to log in to view this page');
        }

        // Verify state for CSRF protection
        $expectedState = Session::pull('ivao_oauth_state');
        if (!$expectedState || $request->state !== $expectedState) {
            return $this->redirectWithErrorToast('Invalid state - CSRF attempt detected');
        }

        try {
            // Get OpenID configuration data
            $openidResponse = Http::get(env('OPENID_URL'));
            if ($openidResponse->failed()) {
                throw new \Exception('Unable to retrieve IVAO OpenID configuration');
            }
            $openidData = $openidResponse->json();

            // Exchange code for access token
            $tokenResponse = Http::asForm()->post($openidData['token_endpoint'], [
                'grant_type' => 'authorization_code',
                'code' => $request->code,
                'client_id' => env('IVAO_CLIENT_ID'),
                'client_secret' => env('IVAO_CLIENT_SECRET'),
                'redirect_uri' => route('auth.ivao.callback'),
            ]);

            if ($tokenResponse->failed()) {
                throw new \Exception('Failed to exchange code for access token');
            }

            $tokenData = $tokenResponse->json();
            
            // Get user data from IVAO API
            $userResponse = Http::withToken($tokenData['access_token'])
                ->get($openidData['userinfo_endpoint']);

            if ($userResponse->failed()) {
                throw new \Exception('Unable to retrieve user data from IVAO');
            }

            $userData = $userResponse->json();
            
            // Create or update user
            $user = $this->createOrUpdateUser($userData);
            
            // Log in the user
            Auth::login($user);
            
            // Success toast using session
            Session::put('session_toast', [
                'type' => 'success',
                'title' => 'Welcome!',
                'description' => 'You have successfully logged in via IVAO.',
                'position'      => 'toast-top toast-end', 
                'icon'          => 'phosphor.heart',
                'css'          => 'alert-success',
                'timeout'       => 5000 ,
                'redirectTo'    => null
            ]);
            
            return redirect()->route(env('IVAO_SSO_SUCCESS_ROUTE_NAME', 'hello'));
            
        } catch (\Exception $e) {
            Log::error('IVAO OAuth error: ' . $e->getMessage());
            return $this->redirectWithErrorToast('Authentication failed. Please try again.');
        }
    }

    /**
     * Generates IVAO authentication URL
     */
    public static function getAuthUrl(): string
    {
        // Get OpenID configuration data
        $openidResponse = Http::get(env('OPENID_URL'));
        if ($openidResponse->failed()) {
            throw new \Exception('Unable to retrieve IVAO OpenID configuration');
        }
        $openidData = $openidResponse->json();

        // Generate and store state for CSRF protection
        $state = Str::random(40);
        Session::put('ivao_oauth_state', $state);
        
        $query = [
            'response_type' => 'code',
            'client_id' => env('IVAO_CLIENT_ID'),
            'scope' => 'profile configuration email',
            'redirect_uri' => route('auth.ivao.callback'),
            'state' => $state,
        ];
        


        return $openidData['authorization_endpoint'] . '?' . http_build_query($query);
    }

    /**
     * Creates or updates a user with IVAO data
     */
    private function createOrUpdateUser(array $userData): User
    {
        $gca = '';
        if (!isset($userData['gcas']) || !is_array($userData['gcas']) || empty($userData['gcas'])) {
            $gca = "No GCA";
        } else {
            $gcaIds = array_filter(array_column($userData['gcas'], 'divisionId'));
            $gca = empty($gcaIds) ? "No GCA" : implode(' ', $gcaIds);
        }

        $staffPositions = '';
        if (isset($userData['userStaffPositions']) && is_array($userData['userStaffPositions'])) {
            $staffIds = array_column($userData['userStaffPositions'], 'id');
            $staffPositions = implode(',', $staffIds);
        }

        // Prepare user data for saving
        $userDataToSave = [
            'vid' => $userData['id'],
            'first_name' => $userData['firstName'],
            'last_name' => $userData['lastName'],
            'email' => $userData['email'],
            'rating_atc' => (int) $userData['rating']['atcRating']['id'],
            'rating_pilot' => (int) $userData['rating']['pilotRating']['id'],
            'gca' => $gca,
            'division' => $userData['divisionId'],
            'country' => $userData['countryId'],
            'staff' => $staffPositions,
        ];

        // Update hours if available
        if (isset($userData['hours']) && is_array($userData['hours'])) {
            foreach ($userData['hours'] as $hour) {
                if ($hour['type'] === 'atc') {
                    $userDataToSave['hours_atc'] = (int) round($hour['hours']/3600);
                } elseif ($hour['type'] === 'pilot') {
                    $userDataToSave['hours_pilot'] = (int) round($hour['hours']/3600);
                }
            }
        }

        // Create or update user
        return User::updateOrCreate(
            ['vid' => $userDataToSave['vid']],
            $userDataToSave
        );
    }


    /**
     * Redirect to home with error toast
     */
    private function redirectWithErrorToast(string $message)
    {
        Session::put('session_toast', [
            'type' => 'error',
            'title' => 'Authentication Error',
            'description' => $message,
            'position'      => 'toast-top toast-end', 
            "icon"          => 'phosphor.heart-break',
            "css"           => 'alert-error',
            "timeout"       => 5000,
            'redirectTo'    => null
        ]);

        return redirect()->route('home');
    }
}