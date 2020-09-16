<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\Tracker;
use App\Models\UserProvider;
use App\Models\UserTracker;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function wizard()
    {
        $providers = Provider::all();
        $trackers = Tracker::all();
        return view('accounts.wizard', compact('providers', 'trackers'));
    }

    public function advertisers()
    {
        $provider = Provider::where('slug', request('provider'))->first();
        $user_info = auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first();
        try {
            $client = new Client();
            $response = $client->get(env('BASE_URL') . '/v3/rest/advertiser', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $user_info->token
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (Exception $e) {
            dd($e);
        }
    }

    public function signUp()
    {
        $provider = Provider::where('slug', request('provider'))->first();
        $user_info = auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first();
        try {
            $client = new Client();
            $response = $client->request('POST', env('BASE_URL') . '/v3/rest/advertisersignup', [
                'body' => json_encode([
                    'advertiserName' => request('name')
                ]),
                'headers' => [
                    'Authorization' => 'Bearer ' . $user_info->token,
                    'Content-Type' => 'application/json'
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (Exception $e) {
            dd($e);
        }
    }

    public function trafficSources()
    {
        return view('accounts.traffic-sources');
    }

    public function trackers()
    {
        return view('accounts.trackers');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($param)
    {
        $db_provider = Provider::where('slug', $param)->first();
        $db_tracker = Tracker::where('slug', $param)->first();
        if ($db_provider) {
            session()->put('use_tracker', request('user_tracker'));
            return Socialite::driver($db_provider->slug)->scopes(json_decode($db_provider->scopes))->redirect();
        }
        if ($db_tracker) {
            $client = new Client();
            session()->put('api_key', request('api_key'));
            $response = $client->get('http://api.redtrack.io/auth?api_key=' . request('api_key'));

            $tracker_user = json_decode($response->getBody(), true);

            UserTracker::firstOrCreate([
                'user_id' => auth()->id(),
                'tracker_id' => $db_tracker->id,
                'open_id' => $tracker_user['id'],
                'api_key' => $tracker_user['api_key'],
                'email' => $tracker_user['email'],
                'name' => $tracker_user['firstname'] . ' ' . $tracker_user['lastname']
            ]);

            return redirect('account-wizard?step=3');
        }

        abort(404);
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider)
    {
        $db_provider = Provider::where('slug', $provider)->first();
        $provider_user = Socialite::driver($provider)->user();
        $open_id = $provider_user->id;
        $token = $provider_user->token;
        $refresh_token = $provider_user->refreshToken;
        $expires_in = $provider_user->expiresIn;

        $user_provider = UserProvider::firstOrNew([
            'user_id' => auth()->id(),
            'provider_id' => $db_provider->id,
            'open_id' => $open_id
        ]);
        $user_provider->token = $token;
        $user_provider->refresh_token = $refresh_token;
        $user_provider->expires_in = Carbon::now()->addSeconds($expires_in);
        $user_provider->save();

        if (session('use_tracker')) {
            return redirect('account-wizard?step=2');
        } else {
            return redirect('account-wizard?step=3');
        }
    }
}
