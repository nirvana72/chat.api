<?php
namespace App\Hooks;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use PhpBoot\Controller\HookInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
/**
 * 简单登录校验
 *
 * 实现了 Basic Authorization
 * @package App\Hooks
 */
class JwtHook implements HookInterface
{
    use EnableDIAnnotations; //启用通过@inject标记注入依赖

    /**
     * @inject
     * @var Request
     */
    private $global_request;

    /**
     * @inject my.environment
     * @var string
     */
    private $environment;

    /**
     * @inject my.jwtKey
     * @var string
     */
    private $jwtKey;

    /**
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function handle(Request $request, callable $next) { 
      // if($this->environment === 'develop'){        
      //   $this->global_request->headers->set('hook_uid', 1000);
      //   return $next($request); 
      // }

      // \PhpBoot\abort('系统维护中，预计2019-12-11 10点恢复');
      $lastVersion = '20200511';
      $ClientVersion = $request->headers->get('ClientVersion');
      if(!$ClientVersion) {
        \PhpBoot\abort("此接口需要登录后访问");
      }
      if ($ClientVersion !== 'app' && $ClientVersion !== $lastVersion) {
        \PhpBoot\abort("您访问的内容不是最新版本,请刷新浏览器清理缓存,保证网页版本号是 {$lastVersion}, 版本号见网页标题");
      }

      $Bearer = $request->headers->get('Authorization');
    
      $Bearer = substr($Bearer, 7); // Bearer 
      
      $signer = new \Lcobucci\JWT\Signer\Hmac\Sha256();
      //解析token
      $parser = new \Lcobucci\JWT\Parser();
          
      $token = null;
      try {
          $token = $parser->parse($Bearer);
      } catch (\Exception $e) {
          \PhpBoot\abort('jwt_token_error');
      }
      
      //验证token合法性, 由ExceotionRenderer捕获, 返回-98
      if (!$token->verify($signer, $this->jwtKey)) {
          \PhpBoot\abort('jwt_token_error');
      }
    
      //验证是否已经过期, 由ExceotionRenderer捕获, 返回-99
      if ($token->isExpired()) {
          \PhpBoot\abort('jwt_token_expired');
      }

      // 取出解析后uid, 给后续controller 用
      $uid = $token->getClaim('uid');
      $this->global_request->headers->set('hook_user', "uid={$uid}");
      // if ($uid !== '1000') {
      //   \PhpBoot\abort('系统维护中，预计2019-12-26 14:30点恢复');
      // }
      return $next($request); 
    }
}