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
 
namespace Limestone;

class Limestone {

	protected static $instance;
	
	public function __construct() {
		self::$instance = $this;
	}
	
	public static function getInstance() {
        return isset(self::$instance) ? self::$instance : null;
    }

	protected $modules = array();
	
	public function add($module) {
		$this->modules[] = new $module();
	}
	
	public function load() {
		$templates = array();
		$mappings = array();
		foreach($this->modules as $module) {
			
			$module_templates = $module->getTemplatesPaths();

			if($module_templates) {
				$templates[] = $module_templates; 	
			}
			
			if(method_exists($module, "getModelMappings")) {
				$model_mappings = $module->getModelMappings();
				if($model_mappings) {
					$mappings = array_merge($mappings, $model_mappings);	
				}	
			}
		}
		
		return array("templates" => $templates, "mappings" => $mappings);
	}
	
	public function routes($app) {
		foreach($this->modules as $module) {
			$module->applyRoutes($app);
		}
	}
	
	public function flashError($message) {
		$app = \Slim\Slim::getInstance();
		$app->flash("error", $message);
		
	}

}

if(!defined("LIMESTONE_AUTH_COOKIE_NAME")) {
    define("LIMESTONE_AUTH_COOKIE_NAME", "lkf");
}