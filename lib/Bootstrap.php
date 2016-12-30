<?php
/**
 * 
 * Routing Class - For MVC
 * Routed to [class-method-parameter] by the url value
 *
 * @author  Jees K Denny
 * @version 1.0, 30/12/16
 * @since   28/12/16
 */

class Bootstrap 
{
	protected $controller='index';
	protected $method ='index';
	protected $parm = [];
	protected $error_controller='error';

	function __construct()
	{
		$url = $this-> urlPars();
		if($url){

			if(file_exists('controllers/' . $url[0] . '.php')){

				$this->controller = $url[0];

				unset($url[0]);

				require 'controllers/' . $this->controller . '.php';

				$controller_ob = new $this->controller;

				if(isset($url[1])){

					if(method_exists($this->controller, $url[1])){

						$this->method = $url[1];

						unset($url[1]);

						$this->parm = $url ? array_values($url) : false ;

						if($this->parm)
						{
							$controller_ob->{$this->method}($this->parm);
							
						}else{
							$controller_ob->{$this->method}();
						}
					}
				}else{
					$controller_ob->{$this->method}();
				}
			}else{
				require 'controllers/' . $this->error_controller . '.php';

				$this->error_controller = new $this->error_controller;

				return false;
			}
		}else{
			require 'controllers/' . $this->controller . '.php';
			$controller_ob = new $this->controller;
		}
	}

	public function urlPars()
	{
		if(isset($_GET['url'])){
			return $url =  explode('/', filter_var(rtrim( $_GET['url'], '/'), FILTER_SANITIZE_URL));
		}
	}
}