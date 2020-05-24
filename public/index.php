<?php

use PhpBoot\Docgen\Swagger\Swagger;
use PhpBoot\Docgen\Swagger\SwaggerProvider;
use PhpBoot\Application;

ini_set("display_errors", "On");//打开错误提示
ini_set("error_reporting",E_ALL);//显示所有错误
// ini_set('date.timezone','Asia/Shanghai');

require __DIR__.'/../vendor/autoload.php';

// 加载配置
$app = Application::createByDefault(
    __DIR__.'/../config/config.php'
);
// 全局勾子
$app->setGlobalHooks([
  // 支持跨域访问
  \PhpBoot\Controller\Hooks\Cors::class, 
  // 全局
  // \App\Hooks\GlobalHook::class
]);

//接口文档自动导出功能, 如果要关闭此功能, 只需注释掉这块代码
//{{
SwaggerProvider::register($app, function(Swagger $swagger)use($app){
    // error_reporting(0);
    $swagger->schemes = ['https','http'];
    $swagger->host = $app->get('host');
    $swagger->info->title = 'PhpBoot 示例';
    $swagger->info->description = "此文档由 PbpBoot 生成 swagger 格式的 json, 再由Swagger UI 渲染成 web。";
    
    // $swagger->securityDefinitions->api_key->type = 'apiKey';
    // $swagger->securityDefinitions->api_key->name = 'Authorization';
    // $swagger->securityDefinitions->api_key->in = 'header';
    
    $api_key['type'] = 'apiKey';
    $api_key['name'] = 'Authorization';
    $api_key['in'] = 'header';
    $swagger->securityDefinitions['api_key'] = $api_key;
    
    // $swagger->externalDocs=new ExternalDocumentationObject();
    // $swagger->externalDocs->description = '接口对应代码';
    // $swagger->externalDocs->url = 'https://github.com/caoym/phpboot-example/blob/master/App/Controllers/Books.php';
});
//}}
// 加载路由
$app->loadRoutesFromPath( __DIR__.'/../App/Controllers/V1', 'App\\Controllers\\V1');

// 执行请求
$app->dispatch();
