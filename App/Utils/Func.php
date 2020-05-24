<?php
namespace App\Utils;

class Func
{
  public function createToken($uid, $jwtKey, $host){
    $signer = new \Lcobucci\JWT\Signer\Hmac\Sha256();
    $time = time();
    $token = (new \Lcobucci\JWT\Builder())->setIssuer($host) // Configures the issuer (iss claim)
        ->setAudience($host) // Configures the audience (aud claim)
        ->setId('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
        ->setIssuedAt($time) // Configures the time that the token was issued (iat claim)
        ->setNotBefore($time + 60) // Configures the time that the token can be used (nbf claim)
        ->setExpiration($time + 3600 * 24) // Configures the expiration time of the token (exp claim)
        ->set('uid', $uid) // 
        ->sign($signer, $jwtKey) // creates a signature using "testing" as key
        ->getToken(); // Retrieves the generated token

    $refreshtoken = (new \Lcobucci\JWT\Builder())->setIssuer($host) // Configures the issuer (iss claim)
        ->setAudience($host) // Configures the audience (aud claim)
        ->setId('572hg240482', true) // Configures the id (jti claim), replicating as a header item
        ->setIssuedAt($time) // Configures the time that the token was issued (iat claim)
        ->setNotBefore($time + 60) // Configures the time that the token can be used (nbf claim)
        ->setExpiration($time + 3600 * 24 * 15) // Configures the expiration time of the token (exp claim)
        ->set('uid', $uid) // Configures a new claim, called "uid"
        ->sign($signer, $jwtKey) // creates a signature using "testing" as key
        ->getToken(); // Retrieves the generated token

    $jwt['token'] = (string)$token;
    $jwt['refreshtoken'] = (string)$refreshtoken;
    return $jwt;
  }

  // 生成随机字符串
  public function getRandomString($len, $chars=null){  
    if (is_null($chars)) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";  
    }  
    mt_srand(10000000*(double)microtime());  
    for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++) {  
        $str .= $chars[mt_rand(0, $lc)];  
    }  
    return $str;  
  }

}