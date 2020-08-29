<?php

/**
 * Get Zimbabwean iso code
 */
function zimrate_get_iso()
{
    return apply_filters('zimrate-iso', 'ZWL');
}

/**
 * Get Zimbabwean iso codes
 */
function zimrate_get_isos()
{
    return apply_filters('zimrate-isos', [zimrate_get_iso(), 'ZWE', 'ZWD']);
}

/**
 * Get exchange rates
 */
function zimrate_get_rates($currency = false)
{
    $key = 'zimrate' . ($currency ? '-' . $currency : '');

    $rates = get_transient($key);

    if ($rates === false) {
        $url = 'http://zimrate.tyganeutronics.com/api/v1';

        $args = [
            'method' => 'POST',
            'body' => [
                'prefer' => get_option('zimrate-prefer', 'mean'),
                'currency' => $currency ? $currency : '',
            ],
        ];

        $response = wp_remote_post($url, $args);

        $rates = apply_filters(
            'zimrate-rates',
            json_decode($response['body'], true)
        );

        set_transient(
            $key,
            $rates,
            get_option('zimrate-interval', HOUR_IN_SECONDS)
        );
    }

    return $rates;
}

//get exchange rate for currency
/**
 * @param string $currency
 */
function zimrate_get_rate($currency = false)
{
    $rates = zimrate_get_rates($currency ?: zimrate_get_selected_currency());

    return $rates['USD'][0]['rate'] ?: 1;
}

//check if woo multi currency is active
function zimrate_woo_multi_currency_active()
{
    return zimrate_plugin_active('woo-multi-currency/woo-multi-currency.php');
}

/**
 * Check if a plugin is active
 *
 * @param string $plugin
 */
function zimrate_plugin_active($plugin)
{
    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    return is_plugin_active($plugin);
}

/**
 * Get list of known supported plugins
 */
function zimrate_supported_plugins()
{
    return apply_filters('zimrate-plugins', [
        'woo-multi-currency/woo-multi-currency.php' => [
            'name' => 'Multi Currency for WooCommerce',
            'tested' => '2.1.5.6 ',
        ],
        'wc-multi-currency/wcmilticurrency.php' => [
            'name' => 'Multi Currency for WooCommerce',
            'tested' => '1.5 ',
        ],
        'currencyconverter/plugin.php' => [
            'name' => 'CurrencyConverter',
            'tested' => '0.5.3',
        ],
        'currency-switcher-woocommerce/currency-switcher-woocommerce.php' => [
            'name' => 'Currency Switcher for WooCommerce',
            'tested' => '2.12.3',
        ],
        'currency-exchange-for-woocommerce/woocommerce-currency-exchange.php' => [
            'name' => 'Currency Exchange for WooCommerce',
            'tested' => '3.5.1.5',
        ],
    ]);
}

/**
 * Get list of currencies we will be directly supporting
 */
function zimrate_supported_currencies()
{
    return apply_filters('zimrate-currencies', [
        'BOND' => __('Bond Note Rate', 'zimrate'),
        'OMIR' => __('Old Mutual Implied Rate', 'zimrate'),
        'RBZ' => __('Reserve Bank Rate', 'zimrate'),
        'RTGS' => __('Real Time Gross Settlement Rate', 'zimrate'),
    ]);
}

/**
 * Get zimrate intervals array
 */
function zimrate_intervals()
{
    return apply_filters('zimrate-intervals', [
        MINUTE_IN_SECONDS => __('Minutely', 'zimrate'),
        MINUTE_IN_SECONDS * 30 => __('Twice Hourly', 'zimrate'),
        HOUR_IN_SECONDS => __('Hourly', 'zimrate'),
        HOUR_IN_SECONDS * 2 => __('Two Hours', 'zimrate'),
        HOUR_IN_SECONDS * 6 => __('Six Hours', 'zimrate'),
        HOUR_IN_SECONDS * 12 => __('Twice Daily', 'zimrate'),
        DAY_IN_SECONDS => __('Daily', 'zimrate'),
        DAY_IN_SECONDS * 2 => __('Two Days', 'zimrate'),
        WEEK_IN_SECONDS => __('Weekly', 'zimrate'),
    ]);
}

/**
 * get host from url
 *
 * @param string $url
 */
function zimrate_url_host($url)
{
    return parse_url($url)['host'];
}

/**
 * Get parameters from url
 *
 * @param  string   $url
 * @return string
 */
function zimrate_url_params($url)
{
    $params = [];

    parse_str(parse_url($url)['query'], $params);

    return $params;
}

function zimrate_get_selected_currency()
{
    return get_option('zimrate-currencies', 'RBZ');
}