<?php
namespace App\Controllers\V1;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use Symfony\Component\HttpFoundation\Request;
use PhpBoot\DB\DB;

class BaseController
{
  use EnableDIAnnotations; 

  /**
   * @inject
   * @var Request
   */
  protected $request;

  /**
   * @inject
   * @var DB
   */
  protected $db;

  /**
   * @inject my.environment
   * @var string
   */
  protected $environment;

  protected $pdo;
  
  private $hook_user;
  
  protected function get_hook_user(){
    if (is_null($this->hook_user)) {
      $hook_user = [];
      $hook_user_str = $this->request->headers->get('hook_user');
      $ary = explode('&', $hook_user_str);
      foreach($ary as $item) {
        $ary2 = explode('=', $item);
        $hook_user[$ary2[0]] = $ary2[1];
      }
      $this->hook_user = $hook_user;
    }
    return $this->hook_user;
  }
  
  protected function failure($msg) {
    return ['ret' => -1, 'msg' => $msg];
  }
  
  // -- PDO
  protected function getPdo() {
    if ($this->pdo === null) {
      $this->pdo = $this->db->getConnection();
    }
    return $this->pdo;
  }

  protected function dbQuery($sql, $params = []) {
    return $this->_dbCommand($sql, $params, 'query');
  }

  protected function dbExec($sql, $params = []) {
    return $this->_dbCommand($sql, $params, 'exec');
  }

  private function _dbCommand($sql, $params, $cmd) {
    if (count($params) > 0) {
      $stmt = $this->getPdo()->prepare($sql);
      foreach($params as $k => $v) {
        $stmt->bindValue($k, $v);
      }
      $stmt->execute();
      if ($cmd === 'query') {
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
      } else {
        return $stmt->rowCount(); // 返回行数
      }
    } else {
      if ($cmd === 'query') {
        return $this->getPdo()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
      } else {
        return $this->getPdo()->exec($sql); // 返回行数
      }
    }
  }
}