<?php

namespace App\Http\Controllers;

use App\Endpoints\OutbrainAPI;
use App\Jobs\PullCampaign;
use App\Jobs\PullOutbrainCampaign;
use App\Models\Provider;
use App\Models\Tracker;
use App\Models\UserProvider;
use App\Models\UserTracker;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hborras\TwitterAdsSDK\TwitterAds;
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
        return UserProvider::whereHas('provider', function($q) {
            return $q->where('slug', request('provider'));
        })->get();
    }

    /**
     * @return array|mixed
     * @throws GuzzleException
     * @throws TwitterAds\Errors\BadRequest
     * @throws TwitterAds\Errors\Forbidden
     * @throws TwitterAds\Errors\NotAuthorized
     * @throws TwitterAds\Errors\NotFound
     * @throws TwitterAds\Errors\RateLimit
     * @throws TwitterAds\Errors\ServerError
     * @throws TwitterAds\Errors\ServiceUnavailable
     * @throws \Hborras\TwitterAdsSDK\TwitterAdsException
     */
    public function advertisers()
    {
        $data = [];
        $provider = Provider::where('slug', request('provider'))->first();
        $user_info = auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first();
        try {
            $data = $this->getAdvertisers($user_info);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                if ($provider->slug === 'outbrain') {
                    Token::refreshOutbrain($user_info, function() use ($user_info, &$data) {
                        $data = $this->getAdvertisers($user_info);
                    });
                } else {
                    Token::refresh($user_info, function() use ($user_info, &$data) {
                        $data = $this->getAdvertisers($user_info);
                    });
                }
            }
        }

        return $data;
    }

    /**
     * @param $user_info
     * @return array|mixed
     * @throws GuzzleException
     * @throws TwitterAds\Errors\BadRequest
     * @throws TwitterAds\Errors\Forbidden
     * @throws TwitterAds\Errors\NotAuthorized
     * @throws TwitterAds\Errors\NotFound
     * @throws TwitterAds\Errors\RateLimit
     * @throws TwitterAds\Errors\ServerError
     * @throws TwitterAds\Errors\ServiceUnavailable
     * @throws \Hborras\TwitterAdsSDK\TwitterAdsException
     */
    private function getAdvertisers($user_info)
    {
        $data = [];
        switch ($user_info->provider_id) {
            case 1:
                $client = new Client();
                $response = $client->get(env('BASE_URL') . '/v3/rest/advertiser', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $user_info->token
                    ]
                ]);
                $data = json_decode($response->getBody(), true)['response'];
                break;
            case 2:
                $api = new OutbrainAPI($user_info);
                $data = $api->getMarketers()['marketers'];
                break;
            case 3:
                $api_key = env('TWITTER_CLIENT_ID');
                $api_secret = env('TWITTER_CLIENT_SECRET');
                $access_token = $user_info->token;
                $access_token_secret = $user_info->secret_token;
                $api = TwitterAds::init($api_key, $api_secret, $access_token, $access_token_secret, null, env('TWITTER_SANDBOX'));
                $accounts = $api->getAccounts()->getCollection();
                foreach ($accounts as $key => $account) {
                    array_push($data, [
                        'id' => $account->getId(),
                        'advertiserName' => $account->getName()
                    ]);
                }
                break;

            default:
                break;
        }

        return $data;
    }

    public function signUp()
    {
        $data = [];
        $provider = Provider::where('slug', request('provider'))->first();
        $user_info = auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first();
        try {
            $data = $this->signUpAdvertiser($user_info);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($user_info, function() use ($user_info, &$data) {
                    $data = $this->signUpAdvertiser($user_info);
                });
            }
        }

        return $data;
    }

    private function signUpAdvertiser($user_info)
    {
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
            if ($param !== 'outbrain') {
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

            // If there is no tracker
            if ($db_provider->id === Provider::whereSlug('outbrain')->first()->id) {
                $this->pullOutbrainCampaign();
            } else {
                $this->pullCampaign();
            }
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

            if (session('provider_id') === Provider::whereSlug('outbrain')->first()->id) {
                $this->pullOutbrainCampaign();
            } else {
                $this->pullCampaign();
            }

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
        $secret_token = null;
        $refresh_token = null;
        $expires_in = null;
        if (property_exists($provider_user, 'tokenSecret')) {
            $secret_token = $provider_user->tokenSecret;
        }
        if (property_exists($provider_user, 'refreshToken')) {
            $refresh_token = $provider_user->refreshToken;
        }
        if (property_exists($provider_user, 'expiresIn')) {
            $expires_in = $provider_user->expiresIn;
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
            $this->pullCampaign();
            return redirect('account-wizard?step=3');
        }
    }

    private function pullCampaign()
    {
        return PullCampaign::dispatch(auth()->user());
    }

    private function pullOutbrainCampaign()
    {
        return PullOutbrainCampaign::dispatch(auth()->user());
    }
}
