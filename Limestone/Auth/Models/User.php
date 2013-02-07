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
namespace Limestone\Auth\Models;

class User extends \Limestone\Model {
	
	protected $fields = array("username", "password", "email", "firstName", "lastName", "isActive", "isStaff" ,"isSuperuser", "dateJoined", "lastLogin");
	
    public function setPassword($value) {
    	if(!empty($value)) {	    	
			$this->password = hash_hmac("ripemd160", $value, LIMESTONE_KEY);    
		}
    }
    
    public function setEmail($value) {
	    if(filter_var($value, FILTER_VALIDATE_EMAIL )) {
		    $this->email = $value;
	    } else {
		    throw new \Limestone\Exceptions\ValidationException("E-mail inválido");
      	}
    }
    
    public function checkPassword($password) {
		$check = hash_hmac("ripemd160", $password, LIMESTONE_KEY);
		if($check === $this->password) {
			return true;
		} 
		return false;
    }
    
    public function changePassword($current, $new) {
	    if($this->checkPassword($current)) {
		    $this->setPassword($new);
		    $this->passwordChanged = new \DateTime();
		    return true;
	    }
    }
    
    public function canManageUsers() {
	    if($this->isSuperuser) {
		    return true;
	    }
    }
	
}

?>