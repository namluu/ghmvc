<?php
namespace Core;

class Ckeditor{
	
	public $data 	= 	array();

	public static function getInstance(){
		static $instance;
		if (!is_object($instance))
		{
			$instance = new Ckeditor();
		}	
		return $instance;
	}

	public function __construct() {
		//...nothing
	}
	/**
	 * __get
	 *
	 * Enables the use of CI super-global without having to define an extra variable.
	 *
	 * I can't remember where I first saw this, so thank you if you are the original author. -Militis
	 *
	 * @access	public
	 * @param	$var
	 * @return	mixed
	 */
	public function __get($var)
	{
		return get_instance()->$var;
	}
	public function create_editor($name='content',$value='',$template='Full',$width = '100%',$height='100px',$extent=''){
		$this->build_data_config();

		$templatearr = array('Full','Basic');
		if(!array_search($template,$templatearr)){
			$template = 'Full';
		}
		//custom width,height
		$this->data[$template]['config']['width']=$width;
		$this->data[$template]['config']['height']=$height;
		//custion id textarea
		$this->data[$template]['id']=$name;
		if(is_array($extent)){
			$extent = implode(' ',$extent);
		}
		$html = '<textarea name="'.$name.'" '.$extent.' >'.htmlspecialchars($value).'</textarea>';
		$html .= $this->display_ckeditor($this->data[$template]);
	
		return $html;
	}
	function cke_initialize($data = array()) {
	
	$return = '';
	
	if(!defined('CI_CKEDITOR_HELPER_LOADED')) {
		
		define('CI_CKEDITOR_HELPER_LOADED', TRUE);
		$return =  '<script type="text/javascript" src="'.$data['path'] . '/ckeditor.js"></script>';
		$return .=	"<script type=\"text/javascript\">CKEDITOR_BASEPATH = '" . $data['path'] . "/';</script>";
	} 
	
	return $return;
	
}

	/**
	 * This function create JavaScript instances of CKEditor
	 * @author Samuel Sanchez 
	 * @access private
	 * @param array $data (default: array())
	 * @return string
	 */
	function cke_create_instance($data = array()) {
		
		$return = "<script type=\"text/javascript\">
			CKEDITOR.replace('" . $data['id'] . "', {";
				$keys = array_keys($data['config']);
				//Adding config values
				if(isset($data['config'])) {
					foreach($data['config'] as $k=>$v) {
						// Support for extra config parameters
						if (is_array($v)) {
							$return .= $k . " : [";
							$return .= $this->config_data($v);
							$return .= "]";
							
						}
						else {
							$return .= $k . " : '" . $v . "'";
						}
						if($k !== end($keys)) {
							$return .= ",";
						}
									
					} 
				}
						
		$return .= '});';
		$return .=' CKEDITOR.on( \'instanceReady\', function( ev )
		{
			var formater = [];
			formater[\'indent\'] = 1;
			formater[\'breakBeforeOpen\'] = 1;
			formater[\'breakAfterOpen\'] = 1;
			formater[\'breakBeforeClose\'] = 0;
			formater[\'breakAfterClose\'] = 1;
			var pre_formater = 0;
			var dtd = CKEDITOR.dtd;
			for ( var e in CKEDITOR.tools.extend( {}, dtd.$nonBodyContent, dtd.$block, dtd.$listItem, dtd.$tableContent ) ) {
				ev.editor.dataProcessor.writer.setRules( e, formater);
			}
	
			ev.editor.dataProcessor.writer.setRules( \'pre\',
			{
				indent: pre_formater
			});
		})
		CKEDITOR.dtd.$removeEmpty[\'span\'] = false;
		CKEDITOR.config.allowedContent = true;
		</script>';
		
			
		
		return $return;
		
	}
	
	/**
	 * This function displays an instance of CKEditor inside a view
	 * @author Samuel Sanchez 
	 * @access public
	 * @param array $data (default: array())
	 * @return string
	 */
	function display_ckeditor($data = array())
	{
		// Initialization
		$return = $this->cke_initialize($data);
		
		// Creating a Ckeditor instance
		$return .= $this->cke_create_instance($data);
		
	
		// Adding styles values
		if(isset($data['styles'])) {
			
			$return .= "<script type=\"text/javascript\">CKEDITOR.addStylesSet( 'my_styles_" . $data['id'] . "', [";
	   
			


			foreach($data['styles'] as $k=>$v) {
				
				$return .= "{ name : '" . $k . "', element : '" . $v['element'] . "', styles : { ";
	
				if(isset($v['styles'])) {
					foreach($v['styles'] as $k2=>$v2) {
						
						$return .= "'" . $k2 . "' : '" . $v2 . "'";
						
						if($k2 !== end(array_keys($v['styles']))) {
							 $return .= ",";
						}
					} 
				} 
			
				$return .= '} }';
				
				if($k !== end(array_keys($data['styles']))) {
					$return .= ',';
				}	    	
				
	
			} 
			
			$return .= ']);';
			
			$return .= "CKEDITOR.instances['" . $data['id'] . "'].config.stylesCombo_stylesSet = 'my_styles_" . $data['id'] . "';
			</script>";		
		}   
	
		return $return;
	}
	
	/**
	 * config_data function.
	 * This function look for extra config data
	 *
	 * @author ronan
	 * @link http://kromack.com/developpement-php/codeigniter/ckeditor-helper-for-codeigniter/comment-page-5/#comment-545
	 * @access public
	 * @param array $data. (default: array())
	 * @return String
	 */
	function config_data($data = array())
	{
		$return = '';
		foreach ($data as $key)
		{

			if (is_array($key)) {
				$return .= "[";
				foreach ($key as $string) {
					$return .= "'" . $string . "'";
					$values = array_values($key);
					$end = end($values);
					if ($string != $end) {
                        $return .= ",";
                    }
				}
				$return .= "]";
			}
			else {
				$return .= "'".$key."'";
			}
			$keys = array_values($data);
			if ($key != end($keys)) $return .= ",";
	
		}


		return $return;

	}
	/**
	 * build_data function.
	 *
	 * @author Mai The Phuong
	 * @access public
	 * @param int $subfolder <total of sub directory of webroot where this class is called>
	 * @return void
	 */
	 function build_data_config(){
			//Ckeditor's configuration
         $root = \App\Helper::getUrl();
		$this->data['Full'] = array(
		
			//ID of the textarea that will be replaced
			'id' 	=> 	'content',
			'path'	=>	$root.'js/ckeditor',
		
			//Optionnal values
			'config' => array(
				'width' 	=> 	"550px",	//Setting a custom width
				'height' 	=> 	'100px',	//Setting a custom height
				'defaultLanguage' => 'en',
				'contentsLangDirection' => 'ltr',
				'scayt_autoStartup' => false,
				'entities' => true,
				'enterMode' => 1,
				'shiftEnterMode' => 2,
				'htmlEncodeOutput'=>true,
				'filebrowserBrowseUrl'      => $root.'js/ckfinder/ckfinder.html',
                'filebrowserImageBrowseUrl' => $root.'js/ckfinder/ckfinder.html?Type=Images',
                'filebrowserFlashBrowseUrl' => $root.'js/ckfinder/ckfinder.html?Type=Flash',
                'filebrowserUploadUrl'      => $root.'js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
                'filebrowserImageUploadUrl' => $root.'js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
                'filebrowserFlashUploadUrl' => $root.'js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash',
				'extraPlugins' => 'fontawesome,print,backgrounds,quicktable,templates,oembed',
				//'font_names' => 'GoogleWebFonts',
				'contentsCss' => array($root.'js/ckeditor/plugins/fontawesome/css/font-awesome.min.css',$root.'css/bootstrap.min.css'),
				'language' =>'vi',
				'removePlugins' =>'forms',
				'toolbar' 	=> 	"MyToolbar", 	//Using the Full toolbar
				'toolbar_MyToolbar' => array(
					array( 'Source'),
					array('Preview','Print','Templates'),
					array('Cut','Copy','Paste','PasteText','PasteFromWord', 'Undo','Redo'),
					array('Find','Replace','RemoveFormat'),
					array('Bold','Italic','Underline','Strike'),
					array('JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'),
					array('Link','Anchor','Unlink'),
					array('Image','Flash','Table','HorizontalRule','SpecialChar','Smiley','PageBreak','Iframe'),
					array('FontStyle','Format','Font','FontSize','-','TextColor','BGColor'),
					array('Maximize', 'ShowBlocks'),
					array('FontAwesome', 'oembed')
				)
			)
		);
		
		$this->data['Basic'] = array(
		
			//ID of the textarea that will be replaced
			'id' 	=> 	'content_2',
			'path'	=>	$root.'js/ckeditor',
			
			//Optionnal values
			'config' => array(
				'width' 	=> 	"550px",	//Setting a custom width
				'height' 	=> 	'100px',	//Setting a custom height
				'defaultLanguage' => 'en',
				'contentsLangDirection' => 'ltr',
				'scayt_autoStartup' => false,
				'entities' => true,
				'enterMode' => 1,
				'shiftEnterMode' => 2,
				'htmlEncodeOutput'=>true,
				'toolbar' 	=> 	array(		//Setting a custom toolbar
					array('Bold', 'Italic'),
					array('Underline', 'Strike', 'FontSize'),
					array('Smiley'),'/'
				)
			),
		
			//Replacing styles from the "Styles tool"
			'styles' => array(
			
				//Creating a new style named "style 1"
				'style 3' => array (
					'name' 		=> 	'Green Title',
					'element' 	=> 	'h3',
					'styles' => array(
						'color' 			=> 	'Green',
						'font-weight' 		=> 	'bold'
					)
				)
								
			)
		);
	 }
}