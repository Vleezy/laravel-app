<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class FindRetrosService
{
    /**
     * The findretros verification uri.
     */
    const FIND_RETROS_VERIFY_URI = '%s/validate.php?user=%s&ip=%s';

    /**
     * The findretros redirect uri.
     */
    const FIND_RETROS_REDIRECT_URI = '%s/servers/%s/vote?minimal=1&return=1';

    const FIND_RETROS_CACHE_KEY = 'voted.%s';

    /**
     * The guzzle client instance
     *
     * @var Client
     */
    protected Client $client;

    /**
     * Initialise Find Retros Service
     */
    public function __construct()
    {
        $this->client = new Client;
    }

    /**
     * Check the user has voted.
     * 
     * @return boolean
     */
    public function checkHasVoted(): bool
    {
        $cacheKey = sprintf(self::FIND_RETROS_CACHE_KEY, request()->ip());
        if (request()->ip() === '127.0.0.1') return true;
        if (request()->has('novote')) return true;
        if (Cache::has($cacheKey)) return true;
        $uri = sprintf(self::FIND_RETROS_VERIFY_URI, config('habbo.findretros.api'), config('habbo.findretros.name'), request()->ip());
        $request = $this->client->get($uri);
        $response = $request->getBody()->getContents();
        if (in_array($response, [1, 2])) {
            Cache::put($cacheKey, true, 86400);
            return true;
        }
        return false;
    }

    /**
     * Retrieve the find retros redirect url.
     * 
     * @return string
     */
    public function getRedirectUri(): string
    {
        return sprintf(self::FIND_RETROS_REDIRECT_URI, config('habbo.findretros.api'), config('habbo.findretros.name'));
    }
}
