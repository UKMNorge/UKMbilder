<?php


/**
 * Abstract Controller class.
 * 
 * Main Controller class. All controllers in UKMImages should extend this
 *
 * @abstract
 */
abstract class Controller
{
	public $viewPath = 'views/';
	public $view 	 = 'default.php';
	public $data 	 = array(); // Holding data for view and controller
	public $post	 = array(); // Holds post data
	public $get 	 = array(); // Holds get data

	public function __construct()
	{
		$this->post = $_POST;
		$this->get = $_GET;
	}
	
	public function setView( $view )
	{
		$this->view = $view . '.php';
		$this->render();
	}
	
	public function setData( $key, $data )
	{
		$this->data[$key] = $data;
	}
	
	public function getData( $key )
	{
		return $this->data[$key];
	}
	
	public function getVar( $var )
	{
		if( isset( $this->post[$var] ) )
			return $this->post[$var];
			
		elseif( isset( $this->get[$var] ) )
			return $this->get[$var];
		
		else
			return NULL;
	}
	
	public function render() 
	{
		require_once( $this->viewPath . $this->view );
	}
	
	public function redirect( $to )
	{
		exit( '<script type="text/javascript">document.location = "'.get_admin_url() . $to . '";</script>' );
	}
	
	abstract public function prerender();
}