<?php
/**
 * Created by PhpStorm.
 * User: Bruin
 * Date: 2016/11/23
 * Time: 16:57
 */
namespace Bruin\BaiduTongji;

use Illuminate\Support\ServiceProvider;
use Bruin\BaiduTongji\BaiduTongji;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;

class BaiduTongjiServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $source = __DIR__ . '/config.php';

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('baidu_tongji.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('baidu_tongji');
        }
        $this->mergeConfigFrom($source, 'baidu_tongji');
    }

    public function register()
    {
        $this->app->singleton('BaiduTongji', function ($app) {
            return new BaiduTongji(config('baidu_tongji'));
        });
    }
}