<?php
// +----------------------------------------------------------------------
// | UCToo [ Universal Convergence Technology ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2017 http://uctoo.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Patrick <contact@uctoo.com>
// +----------------------------------------------------------------------

namespace com\wxapp;
use com\wxapp\errorCode;
use think\exception;

/**
 * Prpcrypt class
 *
 * 
 */
class Prpcrypt
{
	public $key;

	function __construct( $k )
	{
		$this->key = $k;
	}

	/**
	 * 对密文进行解密
	 * @param string $aesCipher 需要解密的密文
     * @param string $aesIV 解密的初始向量
	 * @return string 解密得到的明文
	 */
	public function decrypt( $aesCipher, $aesIV )
	{

		try {
			
			$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
			
			mcrypt_generic_init($module, $this->key, $aesIV);

			//解密
			$decrypted = mdecrypt_generic($module, $aesCipher);
			mcrypt_generic_deinit($module);
			mcrypt_module_close($module);
		} catch (Exception $e) {
			return array(errorCode::$IllegalBuffer, null);
		}


		try {
			//去除补位字符
			$pkc_encoder = new pkcs7Encoder;
			$result = $pkc_encoder->decode($decrypted);

		} catch (Exception $e) {
			//print $e;
			return array(errorCode::$IllegalBuffer, null);
		}
		return array(0, $result);
	}
}

?>