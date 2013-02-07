<?php
/**
 * Limestone -  Developing Together with Slim, Doctrine and Twig 
 *
 * @author      Thiago Varela <contato@thiagovarela.com.br>
 * @copyright   2012 Thiago Varela
 * @link        http://www.thiagovarela.com.br/limestone
 * @license     http://www.thiagovarela.com.br/limestone/license
 * @version     1.0.0
 * @package     Limestone
 *
 */

namespace Limestone\Admin\Middleware;

class Loader extends \Slim\Middleware
{

    public function call()
    {
    	$app = $this->app;
    	$template_path = $app->config("templates.path");
    	if($template_path) {
	    	if(!is_array($template_path)) {
		    	$template_path = array($template_path);
	    	}
	    	$template_path[] = __DIR__ . "/../stemplates/";
	    	$app->config("templates.path", $template_path);
	    	$app->view()->setTemplatesDirectory($template_path);
    	}
        $this->next->call();
    }
}

?>