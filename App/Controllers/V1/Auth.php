<?php
namespace App\Controllers\V1;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use PhpBoot\DB\DB;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

/**
 * Auth
 *
 * 权限
 * 
 * @path /v1/auth
 */
class Auth extends BaseController
{
  /**
   * @inject
   * @var LoggerInterface
   */
  public $logger;

  /**
   * @inject host
   * @var string
   */
  private $host;

  /**
   * @inject my.jwtKey
   * @var string
   */
  private $jwtKey;

  /**
   *
   * 登录
   *
   * @route POST /login
   * @param string $account 账号 {@v required}
   * @param string $pwd 密码 {@v required}
   */
  public function login($account, $pwd) {
    $sql = "select uid, nickname, avatar, password, pwd_salt from t_user where account = :account";
    $rows = $this->dbQuery($sql, ['account' => $account]);

    if(count($rows) <= 0){
      return $this->failure('用户名或密码不正确!');
    }

    if(count($rows) > 1){
      $count = count($rows);
      $this->logger->error("find {$count} rows by account = {$account}");
      return $this->failure('用户名或密码不正确!!');
    }

    $pwd = md5($pwd . $rows[0]['pwd_salt']);
    if($rows[0]['password'] !== $pwd){
      return $this->failure('用户名或密码不正确!!!');
    }

    //---------------------------------------------------------------------------------------

    $ret['ret'] = 1;
    $ret['msg'] = '登录成功';
    $ret['uid'] = $rows[0]['uid'];
    $ret['nickname'] = $rows[0]['nickname']; 
    $ret['avatar'] = $rows[0]['avatar']; 

    $func = new \App\Utils\Func();
    $jwt = $func->createToken($rows[0]['uid'], $this->jwtKey, $this->host);
    $ret['token'] = $jwt['token'];
    $ret['refreshtoken'] = $jwt['refreshtoken'];

    return $ret; 
  }

  /**
   *
   * 注册账号
   *
   * @route POST /reg
   * @param string $account 账号 {@v required}
   * @param string $pwd 密码 {@v required}
   */
  public function reg($account, $pwd ) {
    $tablename = 't_user';
    $func = new \App\Utils\Func();

    // 密码(长度在6~18之间，只能包含字母、数字和下划线)
    if(!preg_match("/^.{6,20}$/", $pwd)){
      return $this->failure('密码格式不正确,6~20位任意字符');
    }
    
    $account_type = $this->_checkAccountType($account);
    if ($account_type === '') {
      return $this->failure('账号格式不正确');
    }
    
    $result = $this->db->select('uid')->from($tablename)->where('account = ?', $account)->get();
    if(count($result) > 0){
      return $this->failure('账号已存在');
    }
    
    $pwd_salt = strtolower($func->getRandomString(6));
    $data = [
        'account' => $account,
        'password' => md5($pwd . $pwd_salt),
        'pwd_salt' => $pwd_salt,
        'nickname' => $account,
        'avatar'   => mt_rand(1, 34),
        'writetime' => date('Y-m-d H:i:s')
    ];
    $uid = $this->db->insertInto($tablename)->values($data)->exec()->lastInsertId(); 
 
    $ret['ret'] = 1;
    $ret['msg'] = '注册成功';
    $ret['uid'] = $uid;
    $ret['nickname'] = $data['nickname']; 
    $ret['avatar'] = $data['avatar']; 

    $jwt = $func->createToken($uid, $this->jwtKey, $this->host);
    $ret['token'] = $jwt['token'];
    $ret['refreshtoken'] = $jwt['refreshtoken'];

    return $ret;
  }

  //----------------------------------------------------------------------------------------------

  private function _checkAccountType($account){
    $regex = [
      'email' => "/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/",
      'mobile' => "/^(13[0-9]|14[0-9]|15[0-9]|166|17[0-9]|18[0-9]|19[8|9])\d{8}$/",
      'account' => "/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{2,18}$/u"
    ];
    
    if(preg_match($regex['email'], $account)){
      return 'email';
    }   
    if(preg_match($regex['mobile'], $account)){
      return 'mobile';
    }
    if(preg_match($regex['account'], $account)){
      return 'account';
    }
    return '';
  }

}