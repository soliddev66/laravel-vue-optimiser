<?php

namespace App\Http\Controllers;

use App\Jobs\PullCampaign;
use App\Models\Provider;
use App\Models\Tracker;
use App\Models\UserProvider;
use App\Models\UserTracker;
use App\Vngodev\Helper;
use App\Jobs\UnlinkTrafficSource;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Artisan;
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

    public function accounts()
    {
        return UserProvider::whereHas('provider', function ($q) {
            return $q->where('slug', request('provider'));
        })->get();
    }

    public function formatedAdvertisers()
    {
        $provider = Provider::where('slug', request('provider'))->first();
        $account = auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first();

        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst(request('provider'));

        $advertisers = (new $adVendorClass())->advertisers();

        if (request('provider') === 'outbrain') {
            $advertisers = $advertisers['marketers'];
        }

        return [
            'accounts' => $account->advertisers,
            'advertisers' => array_map(function ($value) {
                if (isset($value['advertiserName'])) {
                    $value['name'] = $value['advertiserName'];
                }
                if (request('provider') == 'taboola' && isset($value['account_id'])) {
                    $value['id'] = $value['account_id'];
                }

                return $value;
            }, $advertisers)
        ];
    }

    public function advertisers()
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst(request('provider'));
        $remote_advertisers = (new $adVendorClass())->advertisers();
        if (request('provider') == 'outbrain') {
            $remote_advertisers = $remote_advertisers['marketers'];
        }

        $filtered_advertisers = UserProvider::whereHas('provider', function ($q) {
            return $q->where('slug', request('provider'))->where('open_id', request('account'));
        })->first()->advertisers;

        $advertisers = array_filter($remote_advertisers, function ($advertiser) use ($filtered_advertisers) {
            if (request('provider') == 'taboola' && isset($advertiser['account_id'])) {
                $advertiser['id'] = $advertiser['account_id'];
            }
            return in_array($advertiser['id'], $filtered_advertisers);
        });

        return array_values($advertisers);
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

    public function removeTrafficSource()
    {
        // Remove user_provider
        UserProvider::where('provider_id', request('providerId'))->where('open_id', request('openId'))->delete();
        // Remove user_tracker
        UserTracker::where('provider_id', request('providerId'))->where('provider_open_id', request('openId'))->delete();

        UnlinkTrafficSource::dispatch(request('providerId'), request('openId'))->onQueue('medium');

        return response()->json([
            'success' => true
        ]);
    }

    public function trackers()
    {
        return view('accounts.trackers');
    }

    public function removeTracker()
    {
        // Remove user_tracker
        UserTracker::where('provider_id', request('providerId'))->where('open_id', request('openId'))->delete();

        // Restart the queue
        Artisan::call('queue:restart');

        return response()->json([
            'success' => true
        ]);
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
                session()->put('provider_slug', $db_provider->slug);

                // Redirect to Tracker setup
                return redirect('account-wizard?step=2&provider=' . $param);
            }

            return redirect('account-wizard?step=3&provider=' . session('provider_slug') . '&account=' . urlencode(session('provider_open_id')));
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

            return redirect('account-wizard?step=3&provider=' . session('provider_slug') . '&account=' . urlencode(session('provider_open_id')));
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
        $account_name = null;
        $token = null;
        $secret_token = null;
        $refresh_token = null;
        $expires_in = null;
        if (property_exists($provider_user, 'name')) {
            $account_name = $provider_user->name;
        }
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
        $user_provider->account_name = $account_name;
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
            session()->put('provider_slug', $db_provider->slug);

            return redirect('account-wizard?step=2');
        } else {
            return redirect('account-wizard?step=3&provider=' . $db_provider->slug . '&account=' . urlencode($open_id));
        }
    }
}
