<?php

namespace App\Http\Controllers;

use App\Jobs\PullCampaign;
use App\Models\Provider;
use App\Models\Tracker;
use App\Models\UserProvider;
use App\Models\UserTracker;
use App\Vngodev\Helper;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Token;

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

    public function accounts()
    {
        return UserProvider::whereHas('provider', function ($q) {
            return $q->where('slug', request('provider'));
        })->get();
    }

    public function advertisers()
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst(request('provider'));

        return (new $adVendorClass())->advertisers();
    }

    public function fundingInstruments()
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst(request('provider'));

        return (new $adVendorClass())->fundingInstruments();
    }

    public function adGroupCategories()
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst(request('provider'));

        return (new $adVendorClass())->adGroupCategories();
    }

    public function signUp()
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst(request('provider'));

        return (new $adVendorClass())->signUp();
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
     * @param  $param
     * @return Response|mixed
     * @throws GuzzleException
     */
    public function redirectToProvider($param)
    {
        $db_provider = Provider::where('slug', $param)->first();
        $db_tracker = Tracker::where('slug', $param)->first();
        if ($db_provider) {
            session()->put('use_tracker', request('user_tracker'));

            // If service has auth, which can be adapted to Socialite
            if ($param !== 'outbrain' && $param !== 'taboola') {
                if ($db_provider->scopes) {
                    return Socialite::driver($db_provider->slug)->scopes(json_decode($db_provider->scopes))->redirect();
                }

                return Socialite::driver($db_provider->slug)->redirect();
            }

            if (request('user_tracker')) {
                session()->put('provider_open_id', request('open_id'));
                session()->put('provider_id', $db_provider->id);

                // Redirect to Tracker setup

                return redirect('account-wizard?step=2&provider=' . $param);
            }

            Helper::pullCampaign();

            return redirect('account-wizard?step=3');
        }
        if ($db_tracker) {
            $client = new Client();
            session()->put('api_key', request('api_key'));
            $response = $client->get('http://api.redtrack.io/auth?api_key=' . request('api_key'));

            $tracker_user = json_decode($response->getBody(), true);

            UserTracker::firstOrCreate([
                'user_id' => auth()->id(),
                'tracker_id' => $db_tracker->id,
                'provider_id' => session('provider_id'),
                'provider_open_id' => session('provider_open_id'),
                'open_id' => $tracker_user['id'],
                'api_key' => $tracker_user['api_key'],
                'email' => $tracker_user['email'],
                'name' => $tracker_user['firstname'] . ' ' . $tracker_user['lastname']
            ]);

            Helper::pullCampaign();

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
        $token = null;
        $secret_token = null;
        $refresh_token = null;
        $expires_in = null;
        if (property_exists($provider_user, 'token')) {
            $token = $provider_user->token;
        }
        if (property_exists($provider_user, 'access_token')) {
            $token = $provider_user->access_token;
        }
        if (property_exists($provider_user, 'tokenSecret')) {
            $secret_token = $provider_user->tokenSecret;
        }
        if (property_exists($provider_user, 'tokenSecret')) {
            $secret_token = $provider_user->tokenSecret;
        }
        if (property_exists($provider_user, 'refreshToken')) {
            $refresh_token = $provider_user->refreshToken;
        }
        if (property_exists($provider_user, 'refresh_token')) {
            $refresh_token = $provider_user->refresh_token;
        }
        if (property_exists($provider_user, 'expiresIn')) {
            $expires_in = $provider_user->expiresIn;
        }
        if (property_exists($provider_user, 'expires_in')) {
            $expires_in = $provider_user->expires_in;
        }

        $user_provider = UserProvider::firstOrNew([
            'user_id' => auth()->id(),
            'provider_id' => $db_provider->id,
            'open_id' => $open_id
        ]);
        $user_provider->token = $token;
        $user_provider->secret_token = $secret_token;
        $user_provider->refresh_token = $refresh_token;
        if ($expires_in) {
            $user_provider->expires_in = Carbon::now()->addSeconds($expires_in);
        }
        $user_provider->save();

        if (session('use_tracker')) {
            session()->put('provider_id', $db_provider->id);
            session()->put('provider_open_id', $open_id);

            return redirect('account-wizard?step=2');
        } else {
            Helper::pullCampaign();

            return redirect('account-wizard?step=3');
        }
    }
}
