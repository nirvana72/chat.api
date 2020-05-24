<?php
namespace App\Hooks;
use PhpBoot\Controller\HookInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use PhpBoot\DB\DB;
/**
 * auth hook
 *
 * 权证是否有执行API的权限
 * @package App\Hooks
 */
class AuthHook implements HookInterface
{
  use EnableDIAnnotations; //启用通过@inject标记注入依赖

  /**
   * @inject
   * @var DB
   */
  private $db;

  /**
   * @inject
   * @var Request
   */
  private $global_request;
  
  private $auth;

  public function __construct($params) {
    $this->auth = $params;
  }
  /**
   * @param Request $request
   * @param callable $next
   * @return Response
   */
  public function handle(Request $request, callable $next) {
    /**
     * 三种使用方法
     * \App\Hooks\AuthHook 只验证enable
     * \App\Hooks\AuthHook role:admin 固定角色验证，admin也不能随便调用, 如角色名有空格，需要用_代替，因为框架取参限制
     * \App\Hooks\AuthHook user.list 授权验证， admin可以忽略验证
     */
    $hook_user_str = $this->global_request->headers->get('hook_user'); // string
    $ary = explode('=', $hook_user_str);
    $uid = $ary[1];

    $sql = "select 
              account, nickname
            from 
              v_user
            where uid = {$uid}";
    $pdo = $this->db->getConnection();
    $rows = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    if (count($rows) !== 1) {
      \PhpBoot\abort('用户不存在');
    }
    $account = $rows[0]['account'];
    $nickname = $rows[0]['nickname'];

    // 用户信息给后续controller 用
    $this->global_request->headers->set('hook_user', "uid={$uid}&account={$account}&nickname={$nickname}");
    return $next($request); 
  }
}