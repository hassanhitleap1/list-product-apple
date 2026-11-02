<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Styles and script for the trade-in price calculator (site/index).
 */
class TradeInAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'https://fonts.googleapis.com/css2?family=Readex+Pro:wght@300;400;500;600;700&display=swap',
        'css/trade-in.css',
    ];
    public $js = [
        'js/trade-in.js',
    ];
    public $depends = [
        AppAsset::class,
    ];
}
