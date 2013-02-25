<?php
namespace Limestone\Admin;

class ScaffoldAdmin {
	
	protected $routes;
	
	protected $route_name;
	protected $template_name;
	protected $model;
	protected $name;
	protected $name_plural;
	protected $new_title;
	
	private function listRoute($app, $route) {
		$app->get($route["route_name"] .'/', function () use ($app, $route) {
			extract($route);
			$page = isset($_GET['p']) ? $_GET['p'] : null;
			$order_fields = isset($_GET['ord_fields']) ? $_GET['ord_fields'] : null;
			$searchHelper = new \Limestone\SearchHelper($page, $order_fields);
			$records = \R::findAll($model, $searchHelper->getSql());
			if(count($records) > 0) {
				$relations = \R::dispense($model)->getRelations();
				if($relations) {
					\R::preload($records, $relations);	
				}
			}
			$pages = $searchHelper->getPages(\R::count($model)); 
			$view = $app->view();
		    $view->appendData(array(
			    "records" => $records,
			    "pages" => $pages,
			    "current_page" => $page,
			    "routes" => array("edit" => $route_name ."/edit", "delete" => $route_name ."/delete"),
			    "title" => $name_plural
		    ));
		    $app->render($template_name .".html");
		});
	}
	
	private function editRoute($app, $route) {
		$app->get($route["route_name"] .'/edit(/:id)', function ($id = null) use ($app, $route) {
			extract($route);
			$view = $app->view();
			$record = \R::load($model, $id);
			$view->appendData(array(
			    "record" => $record
		    ));
			if($record->id) {   
				$view->appendData(array(
				    "title" => $record->getDisplay()
				));
			} else {
				$view->appendData(array(
				    "title" => $new_title
			    ));
			} 
		    $app->render($template_name .'_edit.html');
		});
		
		$app->post($route["route_name"] .'/edit(/:id)', function ($id = null) use ($app, $route) {
			try {
				\R::begin();
				extract($route);
			    if($id) {
			    	$record = \R::load($model, $id);
			    } else {
				    $record = \R::dispense($model);			    
			    }
			    $record->importArray($_POST);

			    if(!empty($_FILES)) {
			    	$uploaded_files = array(); 
				    foreach($_FILES as $fieldname => $fieldvalue) {
					    foreach($fieldvalue as $paramname => $paramvalue) {
						    foreach((array)$paramvalue as $index => $value) {
								$uploaded_files[$fieldname][$index][$paramname] = $value;    
						    }
					    }
				    }
			    	$uploads = $record->getUploads();
			    	$limestone = \Limestone\Limestone::getInstance();
			    	if(!empty($uploads)) {
				    	foreach($uploaded_files as $name => $files) {
				    		$conf = $uploads[$name];
				    		foreach($files as $file) {
					    		$uploader = new \Limestone\Upload($file);
						    	if(isset($conf["type"]))	{
						    		$uploader->allowed = explode("|", $conf["type"]);
						    		$uploader->mime_check = true;	    		
						    	}
						    	if(isset($conf["max_image_size"]))	{
							    	list($width, $height) = explode("x", $conf["max_image_size"]);
							    	$uploader->image_max_width = $width;
							    	$uploader->image_max_height = $height;		
						    	}
						    	$uploader->file_new_name_body = md5($file["tmp_name"]);
						    	$uploader->process($conf["upload_to"]);
						    	if($uploader->processed) {
							    	$record[$name] = $conf["base_url"] . $uploader->file_dst_name;	
						    	} else {	
						    		throw new \Limestone\Exceptions\FileUploadException($uploader->error);
						    	}
						    	
					    	}
				    	}
			    	} else {
			    		throw new \Limestone\Exceptions\FileUploadException("Não existe configuração de envio de arquivos para este modelo");
			    	}
			    }
			    
			    
			    \R::store($record);
			    
			    \R::commit();
			} catch(Exception $e) {
				\R::rollback();	
				throw $e;
			}

			if(isset($_POST['save'])) {
				$app->redirect($route_name);
			} else {
				$app->redirect($route_name ."/edit");
			}
		   
		});
	}
	
	private function deleteRoute($app, $route) {
		$app->delete($route["route_name"] .'/delete', function () use ($app, $route) {
			extract($route);
			$records = \R::batch($model, explode(",", $_POST['delete_values']));
			\R::trashAll($records);
			$app->redirect($route_name);
		});		
	}
	
	private function jsonAllRoute($app, $route) {
		$app->get($route["route_name"] .'/all.json', function () use ($app, $route) {
			$model = $route["model"];
			$records = \R::findAll($model);
			if(count($records) > 0) {
				$relations = \R::dispense($model)->getRelations();
				\R::preload($records, $relations);
			}
			$result = json_encode(\R::exportAll($records));
			echo $result;
		});		
	}
	
	public function applyRoutes($app) {
		
		if(!empty($this->routes)) {
			foreach($this->routes as $route) {
				$this->listRoute($app, $route);
				$this->editRoute($app, $route);
				$this->deleteRoute($app, $route);
				$this->jsonAllRoute($app, $route);
			}
		}
	}
}