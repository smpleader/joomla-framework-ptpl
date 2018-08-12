<?php
/**
 * @package    App\View\Renderer
 *
 * @copyright  Copyright (C) 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace App\View\Renderer;

use App\View\Renderer\HtmlGenerator;

/**
 * Php class for rendering output.
 *
 * @since  1.0
 */
class Ptpl implements RendererInterface
{
	/**
	 * The renderer default configuration parameters.
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $config = array(
		'templates_base_dir' => 'templates/',
		'template_file_ext'  => '.ptpl',
		'template_structure_file'  => 'default',
		'template_cache_dir'     => 'cache/ptpl/', 
		'environment'        => array()
	);

	/**
	 * The data for the renderer.
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $data = array();

	/**
	 * The templates location paths.
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $templatesPaths = array();

	/**
	 * Current template name.
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $template;

	/**
	 * Instantiate the renderer.
	 *
	 * @param   array  $config  The array of configuration parameters.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function __construct($config = array())
	{
		// Merge the config.
		$this->config = array_merge($this->config, $config);

		// Set the templates location path.
		$this->setTemplatesPaths($this->config['templates_base_dir'], true);
	}

	/**
	 * Set the data for the renderer.
	 *
	 * @param   mixed    $key     The variable name or an array of variable names with values.
	 * @param   mixed    $value   The value.
	 * @param   boolean  $global  Is this a global variable?
	 *
	 * @return  Php  Method supports chaining.
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function set($key, $value = null, $global = false)
	{
		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->set($k, $v, $global);
			}
		}
		else
		{
			if (!isset($value))
			{
				throw new \InvalidArgumentException('No value defined.');
			}

			if ($global)
			{
				$this->addGlobal($key, $value);
			}
			else
			{
				$this->data[$key] = $value;
			}
		}

		return $this;
	}

	/**
	 * Unset a particular variable.
	 *
	 * @param   mixed  $key  The variable name.
	 *
	 * @return  Php  Method supports chaining.
	 *
	 * @since   1.0
	 */
	public function unsetData($key)
	{
		return $this->unsetData($key);
	}

	/**
	 * Render and return compiled HTML.
	 *
	 * @param   string  $template  The template file name.
	 * @param   array   $data      An array of data to pass to the template.
	 *
	 * @return  string  Compiled HTML.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function render($template = '', array $data = array())
	{
		if (!empty($template))
		{
			$this->setTemplate($template);
		}

		if (!empty($data))
		{
			$this->set($data);
		}

		try
		{
			return $this->load() ;//->render($this->data);
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException($e->getMessage());
		}
	}

	/**
	 * Display the compiled HTML content.
	 *
	 * @param   string  $template  The template file name.
	 * @param   array   $data      An array of data to pass to the template.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($template = '', array $data = array())
	{
		if (!empty($template))
		{
			$this->setTemplate($template);
		}

		if (!empty($data))
		{
			$this->set($data);
		}

		try
		{
			$this->load()->display($this->data);
		}
		catch (\Exception $e)
		{
			echo $e->getMessage();
		}
	}

	/**
	 * Get the current template name.
	 *
	 * @return  string  The name of the currently loaded template file (without the extension).
	 *
	 * @since   1.0
	 */
	public function getTemplate()
	{
		return $this->template;
	}

	/**
	 * Add a path to the templates location array.
	 *
	 * @param   string  $path  Templates location path.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function addPath($path)
	{
		return $this->setTemplatesPaths($path, true);
	}

	/**
	 * Set the template.
	 *
	 * @param   string  $name  The name of the template file.
	 *
	 * @return  Php  Method supports chaining.
	 *
	 * @since   1.0
	 */
	public function setTemplate($name)
	{
		$this->template = $name;

		return $this;
	}

	/**
	 * Sets the paths where templates are stored.
	 *
	 * @param   string|array  $paths            A path or an array of paths where to look for templates.
	 * @param   bool          $overrideBaseDir  If true a path can be outside themes base directory.
	 *
	 * @return  Php
	 *
	 * @since   1.0
	 */
	public function setTemplatesPaths($paths, $overrideBaseDir = false)
	{
		if (!is_array($paths))
		{
			$paths = array($paths);
		}

		foreach ($paths as $path)
		{
			if ($overrideBaseDir)
			{
				$this->templatesPaths[] = $path;
			}
			else
			{
				$this->templatesPaths[] = $this->config['templates_base_dir'] . $path;
			}
		}

		return $this;
	}

	/**
	 * Load the template and return an output object.
	 *
	 * @return  object  Output object.
	 *
	 * @since   1.0
	 */
	private function load()
	{
		$this->loadPartials();

		return $this->loadLayout($this->config['template_structure_file']);
	}

	private $_partials;

	private function loadPartials(){

		$this->_partials = array();

		$_view_layout = $this->config['templates_base_dir']. '/' . str_replace('.','/',$this->getTemplate() ) . $this->config['template_file_ext'];
		if(!file_exists($_view_layout)) throw 'View layout '.$_view_layout.' not found';

		$this->_partials = (array) require $_view_layout;
		
		foreach(['subtitle','content','title'] as $layout){
			if( !isset($this->_partials[$layout])) $this->_partials[$layout] = '';
		}

		$this->_partials['message'] = $this->loadLayout('shared.message') ;
	}

	private function getPartial($name){
		return isset($this->_partials[$name]) ? (string)$this->_partials[$name] : '';
	}

	private function loadLayout($_layout_file){

		$_full_path = $this->config['templates_base_dir']. '/' . str_replace('.','/', $_layout_file ) . $this->config['template_file_ext'];
		if( !file_exists($_full_path)) return '<!-- Warning: Layout '.$_full_path.' not found -->' ;

		ob_start();		
		include $_full_path;
		return ob_get_clean();
	}
}

class H extends HtmlGenerator{

	static $_ = [];
	static public function set( $key, $value ){ self::$_[$key] = $value; }
	static public function get( $key ){ return isset(self::$_[$key]) ? self::$_[$key] : null; }

	static function html( $content){
		echo '<!DOCTYPE html>'. $content ;
	}
}
