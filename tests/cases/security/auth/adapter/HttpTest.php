<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2013, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace lithium\tests\cases\security\auth\adapter;

use lithium\tests\mocks\security\auth\adapter\MockHttp;
use lithium\action\Request;
use lithium\core\Libraries;

class HttpTest extends \lithium\test\Unit {

	public function testCheckBasicIsFalseRequestsAuth() {
		$request = new Request();
		$http = new MockHttp(array('method' => 'basic', 'users' => array('gwoo' => 'li3')));
		$result = $http->check($request);
		$this->assertEmpty($result);

		$basic = basename(Libraries::get(true, 'path'));
		$expected = array('WWW-Authenticate: Basic realm="' . $basic . '"');
		$result = $http->headers;
		$this->assertEqual($expected, $result);
	}

	public function testCheckBasicIsTrueProcessesAuthAndSucceeds() {
		$request = new Request(array(
			'env' => array('PHP_AUTH_USER' => 'gwoo', 'PHP_AUTH_PW' => 'li3')
		));
		$http = new MockHttp(array('method' => 'basic', 'users' => array('gwoo' => 'li3')));
		$result = $http->check($request);
		$this->assertNotEmpty($result);

		$expected = array();
		$result = $http->headers;
		$this->assertEqual($expected, $result);
	}

	public function testCheckBasicIsTrueProcessesAuthAndSucceedsCgi() {
		$basic = 'Z3dvbzpsaTM=';

		$request = new Request(array(
			'env' => array('HTTP_AUTHORIZATION' => "Basic {$basic}")
		));
		$http = new MockHttp(array('method' => 'basic', 'users' => array('gwoo' => 'li3')));
		$result = $http->check($request);
		$this->assertNotEmpty($result);

		$expected = array();
		$result = $http->headers;
		$this->assertEqual($expected, $result);

		$request = new Request(array(
			'env' => array('REDIRECT_HTTP_AUTHORIZATION' => "Basic {$basic}")
		));
		$http = new MockHttp(array('method' => 'basic', 'users' => array('gwoo' => 'li3')));
		$result = $http->check($request);
		$this->assertNotEmpty($result);

		$expected = array();
		$result = $http->headers;
		$this->assertEqual($expected, $result);
	}

	public function testCheckDigestIsFalseRequestsAuth() {
		$request = new Request();
		$http = new MockHttp(array('realm' => 'app', 'users' => array('gwoo' => 'li3')));
		$result = $http->check($request);
		$this->assertFalse($result);
		$this->assertPattern('/Digest/', $http->headers[0]);
		$this->assertPattern('/realm="app",/', $http->headers[0]);
		$this->assertPattern('/qop="auth",/', $http->headers[0]);
		$this->assertPattern('/nonce=/', $http->headers[0]);
	}

	public function testCheckDigestIsTrueProcessesAuthAndSucceeds() {
		$digest  = 'qop="auth",nonce="4bca0fbca7bd0",';
		$digest .= 'nc="00000001",cnonce="95b2cd1e179bf5414e52ed62811481cf",';
		$digest .= 'uri="/http_auth",realm="app",';
		$digest .= 'opaque="d3fb67a7aa4d887ec4bf83040a820a46",username="gwoo",';
		$digest .= 'response="04d7d878c67f289f37e553d2025e3a52"';

		$request = new Request(array('env' => array('PHP_AUTH_DIGEST' => $digest)));
		$http = new MockHttp(array('realm' => 'app', 'users' => array('gwoo' => 'li3')));
		$result = $http->check($request);
		$this->assertNotEmpty($result);

		$expected = array();
		$result = $http->headers;
		$this->assertEqual($expected, $result);
	}

	public function testCheckDigestIsTrueProcessesAuthAndSucceedsCgi() {
		$digest  = 'qop="auth",nonce="4bca0fbca7bd0",';
		$digest .= 'nc="00000001",cnonce="95b2cd1e179bf5414e52ed62811481cf",';
		$digest .= 'uri="/http_auth",realm="app",';
		$digest .= 'opaque="d3fb67a7aa4d887ec4bf83040a820a46",username="gwoo",';
		$digest .= 'response="04d7d878c67f289f37e553d2025e3a52"';

		$request = new Request(array(
			'env' => array('HTTP_AUTHORIZATION' => "Digest {$digest}")
		));
		$http = new MockHttp(array('realm' => 'app', 'users' => array('gwoo' => 'li3')));
		$result = $http->check($request);
		$this->assertNotEmpty($result);

		$expected = array();
		$result = $http->headers;
		$this->assertEqual($expected, $result);

		$request = new Request(array(
			'env' => array('REDIRECT_HTTP_AUTHORIZATION' => "Digest {$digest}")
		));
		$http = new MockHttp(array('realm' => 'app', 'users' => array('gwoo' => 'li3')));
		$result = $http->check($request);
		$this->assertNotEmpty($result);

		$expected = array();
		$result = $http->headers;
		$this->assertEqual($expected, $result);
	}
}

?>