<?php
namespace Limestone\Admin;

class Module implements \Limestone\IModule {
	
	public function getModelMappings() {
		return null;
	}
	
	public function getTemplatesPaths() {
		return __DIR__ . "/templates/";
	}
	
	public function applyRoutes($app) {
		$app->get('/admin', function () use ($app) {
			$view = $app->view();
			$app->render('admin/index.html');
		});
		
		$app->get('/admin/login', function () use ($app) {
			$view = $app->view();
			if(isset($_GET["attempt"])) {
				$view->appendData(array("attempt" => true));
			}
		    $app->render('admin/login.html');
		});
		
		$app->post('/admin/login', function () use ($app) {
			$username = $_POST["username"];
			$password = $_POST["password"];
			$user = \R::findOne('user', 'username = ?', array($username));
			if($user && $user->checkPassword($password)) {
				$app->setEncryptedCookie(LIMESTONE_AUTH_COOKIE_NAME, $user->id, "2 hours");
				$app->redirect('/admin');
				$user->lastLogin = new \DateTime();
				\R::store($user);					
			    
			} else {
				$app->redirect('/admin/login?attempt=1');
			}
		});
		
		$app->get('/admin/logout', function () use ($app) {
			$app->deleteCookie(LIMESTONE_AUTH_COOKIE_NAME);
		    $app->redirect('/admin');
		});
		
	} 
	
}