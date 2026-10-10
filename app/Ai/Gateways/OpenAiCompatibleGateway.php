<?php

namespace App\Ai\Gateways;

use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Gateway\OpenAi\OpenAiGateway;
use Laravel\Ai\Providers\Provider;
use Psr\Http\Message\RequestInterface;

class OpenAiCompatibleGateway extends OpenAiGateway
{
    /**
     * Get an HTTP client for the OpenAI API, automatically patching stream parameters.
     */
    protected function client(Provider $provider, ?int $timeout = null): PendingRequest
    {
        return Http::baseUrl($this->baseUrl($provider))
            ->withToken($provider->providerCredentials()['key'])
            ->timeout($timeout ?? 60)
            ->withRequestMiddleware(function (RequestInterface $request) {
                if ($request->getMethod() === 'POST') {
                    $body = (string) $request->getBody();
                    $data = json_decode($body, true);

                    if (is_array($data) && ! array_key_exists('stream', $data)) {
                        $data['stream'] = false;
                        $request = $request->withBody(Utils::streamFor(json_encode($data)));
                    }
                }

                return $request;
            })
            ->throw();
    }
}
