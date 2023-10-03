# lumen-baidutongji

>请参考百度统计文档使用,本项目适用lumen和laravel集成包
## 安装

1. 安装包文件

  ```shell
  composer require Bruin/baidu-tongji
  ```

## 配置

### lumen 应用

1. 注册 `AppServiceProvider`:

  ```php
  $this->app->register(\Bruin\BaiduTongji\BaiduTongjiServiceProvider::class);
  ```

2. 创建配置文件：

  ```php
  public function boot()
  {
      $this->app->configure('baidu_tongji');
  }

  ```

3. 复制应用根目录下的 `config/baidu_tongji.php` ；


### laravel 应用

1. 注册 `ServiceProvider`:

  ```php
  Bruin\BaiduTongji\BaiduTongjiServiceProvider::class
  ```

2. 创建配置文件：

  ```php
  php artisan vendor:publish

  ```

3. 修改应用根目录下的  `config/baidu_tongji.php`


## 使用 lumen

```php
<?php

namespace App\Http\Controllers;

use Bruin\BaiduTongji\BaiduTongji;

class SiteController extends Controller
{

    public function index()
    {
        $today=date('Ymd');
        $yesterday=date('Ymd',strtotime('yesterday'));
        $baiduTongji = new BaiduTongji(config('baidu_tongji'));
        $result=$baiduTongji->getData([
            'method' => 'trend/time/a',
            'start_date' => $today,
            'end_date' => $today,
            'start_date2' => $yesterday,
            'end_date2' => $yesterday,
            'metrics' => 'pv_count,visitor_count',
            'max_results' => 0,
            'gran' => 'day',
        ]);
        dd($result);
    }
}
```



## 使用 laravel

```php
<?php

namespace App\Http\Controllers;

class SiteController extends Controller
{

    public function index()
    {
        $today=date('Ymd');
        $yesterday=date('Ymd',strtotime('yesterday'));
        $baiduTongji = resolve('BaiduTongji');
        $result=$baiduTongji->getData([
            'method' => 'trend/time/a',
            'start_date' => $today,
            'end_date' => $today,
            'start_date2' => $yesterday,
            'end_date2' => $yesterday,
            'metrics' => 'pv_count,visitor_count',
            'max_results' => 0,
            'gran' => 'day',
        ]);
        dd($result);
    }
}
```


