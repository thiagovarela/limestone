<?php
namespace Limestone\Auth;

class AuthAdmin extends \Limestone\Admin\ScaffoldAdmin implements \Limestone\IModule, \Limestone\IRoutes {

	protected $routes = array(
		array("route_name" => "users",
			  "model" => "user",
			  "name" => "Usuário",
			  "name_plural" => "Usuários",
			  "new_title" => "Novo Usuário",
		)
	);
	
	public function getModelMappings() {
		return null;
	}
	
	public function getTemplatesPaths() {
		return __DIR__ . "/templates/";
	}
	
}