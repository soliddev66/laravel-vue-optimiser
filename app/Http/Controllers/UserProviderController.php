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
        $outbrainProviderId = Provider::where('slug', 'outbrain')->first()->id;
        $userProvider = UserProvider::where('provider_id', $outbrainProviderId)->where('user_id', Auth::id())->first();

        if (!$userProvider) {
            $client = new Client();
            $response = $client->request('GET', 'https://api.outbrain.com/amplify/v0.1/login', ['auth' => [$request->name, $request->password]]);

            $userProvider = UserProvider::create([
                'open_id' => 'outbrain_' . Carbon::now()->format('Y_m_d_h_i_s'),
                'user_id' => Auth::id(),
                'provider_id' => $outbrainProviderId,
                'token' => json_decode($response->getBody()->getContents(), true)['OB-TOKEN-V1'],
                'expires_in' => Carbon::now()->addDays(30),
            ]);
        }

        return response()->json(['user_provider' => $userProvider]);
    }
}
