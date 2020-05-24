<?php
namespace App\Controllers\V1;
use PhpBoot\DB\DB;
use Symfony\Component\HttpFoundation\Request;

/**
 * Chat
 *
 * 权限
 * 
 * @path /v1/chat
 */
class Chat extends BaseController
{
  
  /**
   * @inject
   * @var \Doctrine\Common\Cache\RedisCache
   */
  private $redis;

  /**
   *
   * 在线用户列表
   *
   * @route GET /users
   * @hook \App\Hooks\JwtHook
   */
  public function users() {

    $list = [];

    $ary = $this->redis->getRedis()->smembers('user:list');
    if (count($ary) > 0) {
      $str = implode(',', $ary);

      $sql = "select uid, nickname, avatar from t_user where uid in ({$str})";
      $list = $this->dbQuery($sql);
    }

    $ret['ret'] = 1;
    $ret['msg'] = 'success';
    $ret['list'] = $list;
    return $ret; 
  }


  /**
   *
   * TEST
   *
   * @route GET /test
   * @hook \App\Hooks\JwtHook
   */
  public function test() {

    $ret['ret'] = 1;
    $ret['msg'] = 'test';

    return $ret; 
  }

}