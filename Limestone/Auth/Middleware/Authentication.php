<?php
namespace Limestone\Auth\Middleware;

class Authentication extends \Slim\Middleware
{

	public $secured_paths;
	public $login_url;
	public $logout_url;
	
	public function __construct($paths = array(), $login_url = "/login", $logout_url = "/logout") {
		$this->secured_paths = $paths;
		$this->login_url = $login_url;
		$this->logout_url = $logout_url;
	} 

    public function call()
    {
    	$app = $this->app;
    	$auth = $this;
    	$app->hook("slim.before.router", function() use ($auth, $app) {
	    	$req = $app->request();
	        $path = $req->getPath();
	        if(!defined("LIMESTONE_AUTH_COOKIE_NAME")) {
		        define("LIMESTONE_AUTH_COOKIE_NAME", "lkf");
	        }
	        
	        $is_logged = $app->getEncryptedCookie(LIMESTONE_AUTH_COOKIE_NAME);
	        $is_secured_path = false;
	        foreach($auth->secured_paths as $secure) {
		    	if(strpos($path, $secure) === 0 ) {
		    		if($path != $auth->login_url 
		    		&& $path != "/createsuperuser"
		    		&& $path != $auth->logout_url) {
				    	$is_secured_path = true;
			    	}
		    	}    
	        }
	        if($is_logged) {
		        $user = \R::findOne('user', 'id = ?', array($is_logged));
		        if(!$user) {
			    	$app->redirect($auth->login_url);	    
		        }
		        $view = $app->view();
				$view->appendData(array(
				    'logged_username' => $user->firstName,
				    'logout_route' => $auth->logout_url,
				    'canManageUsers' => $user->canManageUsers()
			    ));
	
	        } else if($is_secured_path) {
		    	$app->redirect($auth->login_url); 	   
	        }	
    	});
        
        $this->next->call();
    }
}

?>