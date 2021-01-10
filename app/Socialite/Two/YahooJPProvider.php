<?php

namespace App\Socialite\Two;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class YahoojpProvider extends AbstractProvider implements ProviderInterface
{
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
            'response_type' => 'code'
        ];

        if ($this->usesState()) {
            $fields['state'] = $state;
        }

        return array_merge($fields, $this->parameters);
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://backstage.taboola.com/backstage/api/1.0/users/current/account', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    protected function mapUserToObject(array $user)
    {
        dd($user);
        return (new User())->setRaw($user)->map([
            'id' => $user['sub'],
            'name' => $user['name'],
            'email' => $user['email'],
            'birth_year' => $user['birthdate'] ?? '',
            'gender' => $user['gender']
        ]);
    }

    /**
     * Overwrite because basic authentication is required.
     * @param string $code
     * @return mixed
     */
    public function getAccessTokenResponse($code)
    {
        $postKey = (version_compare(ClientInterface::MAJOR_VERSION, '6') === 1) ? 'form_params' : 'body';

        $basic_auth_key = base64_encode($this->clientId . ':' . $this->clientSecret);

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => [
                'Authorization' => 'Basic ' . $basic_auth_key,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            $postKey => $this->getTokenFields($code)
        ]);

        return json_decode($response->getBody(), true);
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
