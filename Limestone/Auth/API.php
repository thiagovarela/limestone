<?php
/**
 * Limestone Framework 
 *
 * @author      Thiago Varela <thiago@thiagovarela.com.br>
 * @copyright   2012 Thiago Varela
 * @link        http://limestone.thiagovarela.com.br
 * @license     http://limestone.thiagovarela.com.br/license
 * @version     1.0.0
 * @package     Limestone
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Limestone\Auth;

class API implements \Limestone\IModule {
	
	public function getModelMappings() {
		return array("user" => "\Limestone\Auth\Models\User");
	}
	
	public function getTemplatesPaths() {
		return null;
	}
	
	public function applyRoutes($app) {
		
		$app->get('/createsuperuser', function () use ($app) {
			$user = \R::dispense("user");
			$user->importArray($_GET);
			$user->isActive = true;
			$user->isStaff = true;
			$user->isSuperuser = true;
			$user->dateJoined = \R::isoDate();
			\R::store($user);
		});
		
		$app->post('/admin/users/changepassword.json', function () use ($app) {
			$result = array();
			$user = \R::load("user", $app->getEncryptedCookie(LIMESTONE_AUTH_COOKIE_NAME));
			if($user->id) {
				$success = $user->changePassword($_POST["current"], $_POST["password"]);
				if($success) {
					$result["success"] = true;
					\R::store($user);	
				} else {
					$result["error"] = "Senha atual não confere";	
				}
			} else {
				$result["error"] = "Ocorreu um erro ao buscar o usuário."; // TODO: E o usuário faz o que?
			}
			echo json_encode($result);
		});
				
	}
	
}