<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserProvider\StoreUserProviderRequest;
use App\Models\Provider;
use App\Models\UserProvider;
use Carbon\Carbon;
use \GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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
        $outbrain_provider_id = Provider::where('slug', 'outbrain')->first()->id;
        $user_provider = UserProvider::where('provider_id', $outbrain_provider_id)->where('user_id', Auth::id())->firstOrNew([
            'open_id' => 'outbrain_' . Carbon::now()->format('Y_m_d_h_i_s'),
            'user_id' => Auth::id(),
            'provider_id' => $outbrain_provider_id,
            'expires_in' => Carbon::now()->addDays(30),
        ]);

        if (!$user_provider->exists) {
            $client = new Client();
            $response = $client->request('GET', config('services.outbrain.api_endpoint') . '/amplify/v0.1/login', ['auth' => [
                $request->name, $request->password
            ]]);

            // Amplify token, valid for 30 days
            $user_provider->token = json_decode($response->getBody()->getContents(), true)['OB-TOKEN-V1'];
        }

        $user_provider->save();
        return response()->json(['user_provider' => $user_provider]);
    }
}
