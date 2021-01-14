<?php

namespace App\Socialite\Two;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class YahooJPProvider extends AbstractProvider implements ProviderInterface
{
    public $user = [];

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://biz-oauth.yahoo.co.jp/oauth/v1/authorize', $state);
    }

    protected function getTokenUrl()
    {
        return 'https://biz-oauth.yahoo.co.jp/oauth/v1/token';
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param  string|null  $state
     * @return array
     */
    protected function getCodeFields($state = null)
    {
        $fields = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'scope' => 'yahooads',
            'response_type' => 'code'
        ];

        if ($this->usesState()) {
            $fields['state'] = $state;
        }

        return array_merge($fields, $this->parameters);
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->post('https://ads-search.yahooapis.jp/api/v3/AccountService/get', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'startIndex' => 1
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    protected function mapUserToObject(array $user)
    {
        $user = array_merge($this->user, $user['rval']);
        return (new User())->setRaw($user)->map([
            'id' => $user['authorizationBusinessId'],
            'name' => $user['authorizationBusinessId'],
            'email' => $user['authorizationBusinessId'],
            'birth_year' => $user['birthdate'] ?? '',
            'gender' => $user['gender'] ?? ''
        ]);
    }

    /**
     * Overwrite because basic authentication is required.
     * @param string $code
     * @return mixed
     */
    public function getAccessTokenResponse($code)
    {
        $response = $this->getHttpClient()->get($this->getTokenUrl(), [
            'query' => [
                'grant_type' => 'authorization_code',
                'client_id' => env('YAHOOJP_CLIENT_ID'),
                'client_secret' => env('YAHOOJP_CLIENT_SECRET'),
                'redirect_uri' => env('YAHOOJP_REDIRECT'),
                'code' => $code
            ]
        ]);

        $user_token = json_decode($response->getBody(), true);
        $this->user = $user_token;

        return $user_token;
    }

    /**
     * OverWrite because there is an excess or deficiency in Token Fields.
     *: Not required because of Basic authentication
     *    - client_id
     *    - client_secret
     *: Required items to be added
     *    + grant_type
     * @param string $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        return [
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
            'grant_type' => 'authorization_code'
        ];
    }
}
