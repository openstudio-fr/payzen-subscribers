<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit327c2dcd791e98d56e2c6db54cfb8c1f
{
    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'setasign\\Fpdi\\' => 14,
        ),
        'D' => 
        array (
            'DrewM\\MailChimp\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'setasign\\Fpdi\\' => 
        array (
            0 => __DIR__ . '/..' . '/setasign/fpdi/src',
        ),
        'DrewM\\MailChimp\\' => 
        array (
            0 => __DIR__ . '/..' . '/drewm/mailchimp-api/src',
        ),
    );

    public static $fallbackDirsPsr4 = array (
        0 => __DIR__ . '/..' . '/lyracom/rest-php-sdk/src',
    );

    public static $classMap = array (
        'FPDF' => __DIR__ . '/..' . '/setasign/fpdf/fpdf.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit327c2dcd791e98d56e2c6db54cfb8c1f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit327c2dcd791e98d56e2c6db54cfb8c1f::$prefixDirsPsr4;
            $loader->fallbackDirsPsr4 = ComposerStaticInit327c2dcd791e98d56e2c6db54cfb8c1f::$fallbackDirsPsr4;
            $loader->classMap = ComposerStaticInit327c2dcd791e98d56e2c6db54cfb8c1f::$classMap;

        }, null, ClassLoader::class);
    }
}
