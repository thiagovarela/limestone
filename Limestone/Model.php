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

class Model extends \RedBean_SimpleModel {
	
	protected $fields = array();
	
	protected $required_fields = array();
	
	protected $ownRelations = array();
	protected $sharedRelations = array();
	protected $relations = array();
	
	private $current_bean;
	private $tainted_before_update;
	
	public function __construct() {
		
	}
	
	protected function addField($name) {
		$this->fields[] = $name;
	}
	
	protected function addRequiredField($name) {
		$this->required_fields[] = $name;
	}
	
	protected function addSharedRelation($name) {
		$this->sharedRelations[] = $name;
	}
	
	protected function addOwnRelation($name) {
		$this->ownRelations[] = $name;
	}
	
	protected function addRelation($name) {
		$this->relations[] = $name;
	}
	
	public function getRelations() {
		$relations = array();
		$relations = array_merge($relations, $this->sharedRelations, $this->ownRelations, $this->relations);
		return $relations;
	}
	
	public function preloadRelations() {
		$relations = $this->getRelations();
		if(!empty($relations)) {
			\R::preload($this->bean, $relations);	
		}
	}
	
	public function importArray($values, $specific_fields = null) {
		if($specific_fields) {
			$fields = $specific_fields;
		} else {
			$fields = $this->fields;
		}
		foreach($fields as $field) {
			$method = "set" . ucfirst($field);
			$value = array_key_exists($field, $values) ? $values[$field] : null;
			if(method_exists($this, $method)) {
				call_user_func_array(array($this, $method), array($value));
			} else {
				$this->$field = $value;	
			}	
		}
		foreach($this->sharedRelations as $field) {
			$value = array_key_exists($field, $values) ? $values[$field] : null;
			if($value) {
				$shared = "shared".ucfirst($field);
				foreach($values[$field] as $value) {
					$result[] = \R::load($field, $value);					
				}
				$this->$shared = $result;
			}
		}
		foreach($this->ownRelations as $field) {
			$value = array_key_exists($field, $values) ? $values[$field] : null;
			if($value) {
				$own = "own".ucfirst($field);
				$this->$own = \R::load($field, $values[$field]);	
			}
		}
		foreach($this->relations as $field) {
			$value = array_key_exists($field, $values) ? $values[$field] : null;
			if($value) {
				$this->$field = \R::load($field, $values[$field]);	
			}
		}
	}
	
	public function getUploads() {
		return null;
	}
	
	protected function displayString() {
		return $this->id;
	}
	
	public function getDisplay() {
		$display = substr(get_class($this), strrpos(get_class($this), "\\")+1);
		if($this->id) {
			return  $display . " " . $this->displayString();
		} else {
			return $display;
		}
	}
	
	public function open() {
		if($this->bean->id) {
			$this->current_bean = $this->bean->__toString();	
		}
	}
	public function update() {
		$this->tainted_before_update = $this->bean->getMeta('tainted');
		foreach($this->required_fields as $field) {
			if(empty($this->$field)) {
				throw new \Limestone\Exceptions\NullFieldException("Campo $field do modelo " . $this->getDisplay() . " não pode ser nulo.");	
			}
		}
	}
	public function after_update() {		
		if($this->tainted_before_update) {
			$app = \Slim\Slim::getInstance();
			$user_key = $app->getEncryptedCookie("_key");
			$audit = \R::dispense("audit");
			$audit->model = $this->bean->getMeta("type");
			$audit->model_id = $this->bean->id;
			$audit->a_date = new \DateTime();
			if($this->current_bean) {
				$audit->type = 2; 
				$audit->before = $this->current_bean;
				$audit->after = $this->bean->__toString();	
			} else {
				$audit->type = 1;
				$audit->after = $this->bean->__toString();
			}
			//$audit->trace = json_encode(debug_backtrace());
			if($user_key) {
				$user = \R::findOne('user', 'id = ?', array($user_key));
				if($user) {
					$audit->user = $user;	
				}	
			}
			\R::store($audit);
		}
	}
	
}

?>