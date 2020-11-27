<?php

namespace App\Http\Controllers;

use App\Endpoints\OutbrainAPI;
use App\Models\Provider;
use Exception;
use GuzzleHttp\Client;
use Hborras\TwitterAdsSDK\TwitterAds;
use Illuminate\Http\Request;
use Token;

class GeneralController extends Controller
{
    public function languages()
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst(request('provider'));

        return (new $adVendorClass)->languages();
    }

    public function countries()
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst(request('provider'));

        return (new $adVendorClass)->countries();
    }

    public function preview()
    {
        $data = [];
        $provider = Provider::where('slug', request('provider'))->first();
        $user_info = auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first();
        switch ($user_info->provider_id) {
            case 1:
                try {
                    $data = $this->getPreview($user_info);
                } catch (Exception $e) {
                    if ($e->getCode() == 401) {
                        Token::refresh($user_info, function () use ($user_info, &$data) {
                            $data = $this->getPreview($user_info);
                        });
                    }
                }
                break;
            case 2:
                break;
            case 3:
                break;
        }

        return $data;
    }

    private function getPreview($user_info)
    {
        $client = new Client();
        $response = $client->request('POST', env('MAIN_URL') . '/v3/rest/preview', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'settings' => [
                    'displayType' => 'handheld',
                    'responseType' => 'iframe'
                ],
                'ad' => [
                    'description' => request('description'),
                    'displayUrl' => request('displayUrl'),
                    'imageUrlHQ' => request('imageUrlHQ'),
                    'title' => request('title'),
                    'sponsoredBy' => request('sponsoredBy'),
                    'imageUrl' => request('imageUrl'),
                    'landingUrl' => request('landingUrl')
                ],
                'campaign' => [
                    'objective' => 'VISIT_WEB',
                    'language' => request('campaignLanguage')
                ]
            ])
        ]);

        return $response->getBody()->__toString();
    }

    public function uploadFiles()
    {
        $path = request()->file('file')->store('public/images');

        return response()->json([
            'path' => env('APP_URL') . '/' . $path
        ]);
    }
}
