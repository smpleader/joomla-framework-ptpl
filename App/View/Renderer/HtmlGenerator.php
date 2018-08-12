<?php 

namespace App\View\Renderer;

class HtmlGenerator{ 

	static $singleTags = ['meta','input','hr','br','img','image'] ;
	
	public static function __callStatic( $tagName, $arguments){
		if( !method_exists( '\App\View\Renderer\HtmlGenerator' , $tagName ) )
			return call_user_func_array( array( '\App\View\Renderer\HtmlGenerator', '_anyTag') , array( $tagName , $arguments ) );
	}
	
	private static function _anyTag( $tagName, $args ){
		switch( count( $args ) ){
			case 2 :
				list( $attr , $txt ) = $args ; 
				if( !is_string($txt) && is_callable( $txt ) ) $txt = $txt(); // self::_callback( $txt ) ;				 
				if( !is_string($txt) && is_callable( $attr ) ) $attr = $attr(); //self::_callback( $attr ) ;
				return '<'.$tagName.' '.$attr.' >'.$txt.'</'.$tagName.'>' ;
				
			case 1 : 
				$txt = $args[0] ;
				if( !is_string($txt) && is_callable( $txt ) ) $txt = $txt(); // self::_callback( $args[0] ) ;		
				return in_array( $tagName, self::$singleTags ) ? '<'.$tagName.' '.$txt.' />' : '<'.$tagName.'>'.$txt.'</'.$tagName.'>' ;
				
			default : return '<'.$tagName.'/>' ;
		}
	}
	
	private static function _callback( $fnc , $ob_start = true ){		
		if( !$ob_start ) return $fnc() ;			
		ob_start() ; $fnc() ; return ob_get_clean();
	}
	
	static function select( $attr='' , $something=false , $selected=null ){
		$txtOptions	= '' ; 
		$options	= array() ; 
		 
		if( $something ) :
			if( is_array( $something ) ) 
				$options = $something ;
			elseif( !is_string($txt) && is_callable( $something ) ) 
				$options = $something() ;//self::_callback( $something );
			elseif( is_string( $something ) ) 
				$txtOptions = $something ;
		endif ;
		 
		
		if( count( $options ) && empty( $txtOptions ) ){
			foreach( $options as $option ){
				if( !is_array( $option ) ) $option = array( $option ) ;
				$txtOptions .= call_user_func_array( array( '\App\View\Renderer\HtmlGenerator', 'option') , $option );
			}
		}
		
		return '<select '.$attr.'>'.$txtOptions.'</select>' ;
	}
	
	static function option( $txt , $value=null, $seleted=false , $attr='' ){
		if( $value === null ) $value = $txt ;
		$isSeleted =  $seleted ? 'selected="selected" ' : '' ;
		return '<option value="'.$value.'" '.$attr.' '.$isSeleted.'>'.$txt.'</option>' ;
	}
	
	static function link( $src ){ return '<link href="'.$src.'" rel="stylesheet" />' ; }
	
	static function script( $js , $content=true ){
		if(is_string($content)) return '<script '.$content.' >'.$js.'</script>' ;
		return $content ? '<script type="text/javascript" >'.$js.'</script>' : '<script type="text/javascript" src="'.$js.'" ></script>' ;
	}
}
