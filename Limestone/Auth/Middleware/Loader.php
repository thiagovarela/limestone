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

namespace Limestone\Auth\Middleware;

class Loader extends \Slim\Middleware
{

    public function call()
    {
    	$app = $this->app;
    	
        $this->next->call();
    }
}

?>