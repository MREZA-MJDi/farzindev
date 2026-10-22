<?php

namespace App\Services\Payments;

use App\Services\Payments\Contracts\InstallmentPaymentGatewayInterface;
use App\Services\Payments\Gateways\DigiPayGateway;
use App\Services\Payments\Gateways\SnapPayGateway;
use App\Services\Payments\Gateways\TorobPayGateway;
use InvalidArgumentException;

class PaymentManager
{
    /**
     * Provider adapters listed here must implement the provider's actual
     * signed/authenticated merchant API contract and verified callback flow.
     *
     * The current gateway classes still use generic placeholder payloads, so
     * no provider is eligible for live charging yet. Credentials and an
     * environment flag must never be enough to expose a fake payment flow.
     */
    private const LIVE_API_ADAPTERS = [];

    /**
     * A provider is live only after the official API contract has been
     * mapped, enabled, and supplied with secure connection settings.
     * Placeholder endpoints alone must never enable charging.
     */
    public function isReady(string $gateway): bool
    {
        $gateway = strtolower($gateway);
        if (! in_array($gateway, self::LIVE_API_ADAPTERS, true)) {
            return false;
        }

        $config = config('payment.' . $gateway, []);
        $baseUrl = (string) ($config['base_url'] ?? '');
        $callbackUrl = (string) ($config['callback_url'] ?? '');
        $base = parse_url($baseUrl);
        $callback = parse_url($callbackUrl);
        $endpoints = $config['endpoints'] ?? [];
        $credentialsReady = filled($config['merchant_id'] ?? null)
            && (filled($config['api_key'] ?? null) || filled($config['access_token'] ?? null));

        return ($config['enabled'] ?? false) === true
            && ($config['api_contract_confirmed'] ?? false) === true
            && is_array($base)
            && strtolower((string) ($base['scheme'] ?? '')) === 'https'
            && filled($base['host'] ?? null)
            && filter_var($baseUrl, FILTER_VALIDATE_URL) !== false
            && is_array($callback)
            && strtolower((string) ($callback['scheme'] ?? '')) === 'https'
            && filled($callback['host'] ?? null)
            && filter_var($callbackUrl, FILTER_VALIDATE_URL) !== false
            && filled($endpoints['create'] ?? null)
            && filled($endpoints['verify'] ?? null)
            && $credentialsReady;
    }

    /**
     * Resolve an installment payment gateway.
     */
    public function driver(
        string $gateway
    ): InstallmentPaymentGatewayInterface {
        return match (strtolower($gateway)) {
            'digipay' =>
            app(DigiPayGateway::class),

            'snappay' =>
            app(SnapPayGateway::class),

            'torobpay' =>
            app(TorobPayGateway::class),

            default =>
            throw new InvalidArgumentException(
                "Unsupported payment gateway [{$gateway}]."
            ),
        };
    }
}
