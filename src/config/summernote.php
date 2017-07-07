<?php

/*
 * This file is part of the overtrue/laravel-summernote.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

return [
    // 存储引擎: config/filesystem.php 中 disks， public 或 qiniu
    'disk' => 'public',
    'route' => [
        'name' => '/summernote/server',
        'options' => [
            // middleware => 'auth',
        ],
    ],

    // 是否使用 md5 格式文件名
    'hash_filename' => true,

    // 上传 配置
    'upload' => [
        'form_name' => 'image',
        /* {filename} 会替换成原文件名,配置这项需要注意中文乱码问题 */
        /* {rand:6} 会替换成随机数,后面的数字是随机数的位数 */
        /* {time} 会替换成时间戳 */
        /* {yyyy} 会替换成四位年份 */
        /* {yy} 会替换成两位年份 */
        /* {mm} 会替换成两位月份 */
        /* {dd} 会替换成两位日期 */
        /* {hh} 会替换成两位小时 */
        /* {ii} 会替换成两位分钟 */
        /* {ss} 会替换成两位秒 */
        /* 非法字符 \  => * ? " < > | */

        /* 上传图片配置项 */
        'max_size' => 2 * 1024 * 1024, /* 上传大小限制，单位B */
        'allow_files' => ['.png', '.jpg', '.jpeg', '.gif', '.bmp'], /* 上传图片格式显示 */
        'path_format' => '/uploads/image/{yyyy}/{mm}/{dd}/', /* 上传保存路径,可以自定义保存路径和文件名格式 */
    ],
];
