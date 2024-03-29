# Laravel Summernote

Summernote editor integration for Laravel 5.

![Laravel Octane Ready Status](https://img.shields.io/badge/Octance-ready-green?style=flat-square)
![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/overtrue/laravel-summernote?style=flat-square)
![GitHub License](https://img.shields.io/github/license/overtrue/laravel-summernote?style=flat-square)
![Packagist Downloads](https://img.shields.io/packagist/dt/overtrue/laravel-summernote?style=flat-square)

[![Sponsor me](https://github.com/overtrue/overtrue/blob/master/sponsor-me-button-s.svg?raw=true)](https://github.com/sponsors/overtrue)

# 使用

## 安装

```shell
$ composer require "overtrue/laravel-summernote"
```

## 配置

1. 添加下面一行到 `config/app.php` 中 `providers` 部分：

    ```php
    Overtrue\LaravelSummernote\SummernoteServiceProvider::class,
    ```

2. 发布配置文件与资源

    ```php
    $ php artisan vendor:publish --provider='Overtrue\LaravelSummernote\SummernoteServiceProvider'
    ```

3. 模板引入编辑器

    这行的作用是引入编辑器需要的 css,js 等文件，所以你不需要再手动去引入它们。

    ```php
    @include('vendor.summernote.assets')
    ```

4. 编辑器的初始化

    ```html
    <!-- 实例化编辑器 -->
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#summernote').summernote($.extend(summernoteOptions, {
                height: 300,
            }));
        });
    </script>

    <!-- 编辑器容器 -->
    <div id="summernote">Hello Summernote</div>
    ```

# 说明

1. 如果你使用的是 laravel 5.3 以下版本，请先创建软链接：
    ```shell
    # 请在项目根目录执行以下命令
    $ ln -s `pwd`/storage/app/public `pwd`/public/storage
    ```
1. 在 `config/summernote.php` 配置 `disk` 为 `'public'` 情况下，上传路径在：`public/uploads/` 下，确认该目录存在并可写。
1. 如果要修改上传路径，请在 `config/summernote.php` 里各种类型的上传路径，但是都在 public 下。
1. 请在 `.env` 中正确配置 `APP_URL` 为你的当前域名，否则可能上传成功了，但是无法正确显示。

## 七牛支持

如果你想使用七牛云储存，需要进行下面几个简单的操作：

1.安装和配置 [laravel-filesystem-qiniu](https://github.com/overtrue/laravel-filesystem-qiniu)

2.配置 `config/summernote.php` 的 `disk` 为 `qiniu`:

```php
'disk' => 'qiniu'
```

3.剩下时间打局 LOL，已经完事了。

> 七牛的 `access_key` 和 `secret_key` 可以在这里找到：https://portal.qiniu.com/user/key ,在创建 `bucket` （空间）的时候，推荐大家都使用公开的空间。

## 事件

你肯定有一些朋友肯定会有一些比较特殊的场景，那么你可以使用本插件提供的事件来支持：

> 请按照 Laravel 事件的文档来使用：
> https://laravel.com/docs/5.4/events#registering-events-and-listeners

### 上传中事件

> Overtrue\LaravelSummernote\Events\Uploading

在保存文件之前，你可以拿到一些信息：

- `$event->file` 这是请求的已经上传的文件对象，`Symfony\Component\HttpFoundation\File\UploadedFile` 实例。
- `$event->filename` 这是即将存储时用的新文件名
- `$event->config` 上传配置，数组。

你可以在本事件监听器返回值，返回值将替换 `$filename` 作为存储文件名。

### 上传完成事件

> Overtrue\LaravelSummernote\Events\Uploaded

它有两个属性：

- `$event->file` 与 Uploading 一样，上传的文件
- `$event->result` 上传结构，数组，包含以下信息：

   ```php
   'state' => 'SUCCESS',
   'url' => 'http://xxxxxx.qiniucdn.com/xxx/xxx.jpg',
   'title' => '文件名.jpg',
   'original' => '上传时的源文件名.jpg',
   'type' => 'jpg',
   'size' => 17283,
   ```

你可以监听此事件用于一些后续处理任务，比如记录到数据库。

[![Sponsor me](https://github.com/overtrue/overtrue/blob/master/sponsor-me.svg?raw=true)](https://github.com/sponsors/overtrue)

## Project supported by JetBrains

Many thanks to Jetbrains for kindly providing a license for me to work on this and other open-source projects.

[![](https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg)](https://www.jetbrains.com/?from=https://github.com/overtrue)


# License

MIT
