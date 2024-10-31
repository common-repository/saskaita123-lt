<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit44f2fb3051f438072b2319670b5a4cb2
{
    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'Includes\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Includes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'S123\\Includes\\Base\\S123_Activate' => __DIR__ . '/../..' . '/includes/base/S123_Activate.php',
        'S123\\Includes\\Base\\S123_BaseController' => __DIR__ . '/../..' . '/includes/base/S123_BaseController.php',
        'S123\\Includes\\Base\\S123_Deactivate' => __DIR__ . '/../..' . '/includes/base/S123_Deactivate.php',
        'S123\\Includes\\Base\\S123_Enqueue' => __DIR__ . '/../..' . '/includes/base/S123_Enqueue.php',
        'S123\\Includes\\Base\\S123_I18n' => __DIR__ . '/../..' . '/includes/base/S123_I18n.php',
        'S123\\Includes\\Base\\S123_Options' => __DIR__ . '/../..' . '/includes/base/S123_Options.php',
        'S123\\Includes\\Helpers\\S123_ResponseHelpers' => __DIR__ . '/../..' . '/includes/helpers/S123_ResponseHelpers.php',
        'S123\\Includes\\Pages\\S123_ApiKey' => __DIR__ . '/../..' . '/includes/pages/S123_ApiKey.php',
        'S123\\Includes\\Pages\\S123_Checkout' => __DIR__ . '/../..' . '/includes/pages/S123_Checkout.php',
        'S123\\Includes\\Pages\\S123_InvoiceSettings' => __DIR__ . '/../..' . '/includes/pages/S123_InvoiceSettings.php',
        'S123\\Includes\\Pages\\S123_Settings' => __DIR__ . '/../..' . '/includes/pages/S123_Settings.php',
        'S123\\Includes\\Requests\\S123_ApiRequest' => __DIR__ . '/../..' . '/includes/requests/S123_ApiRequest.php',
        'S123\\Includes\\S123_Init' => __DIR__ . '/../..' . '/includes/S123_Init.php',
        'S123\\Includes\\Woocommerce\\I123_OrderEmail' => __DIR__ . '/../..' . '/includes/woocommerce/I123_OrderEmail.php',
        'S123\\Includes\\Woocommerce\\I123_Warehouse' => __DIR__ . '/../..' . '/includes/woocommerce/I123_Warehouse.php',
        'S123\\Includes\\Woocommerce\\S123_Invoice' => __DIR__ . '/../..' . '/includes/woocommerce/S123_Invoice.php',
        'S123\\Includes\\Woocommerce\\S123_Product' => __DIR__ . '/../..' . '/includes/woocommerce/S123_Product.php',
        'S123\\Includes\\Woocommerce\\WC_Invoice123_Generated_Email' => __DIR__ . '/../..' . '/includes/woocommerce/WC_Invoice123_Generated_Email.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit44f2fb3051f438072b2319670b5a4cb2::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit44f2fb3051f438072b2319670b5a4cb2::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit44f2fb3051f438072b2319670b5a4cb2::$classMap;

        }, null, ClassLoader::class);
    }
}
