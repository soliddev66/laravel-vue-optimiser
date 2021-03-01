<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserProvider\StoreUserProviderRequest;
use App\Models\Provider;
use App\Models\UserProvider;
use App\Vngodev\Helper;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use \GuzzleHttp\Exception\GuzzleException;

class UserProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param  StoreUserProviderRequest  $request
     * @throws GuzzleException
     * @return JsonResponse
     */
    public function store(StoreUserProviderRequest $request)
    {
        $client = new Client();
        if (request('provider') === 'outbrain') {
            $response = $client->request('GET', config('services.outbrain.api_endpoint') . '/amplify/v0.1/login', ['auth' => [
                $request->name, $request->password
            ]]);

            $outbrain_provider_id = Provider::where('slug', 'outbrain')->first()->id;
            $open_id = $request->name;
            $user_provider = UserProvider::firstOrNew([
                'open_id' => $open_id,
                'user_id' => Auth::id(),
                'provider_id' => $outbrain_provider_id
            ]);
            $user_provider->account_name = $request->name;
            $user_provider->basic_auth = base64_encode($request->name . ':' . $request->password);
            $user_provider->token = json_decode($response->getBody()->getContents(), true)['OB-TOKEN-V1'];
            $user_provider->expires_in = Carbon::now()->addDays(30);

            $user_provider->save();
        }

        if (request('provider') === 'taboola') {
            $response = $client->request('POST', config('services.taboola.api_endpoint') . '/backstage/oauth/token', [
                'form_params' => [
                    'client_id' => config('services.taboola.client_id'),
                    'client_secret' => config('services.taboola.client_secret'),
                    'username' => $request->name,
                    'password' => $request->password,
                    'grant_type' => 'password'
                ]
            ]);
            $oauth_data = json_decode($response->getBody()->getContents(), true);
            // Get account details
            $response = $client->request('GET', config('services.taboola.api_endpoint') . '/backstage/api/1.0/users/current/account', ['headers' => [
                'Authorization' => 'Bearer ' . $oauth_data['access_token'],
                'Content-Type' => 'application/json'
            ]]);
            $account_data = json_decode($response->getBody()->getContents(), true);

            $user_provider = UserProvider::firstOrNew([
                'user_id' => auth()->id(),
                'provider_id' => Provider::where('slug', 'taboola')->first()->id,
                'open_id' => $account_data['account_id']
            ]);
            $user_provider->account_name = $request->name;
            $user_provider->token = $oauth_data['access_token'];
            $user_provider->refresh_token = $oauth_data['refresh_token'];
            $user_provider->expires_in = Carbon::now()->addSeconds($oauth_data['expires_in']);
            $user_provider->save();
        }

        return response()->json(['user_provider' => $user_provider]);
    }

    public function update()
    {
        $provider = Provider::where('slug', request('provider'))->first();
        $user_provider = UserProvider::where('user_id', auth()->id())->where('provider_id', $provider->id)->where('open_id', request('account'))->first();
        $user_provider->advertisers = request('advertisers');
        $user_provider->save();

        Helper::pullCampaign();

        return response()->json(['user_provider' => $user_provider]);
    }
}
