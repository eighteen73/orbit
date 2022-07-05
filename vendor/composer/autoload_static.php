<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd10f5f4a965f74944b04cb8349630a9d
{
    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'Eighteen73\\Orbit\\' => 17,
        ),
        'D' => 
        array (
            'Dealerdirect\\Composer\\Plugin\\Installers\\PHPCodeSniffer\\' => 55,
        ),
        'C' => 
        array (
            'Carbon_Fields\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Eighteen73\\Orbit\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes/classes',
        ),
        'Dealerdirect\\Composer\\Plugin\\Installers\\PHPCodeSniffer\\' => 
        array (
            0 => __DIR__ . '/..' . '/dealerdirect/phpcodesniffer-composer-installer/src',
        ),
        'Carbon_Fields\\' => 
        array (
            0 => __DIR__ . '/..' . '/htmlburger/carbon-fields/core',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd10f5f4a965f74944b04cb8349630a9d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd10f5f4a965f74944b04cb8349630a9d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitd10f5f4a965f74944b04cb8349630a9d::$classMap;

        }, null, ClassLoader::class);
    }
}
