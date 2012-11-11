<?php

$debugMode =false;

require_once(dirname(__FILE__).'/dynamic_counter.php');


if ( !defined('WP_CONTENT_URL') )
	define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );



class ParameterisedObject {

	var $params=array();

	function ParameterisedObject($params=array()){

		$this->__construct($params);
	}

	function __construct($params=array()){
		$this->setParams($params);
		if(!is_array($this->params)){

			foreach(debug_backtrace() as $trace){

				extract($trace);

				echo "<li>$file:$line $class.$function</li>";

			}
			die("<h1>".get_class($this)."</h1>");
		}
	}


	function setParams($params){
		$this->params=$params;
	}

	function param($key,$default=null){
		if(!array_key_exists($key,$this->params)) return $default;
		return $this->params[$key];
	}
}

class DB_WP_Widget extends ParameterisedObject {

	function DB_WP_Widget($name,$params=array()){
		DB_WP_Widget::__construct($name,$params);
	}

	function __construct($name,$params=array()){
		parent::__construct($params);
		$this->name = $name;
		$this->id = strtolower(get_class($this));
		$options = get_option($this->id);

//		register_sidebar_widget($this->name,array(&$this,'renderWidget'));
		$doesOwnConfig = $this->param('doesOwnConfig',false);
		$desc = $this->param('description',$this->name);
		$widget_ops = array('classname' => $this->id, 'description' => __($desc));
		$control_ops = array('width' => 400, 'height' => 350, 'id_base' => $this->id);
		$name = $this->name;

		$id = false;
		do {

			if($options)

			foreach ( array_keys($options) as $o ) {

				// Old widgets can have null values for some reason

				if ( !isset($options[$o]['exists']) )

					continue;

				$id = "$this->id-".abs($o); // Never never never translate an id

				wp_register_sidebar_widget($id, $name, array(&$this,'renderWidget'), $widget_ops, array( 'number' => $o ));

				wp_register_widget_control($id, $name, array(&$this,'configForm'), $control_ops, array( 'number' => $o ));

			}

			$options = array( -1=>array('exists'=>1));

		} while(!$id);

	}

	function setParams($params){
		parent::setParams($this->overrideParams($params));
	}

	function getDefaults(){
		return array('doesOwnConfig'=>false);
	}

	function overrideParams($params){
		foreach($this->getDefaults() as $k=>$v){
			if(!array_key_exists($k,$params)) $params[$k] = $v;
		}
		return $params;
	}

	function renderWidget(){
		echo "<h1>Unconfigured Widget</h1>";
	}

	function defaultWidgetConfig(){
		return array('exists'=>'1');
	}

	function getConfig($id=null,$key=null){
		$options = get_option($this->id);
		if(is_null($id)) return $options;
		if(!@array_key_exists($id,$options))
			$id = preg_replace('/^.*-(\d+)$/','\\1',$id);
		if(is_null($key))
			return $options[$id];
		else 
			return $options[$id][$key];
	}

	function configForm($args,$force=false){
		static $first;
		global $wp_registered_widgets;
		if ( !is_array($args) )
			$args = array( 'number' => $args );

		$args = wp_parse_args($args,array('number'=>-1));
		static $updated = array();
		$options = get_option($this->id);

		if(!$updated[$this->id] && ($_POST['sidebar'] || $force)){
			$updated[$this->id]=true;
			$sidebar = (string) $_POST['sidebar'];
			$default_options=$this->defaultWidgetConfig();
			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( isset($sidebars_widgets[$sidebar]) )
				$this_sidebar = $sidebars_widgets[$sidebar];
			else
				$this_sidebar = array();

			foreach ( $this_sidebar as $_widget_id ) {
				$callback = $wp_registered_widgets[$_widget_id]['callback'];
			       if(is_array($callback) && get_class($callback[0])==get_class($this) && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {{
				       $widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
			       }
				if ( !in_array( "$this->id-$widget_number", $_POST['widget-id'] ) ) 
					unset($options[$widget_number]);
				}
			}
			foreach ((array)$_POST[$this->id] as $widget_number => $posted) {
				if(!isset($posted['exists']) && isset($options[$widget_number]))continue;
				$widgetOptions = $this->form_processPost($posted,$options[$widget_number]);
				$options[$widget_number] = $widgetOptions;
			}
			update_option($this->id,$options);
		}

		global $mycount;
		if(-1==$args['number']){
			$args['number']='%i%';
			$values = $default_options;
		} else {
			$values = $options[$args['number']];
		}

		$this->form_outputForm($values,$this->id.'['.$args['number'].']');
	}

	function form_processPost($post,$old){
		return array('exists'=>1);
	}

	function form_outputForm($old,$pref){
		$this->form_existsInput($pref);
	}

	function form_existsInput($pref){
		echo "<input type='hidden' name='".$pref."[exists]' value='1'/>";
	}

	function nameAsId(){
		return strtolower(str_replace(" ","_",$this->name));
	}
}



class DB_Search_Widget extends DB_WP_Widget {

	var $inputs = array();

	function DB_Search_Widget($name){
		DB_Search_Widget::__construct($name);
	}

	function __construct($name='Custom',$params=array()){
		$this->loadTranslations();
		parent::__construct(sprintf(__('%1$s Search','wp-custom-fields-search'),$name),$params);
		add_filter('posts_join',array(&$this,'join_meta'));
		add_filter('posts_where',array(&$this,'sql_restrict'));
		add_filter('posts_groupby', array(&$this,'sql_group'));
		add_filter('home_template',array(&$this,'rewriteHome'));
		add_filter('page_template',array(&$this,'rewriteHome'));
		add_filter( 'get_search_query', array(&$this,'getSearchDescription'));
		add_action('wp_head', array(&$this,'outputStylesheets'), 1);
	}

	function loadTranslations(){
		static $loaded;
		if ( !$loaded && function_exists('load_plugin_textdomain') ) {
			$loaded=true;
			if ( !defined('WP_PLUGIN_DIR') ) {
				load_plugin_textdomain('wp-custom-fields-search', str_replace( ABSPATH, '', dirname(__FILE__) ) );
			} else {
				load_plugin_textdomain('wp-custom-fields-search', false, dirname( plugin_basename(__FILE__) ) );
			}
		}
	}

	function addInput($input){
		$this->inputs[] = $input;
	}

	function outputStylesheets(){
		$dir = WP_CONTENT_URL .'/plugins/' .  dirname(plugin_basename(__FILE__) ) . '/';
		echo "\n".'<style type="text/css" media="screen">@import "'. $dir .'templates/searchforms.css";</style>'."\n";
	}

	function getInputs($params){
		return $this->inputs;
	}

	function getTitle(){
		return $this->param('description',$this->name);
	}


	function renderWidget($params=array(),$p2 = array()){
		$title = $this->getTitle($params);
		$inputs = $this->getInputs($params);
		$hidden = "<input type='hidden' name='search-class' value='".$this->getPostIdentifier()."'/><input type='hidden' name='widget_number' value='".$p2['number']."'/>";
		$formCssClass = 'custom_search widget custom_search_'.$this->nameAsId();
		$formAction = get_option('home');
		if(function_exists('locate_template'))
			$formTemplate = locate_template(array('wp-custom-fields-search-form.php'));
		if(!$formTemplate) $formTemplate = dirname(__FILE__).'/templates/wp-custom-fields-search-form.php';

		foreach($inputs as $k=>$v){
			if($v->isHidden()){
				$hidden.=$v->getInput(false);
				unset($inputs[$k]);
			}
		}
		include($formTemplate);
	}

	function isPosted(){
		return $_GET['search-class'] == $this->getPostIdentifier();
	}

	function getPostIdentifier(){
		return get_class($this).'-'.$this->id;
	}

	function isHome($isHome){
		return $isHome && !$this->isPosted();
	}

	function rewriteHome($homeTemplate){
		if($this->isPosted()) return get_query_template('search');
		return $homeTemplate;
	}



	function join_meta($join){
		if($this->isPosted()){
			$desc = array();
			foreach($this->getInputs($_REQUEST['widget_number']) as $input){
				$join = $input->join_meta($join);
				$desc = $input->describeSearch($desc);
			}

			if($desc){
				$desc = join(__(" and ",'wp-custom-fields-search'),$desc);
			} else {
				$desc = __("All Entries",'wp-custom-fields-search');
			}
			$this->desc=$desc;
		}
		return $join;
	}


	function getSearchDescription($desc){
		if($this->isPosted()) return $this->desc;
		return $desc;
	}

	function sql_restrict($where){
		if($this->isPosted()){
			global $wpdb;
			/** This could possibly be considered invasive, need to think this bit through
			 * properly.
			 */
		

			$where = preg_replace("_AND\s*\(ID\s*=\s*'\d+'\)_","",$where);
			$where = preg_replace("/AND $wpdb->posts.post_type = '(post|page)'/","AND $wpdb->posts.post_type = 'ad_listing'",$where);
			foreach($this->getInputs($_REQUEST['widget_number']) as $input){
				$where = $input->sql_restrict($where);
			}
		}
		return $where;
	}

	function sql_group($group){	
		if($this->isPosted()){
			global $wpdb;
			$group = "$wpdb->posts.ID";
		}
		return $group;
	}

	function toSearchString(){

	}
}

class SearchFieldBase {

	function SearchFieldBase(){

		SearchFieldBase::__construct();

	}

	function __construct(){

		add_filter('search_params',array(&$this,'form_inputs'));

		static $index;

		$this->index = ++$index;

	}

	function form_inputs($form){

		die("Unimplemented function ".__CLASS__.".".__FUNCTION__);

	}

	function sql_restrict($where){

		die("Unimplemented function ".__CLASS__.".".__FUNCTION__);

	}

}



class Field extends ParameterisedObject {

	function getValue($name){  
		$v =  $_REQUEST[$this->getHTMLName($name)];

		if(get_magic_quotes_gpc()) $v= stripslashes($v);

		return $v;

	}
	
	function getMultipleValue($name){	
 		$raw = !empty($_SERVER['QUERY_STRING']) ? sprintf('%s&%s', $_SERVER['QUERY_STRING'], $_SERVER["PHP_SELF"]) : "";
        $arr = array();
        $pairs = explode('&', $raw);
        
        foreach ($pairs as $i) {
			if (!empty($i)) {
					$arr = explode('=', $i, 2); //(name, value)
					if (isset($arr[0]) &&  $arr[0] == $this->getHTMLName($name)) {
							$v = $v .$arr[1]."-"; 
					} 
				}
		}
		if($v != "" && strlen($v) > 1 ) $v = substr($v,0, (strlen($v)-1));

		if(get_magic_quotes_gpc()) $v= stripslashes($v);
		
		$v= str_replace("+", " ", $v);		
		$v = rawurldecode($v);
		

		return $v;		
	}


	function getHTMLName($name){

		return 'cs-'.str_replace(" ","_",$name);

	}



	function getInput($name){

		$htmlName = $this->getHTMLName($name);

		$value = $this->getValue($name);

		return "<input name='$htmlName' value='$value'/>";

	}

	function getCSSClass(){

		return get_class($this);

	}

}

class TextField extends Field {

}

class TextInput extends TextField{}



// PATCH: Modify to show categories tree like

class DropDownField extends Field {

	function DropDownField($params=array()){
		$this->__construct($params);
	}

	function __construct($params = array()){
		parent::__construct($params);
		if($optionString = $this->param('dropdownoptions',false)){
			$options=array();
			$optionPairs = explode(',',$optionString);
			foreach($optionPairs as $option){
				list($k,$v) = explode(':',$option);
				if(!$v) $v=$k;
				$options[$k]=$v;
			}
		} else {
			$options = $this->param('options',array());
		}
		$this->options = $options;
	}

	function getOptions($joiner,$name){
		if($this->param('fromDb',!$this->options)){
			$options = array(''=>__('ANY','wp-custom-fields-search'));
			$auto = $joiner->getAllOptions($name);
			asort($auto);
			$options +=$auto;
			return $options;
		} else {
			return $this->options;
		}
	}
	// Check if a cat has parent to build the tree
	function isSubcat($opt, $joiner)
	{
		if (get_class($joiner) == "CategoryJoiner")
		{		
			$terms = get_terms('ad_cat');	
			if ($terms)
			{	
				foreach ($terms as $category)
				{
					if (($category->name == $opt) && ($category->parent))
						return true;	
				}
			}
			else return false;
		}
		else return false;;
	}

	// MOD CATEGORY
/*	function getInput($name,$joiner,$fieldName=null){
		if(!$fieldName) $fieldName=$name;
		$v = $this->getValue($name);
		$id = $this->getHTMLName($name);
		$options = '';
	
		foreach($this->getOptions($joiner,$fieldName) as $option=>$label)
		{
				$checked = ($option==$v)?" selected='true'":"";
				$option = htmlspecialchars($option,ENT_QUOTES);
				$label = htmlspecialchars($label,ENT_QUOTES);
				// tree like category
				if ($this->isSubcat($option, $joiner))
				{
					$options.="<option value='$option'$checked>&nbsp;&nbsp;$label</option>";
				}
				else{ 
					$options.="<option value='$option'$checked>$label</option>";
				}	
		}	
		$atts = '';
		if($this->params['onChange']) $atts = ' onChange="'.htmlspecialchars($this->params['onChange']).'"';
		if($this->params['id']) $atts .= ' id="'.htmlspecialchars($this->params['id']).'"';
		if($this->params['css_class']) $atts .= ' class="'.htmlspecialchars($this->params['css_class']).'"';
		return "<select name='$id'$atts>$options</select>";
}
*/

function getInput($name,$joiner,$fieldName=null){
		if(!$fieldName) $fieldName=$name;
		$v = $this->getValue($name);
		$id = $this->getHTMLName($name);

		$options = '';
		
		
		if (is_admin())
		{
			foreach($this->getOptions($joiner,$fieldName) as $option=>$label)
			{
				$checked = ($option==$v)?" selected='true'":"";
				$option = htmlspecialchars($option,ENT_QUOTES);
				$label = htmlspecialchars($label,ENT_QUOTES);
				$options.="<option value='$option'$checked>$label</option>";
			}
		}
		else 
		{	
			if (get_class($joiner) == "CategoryJoiner")		
			{	
				// TESTS
				$cats = get_categories('hierarchical='.get_option('cp_cat_hierarchy').'&hide_empty='.get_option('cp_cat_hide_empty').'&depth='.get_option('cp_search_depth').'&show_count='.get_option('cp_cat_child_count').'&orderby='.get_option('cp_cat_orderby').'&pad_counts=1&taxonomy='.APP_TAX_CAT);
			
				// remove subcats
				foreach($cats as $key=>$value)
					if ($value->category_parent != 0) unset($cats[$key]);
				
			
				$options .= "<option value='0' selected='selected'>ANY</option>";
				foreach($cats as $cat)
				{
						$checked = ($cat->name==$v)?" selected='selected'":"";
						$option = $cat->name;
						$options.="<option value='$cat->name'>$cat->name ($cat->category_count)</option>";
						$subcats = get_categories('hierarchical='.get_option('cp_cat_hierarchy').'&depth='.get_option('cp_search_depth').'&taxonomy=ad_cat&child_of='.$cat->term_id.'&hide_empty='.get_option('cp_cat_hide_empty').'&show_count='.get_option('cp_cat_child_count').'&orderby='.get_option('cp_cat_orderby').'&pad_counts=1');
						//$cats = get_categories('child_of='.$cat->cat_ID.'&taxonomy=ad_cat&hierarchical='.get_option('cp_cat_hierarchy').'&hide_empty='.get_option('cp_cat_hide_empty').'&depth='.get_option('cp_search_depth').'&show_count='.get_option('cp_cat_child_count').'&orderby='.get_option('cp_cat_orderby').'&pad_counts=1&taxonomy='.APP_TAX_CAT);
						if ($subcats)
						{	
							foreach($subcats as $s) 
								$options.="<option value='$s->name'>&nbsp;&nbsp;$s->name ($s->category_count)</option>";
						}		
				}
			}
			else 
			{
				foreach($this->getOptions($joiner,$fieldName) as $option=>$label){
			$checked = ($option==$v)?" selected='true'":"";
			$option = htmlspecialchars($option,ENT_QUOTES);
			$label = htmlspecialchars($label,ENT_QUOTES);
			$options.="<option value='$option'$checked>$label</option>";
		}
			}
		}
		$atts = '';
		if($this->params['onChange']) $atts = ' onChange="'.htmlspecialchars($this->params['onChange']).'"';
		if($this->params['id']) $atts .= ' id="'.htmlspecialchars($this->params['id']).'"';
		if($this->params['css_class']) $atts .= ' class="'.htmlspecialchars($this->params['css_class']).'"';
		return "<select name='$id'$atts>$options</select>";
		//return "<select id='cat' class='searchbar' name='$id'$atts>$options</select>";
	}
//
	function getConfigForm($id,$values){

		return "<label for='$id-dropdown-options'>".__('Drop Down Options','wp-custom-fields-search')."</label><input id='$id-dropdown-options' name='$id"."[dropdownoptions]' value='$values[dropdownoptions]'/>";

	}

}

class HiddenField extends Field {

	function HiddenField(){

		$func_args = func_get_args();

		call_user_func_array(array($this,'__construct'),$func_args);

	}

	function __construct($params = array()){

		$params['hidden']=true;

		parent::__construct($params);

	}

	function getValue(){

		return $this->param('constant-value',null);

	}



	function getInput($name){

		$v=$this->getValue($name);

		$id = $this->getHTMLName($name);

		return "<input type='hidden' name='".htmlspecialchars($name)."' value='".htmlspecialchars($v)."'/>";

	}

	function getConfigForm($id,$values){

		return "<label for='$id-constant-value'>".__('Constant Value','wp-custom-fields-search')."</label><input id='$id-constant-value' name='$id"."[constant-value]' value='{$values['constant-value']}'/>";

	}

}



/* TODO: Add Caching */

class CustomFieldReader {



}





// NEW FEATURE: Check Box



class CheckBoxField extends Field{

function CheckBoxField($options=array(),$params=array()){

		CheckBoxField::__construct($options,$params);

	}

	function __construct($params=array()){

		parent::__construct($params);
		if($params['checkboxoptions']){

			$options=array();

			$optionPairs = explode(',',$params['checkboxoptions']);

			foreach($optionPairs as $option){

				list($k,$v) = explode(':',$option);

				if(!$v) $v=$k;

				$options[$k]=$v;

			}

		}

		$this->options = $options;

	}

	function getOptions($joiner,$name){
		
		if($this->param('fromDb',!$this->options)){

			return $joiner->getAllOptions($name);

		} else {

			return $this->options;

		}

	}

	function getInput($name,$joiner,$fieldName=null){

		if(!$fieldName) $fieldName=$name;
		
		$v = explode("-", $this->getMultipleValue($name));

		$id = $this->getHTMLName($name);



		$options = '';
		$input_value = array();

		foreach($this->getOptions($joiner,$fieldName) as $option=>$label){

			$option = htmlspecialchars($option,ENT_QUOTES);

			$label = htmlspecialchars($label,ENT_QUOTES);

			$checked = in_array ($option,$v)?" checked='true'":"";
			
			if(in_array ($option,$v))$input_value[] = $option;
			
			$htmlId = "$id-$option";
			$id_function = ereg_replace("[^A-Za-z0-9]","", $id);										

			$options.="<div class='check-box-wrapper'><input type='checkbox' $checked onchange=\"if(this.checked){Fill$id_function('$option');}else{Remove$id_function('$option');};\"> <label for='$htmlId'>$label</label></div>";

		}
			$fill_relleno ="";
			if(count($input_value)>0)  $fill_relleno = "value='". join("-",$input_value) ."'";
			$options.= "<input type='hidden' name='$id' id='$id' $fill_relleno />";
			$options.= "<script>
			function Fill$id_function(option)
			{ var input = document.getElementById('$id'); 
				if(input.value == '') input.value += option;
				else{
						if(input.value.indexOf(option) == -1)  input.value += '-' + option;
						//si ya esta, nada						
					}
			}
			function Remove$id_function(option)
			{
				var input = document.getElementById('$id'); 
				if(input.value != '' && input.value.indexOf(option) != -1) 
				{
					if( input.value.indexOf('-'+option) != -1) //existe con -Valor
						input.value = input.value.replace('-'+option, '');
					else 
						if (input.value.indexOf(option) == 0 ) input.value = input.value.substr(2);
						else input.value = input.value.replace(option, '');
				}
			}
			</script>";
		return $options;

	}

	function getCSSClass(){

		return "CheckBox";

	}

	function getConfigForm($id,$values){

return "";
	}

}



class CheckBoxFromValues extends CheckBoxField {

	function CheckBoxFromValues($fieldName=null){

		CheckBoxFromValues::__construct($fieldName);

	}



	function __construct($fieldName=null,$params){

		$params['fromDb'] = true;

		parent::__construct($options,$params);

	}

	function getConfigForm($id,$values){

		return "";

	}

}

//New Feature 
class SliderPriceField extends Field{

function SliderPriceField($options=array(),$params=array()){

		SliderPriceField::__construct($options,$params);

	}

	function __construct($params=array()){

		parent::__construct($params);
		if($params['sliderpriceoptions']){

			$options=array();

			$optionPairs = explode(',',$params['sliderpriceoptions']);

			foreach($optionPairs as $option){

				list($k,$v) = explode(':',$option);

				if(!$v) $v=$k;

				$options[$k]=$v;

			}

		}

		$this->options = $options;

	}

	function getOptions($joiner,$name){
		
		if($this->param('fromDb',!$this->options)){

			return $joiner->getAllOptions($name, true);

		} else {

			return $this->options;

		}

	}

	function getInput($name,$joiner,$fieldName=null){

		if(!$fieldName) $fieldName=$name;
		
		$v = explode("-", $this->getMultipleValue($name));

		$id = $this->getHTMLName($name);

		$options = '';
		$arr_options = array();
		
		foreach($this->getOptions($joiner,$fieldName) as $option=>$label){

			$option = htmlspecialchars($option,ENT_QUOTES);

			$label = htmlspecialchars($label,ENT_QUOTES);

			$htmlId = "$id";

			$arr_options[] = $option;
		
		}
		sort($arr_options); 
		
		$val_a = $arr_options[0];
		$val_b = $arr_options[(count($arr_options)-1)];
		
		if($_GET[$htmlId] != "")
		{
			$vals = split("-",$_GET[$htmlId]);
			if(count($vals) == 2) 
			{$val_a = $vals[0];
				$val_b = $vals[1];
			}
		}
		
		if(count($arr_options) >=2)
		{
		$result = "<div class='slider-price-wrapper'>
	<script>
	jQuery(document).ready(function(){
		
	jQuery(function() {
		jQuery( '#slider-range').slider({
			range: true,
			min: ".$arr_options[0].",
			max: ".$arr_options[(count($arr_options)-1)].",
			values: [ ".$val_a.", ".$val_b." ],
			slide: function( event, ui ) {	jQuery( '#".$htmlId."' ).val( ui.values[ 0 ] + '-' + ui.values[ 1 ] );	},
			stop: function(event, ui) {	jQuery('.searchform-params').trigger('change');	}
		});
	});
		
	
	});
	</script>
	<div class='searchform-params'><input type='text' id='".$htmlId."' name='".$htmlId."'style='border:0; color:#f6931f; font-weight:bold;' value='".$val_a."-".$val_b."'/>
<div id='slider-range'></div></div>";
$result .= "</div>";
		}else return "";

		return $result; 

	}

	function getCSSClass(){

		return "SliderPrice";

	}

	function getConfigForm($id,$values){

return "";
	}

}



class SliderPriceFromValues extends SliderPriceField {

	function SliderPriceFromValues($fieldName=null){

		SliderPriceFromValues::__construct($fieldName);

	}



	function __construct($fieldName=null,$params){

		$params['fromDb'] = true;

		parent::__construct($options,$params);

	}

	function getConfigForm($id,$values){

		return "";

	}

}



class DropDownFromValues extends DropDownField {

	function DropDownFromValues($params=array()){

		$this->__construct($params);

	}



	function __construct($params=array()){

		$params['fromDb'] = true;

		parent::__construct(array(),$params);

	}



	function getConfigForm($id,$values){

		return "";

	}

}

class RadioButtonField extends Field {
	function RadioButtonField($options=array(),$params=array()){
		RadioButtonField::__construct($options,$params);
	}

	function __construct($params=array()){
		parent::__construct($params);
		if($params['radiobuttonoptions']){
			$options=array();
			$optionPairs = explode(',',$params['radiobuttonoptions']);
			foreach($optionPairs as $option){
				list($k,$v) = explode(':',$option);
				if(!$v) $v=$k; 
				$options[$k]=$v;
			}
		}
		$this->options = $options;
	}
	function getOptions($joiner,$name){
		if($this->param('fromDb',!$this->options)){
			return $joiner->getAllOptions($name);
		} else {
			return $this->options;
		}
	}

	function getInput($name,$joiner,$fieldName=null){
		if(!$fieldName) $fieldName=$name;
		$v = $this->getValue($name);
		$id = $this->getHTMLName($name);

		$options = '';
		foreach($this->getOptions($joiner,$fieldName) as $option=>$label){
			$option = htmlspecialchars($option,ENT_QUOTES);
			$label = htmlspecialchars($label,ENT_QUOTES);
			$checked = ($option==$v)?" checked='true'":"";
			$htmlId = "$id-$option";
			$options.="<div class='radio-button-wrapper'><input type='radio' name='$id' id='$htmlId' value='$option'$checked> <label for='$htmlId'>$label</label></div>";
		}
		return $options;
	}

	function getCSSClass(){
		return "RadioButton";
	}

	function getConfigForm($id,$values){
		return "<label for='$id-radiobutton-options'>Radio Button Options</label><input id='$id-radiobutton-options' name='$id"."[radiobuttonoptions]' value='$values[radiobuttonoptions]'/>";
	}
}

class RadioButtonFromValues extends RadioButtonField {
	function RadioButtonFromValues($fieldName=null){
		RadioButtonFromValues::__construct($fieldName);
	}

	function __construct($fieldName=null,$params){
		$params['fromDb'] = true;
		parent::__construct($options,$params);
	}

	function getConfigForm($id,$values){
		return "";
	}
}

class Comparison {
	function addSQLWhere($field,$value){
		die("Unimplemented function ".__CLASS__.".".__FUNCTION__);
	}

	function describeSearch($value){
		die("Unimplemented function ".__CLASS__.".".__FUNCTION__);
	}
}

class EqualComparison extends Comparison {
	function addSQLWhere($field,$value)
	{
		$result = "";
		$divider_or = "";
						
		$values = explode("-",$value);
		foreach($values as $val)
		{	
			$result .= $divider_or . "$field = '$val'";			
			$divider_or = " OR ";
		}
		return "(". $result . ")";
	}

	function describeSearch($value)
	{
	$value = explode("-",$value);
	$aux = array();

	foreach ($value as $val)
		$aux[] = "\"".join("\" and \"",explode(" ",$val)) . "\"";		  
	$txt =  "".join(" , ", $aux). "";
		
	return sprintf(__(' is %1$s','wp-custom-fields-search'), $txt);
	}
}

class LikeComparison extends Comparison{

	function addSQLWhere($field,$value){
		$result = "";
		$divider_or = "";
		
		$values = explode("-",$value);
		foreach($values as $val){
			
			$words = explode(" ",$val);

			$like = array(1);

			foreach($words as $word){

				$like[] = $this->getLikeString($field,$word); 

			}
			
			$result .= $divider_or . $this->getLikeString($field,$val);
			
			$divider_or = " OR ";
		}

		return "(". $result . ")";

	}

	function getLikeString($field,$value){

		return "$field LIKE '%$value%'";

	}

	function describeSearch($value){
		
		$value = explode("-",$value);
	$aux = array();
	
	foreach ($value as $val)
		$aux[] = "\"".join("\" and \"",explode(" ",$val)) . "\"";
									  
	$txt =  "".join(" , ", $aux). "";
		
	return sprintf(__(' contains %1$s','wp-custom-fields-search'), $txt);
	}

}



class WordsLikeComparison extends LikeComparison {

	function addSQLWhere($field,$value){

		$result = "";
		$divider_or = "";
		
		$values = explode("-",$value);
		foreach($values as $val){
			
			$words = explode(" ",$val);

			$like = array(1);

			foreach($words as $word){

				$like[] = $this->getLikeString($field,$word); 

			}
			
			$result .= $divider_or .join(" AND ",$like);
			
			$divider_or = " OR ";
		}
		
		return  "(".$result.")";

	}

	function describeSearch($value){

	$value = explode("-",$value);
	$aux = array();
	
	foreach ($value as $val)
		$aux[] = "\"".join("\" and \"",explode(" ",$val)) . "\"";
									  
	$txt =  "".join(" , ", $aux). "";
		
	return sprintf(__(' contains %1$s','wp-custom-fields-search'), $txt);

	}

}

class LessThanComparison extends Comparison{

	function addSQLWhere($field,$value){
		
		$result = "";
		$divider_or = "";
		
		$values = explode("-",$value);
		foreach($values as $val){
			
			$result .= $divider_or . "$field < '$val'";			
			$divider_or = " OR ";
			
		}
		return "(". $result . ")";

	}

	function describeSearch($value){

				$value = explode("-",$value);
	$aux = array();
	
	foreach ($value as $val)
		$aux[] = "\"".join("\" and \"",explode(" ",$val)) . "\"";
									  
	$txt =  "".join(" , ", $aux). "";
		
	return sprintf(__(' less than %1$s','wp-custom-fields-search'), $txt);

	}

}

class AtMostComparison extends Comparison{

	function addSQLWhere($field,$value){

		$result = "";
		$divider_or = "";
		
		$values = explode("-",$value);
		foreach($values as $val){
			
			$result .= $divider_or . "$field <= '$val'";			
			$divider_or = " OR ";
			
		}
		return "(". $result . ")";

	}

	function describeSearch($value){

		//return sprintf(__(' at most "%1$s"','wp-custom-fields-search'),$value);
	$value = explode("-",$value);
	$aux = array();
	
	foreach ($value as $val)
		$aux[] = "\"".join("\" and \"",explode(" ",$val)) . "\"";
									  
	$txt =  "".join(" , ", $aux). "";
		
	return sprintf(__(' at most %1$s','wp-custom-fields-search'), $txt);

	}

}

class AtLeastComparison extends Comparison{

	function addSQLWhere($field,$value){

		$result = "";
		$divider_or = "";
		
		$values = explode("-",$value);
		foreach($values as $val){
			
			$result .= $divider_or . "$field >= '$val'";			
			$divider_or = " OR ";
			
		}
		return "(". $result . ")";
	}

	function describeSearch($value){

		$value = explode("-",$value);
	$aux = array();
	
	foreach ($value as $val)
		$aux[] = "\"".join("\" and \"",explode(" ",$val)) . "\"";
									  
	$txt =  "".join(" , ", $aux). "";
		
	return sprintf(__(' at least %1$s','wp-custom-fields-search'), $txt);

	}

}

class MoreThanComparison extends Comparison{

	function addSQLWhere($field,$value){

		$result = "";
		$divider_or = "";
		
		$values = explode("-",$value);
		foreach($values as $val){
			
			$result .= $divider_or . "$field > '$val'";			
			$divider_or = " OR ";
			
		}
		return "(". $result . ")";

	}

	function describeSearch($value){

		$value = explode("-",$value);
	$aux = array();
	
	foreach ($value as $val)
		$aux[] = "\"".join("\" and \"",explode(" ",$val)) . "\"";
									  
	$txt =  "".join(" , ", $aux). "";
		
	return sprintf(__(' more than %1$s','wp-custom-fields-search'), $txt);

	}

}

class RangeComparison extends Comparison{ 

	function addSQLWhere($field,$value){

		list($min,$max) = explode("-",$value);

		$where=1;

		if(strlen($min)>0) $where.=" AND $field >= $min";

		if(strlen($max)>0) $where.=" AND $field <= $max";

		return $where;

	}

	function describeSearch($value){

		list($min,$max) = explode("-",$value);

		if(strlen($min)==0) return sprintf(__(' less than "%1$s"','wp-custom-fields-search'),$max);

		if(strlen($max)==0) return sprintf(__(' more than "%1$s"','wp-custom-fields-search'),$min);

		return sprintf(__(' between "%1$s" and "%2$s"','wp-custom-fields-search'),$min,$max);

	}

}

class NotEqualComparison extends Comparison {

	function addSQLWhere($field,$value){
		$result = "";
		$divider_or = "";
		
		$values = explode("-",$value);
		foreach($values as $val){
			
			$result .= $divider_or . "$field != '$val'";			
			$divider_or = " OR ";
			
		}
		return "(". $result . ")";

	}

	function describeSearch($value){
		
		$value = explode("-",$value);
	$aux = array();
	
	foreach ($value as $val)
		$aux[] = "\"".join("\" and \"",explode(" ",$val)) . "\"";
									  
	$txt =  "".join(" , ", $aux). "";
		
	return sprintf(__(' is not %1$s','wp-custom-fields-search'), $txt);

	}

}



class BaseJoiner extends ParameterisedObject {

	function BaseJoiner($name=null,$params=array()){

		$this->__construct($name,$params);

	}

	function __construct($name=null,$params=array()){

		parent::__construct($params);

		$this->name=$name;

	}

	function sql_join($join,$name,$index,$value){

		return $join;

	}

	function process_where($where){

		return $where;

	}

	function needsField(){

		return true;

	}

}

class CustomFieldJoiner extends BaseJoiner{

	function CustomFieldJoiner($name,$params){

		$this->__construct($name,$params);

	}

	function __construct($name,$params){

		$this->params = $params;

	}

	function param($key,$default=null){

		if(array_key_exists($key,$this->params)) return $this->params[$key];

		return $default;

	}

	function sql_restrict($name,$index,$value,$comparison){
		
		if($value != "" && $value != "-")
		{
		$table = 'meta'.$index;

		$field = "$table.meta_value".($this->param('numeric',false)?'*1':'');
		
		$comp = " AND ".$comparison->addSQLWhere($field,$value);

		if($name!='all')

			$comp = " AND ( $table.meta_key='$name' ".$comp.") ";

		return $comp;
		}
		else return "";

	}

	function sql_join($join,$name,$index,$value){

		if(!$value && !$this->param('required',false)) return $join;

		global $wpdb;

		$table = 'meta'.$index;

		return "$join JOIN $wpdb->postmeta $table ON $table.post_id=$wpdb->posts.id";

	}

	function getAllOptions($fieldName)
	{
		
		global $wpdb;

		$where='';

		if($fieldName!='all')
			$where = " WHERE meta_key='$fieldName'";

		$q = mysql_query($sql = "SELECT DISTINCT meta_value FROM $wpdb->postmeta m JOIN $wpdb->posts p ON m.post_id=p.id AND p.post_status='publish' $where");
		
		$options = array();

		if($this->params["input"] == "CheckBoxField")
		{
			while($r = mysql_fetch_row($q))
				if($r[0] != "")	$options[$r[0]] = $r[0];
		}
		else
		{
			while($r = mysql_fetch_row($q))
			$options[$r[0]] = $r[0];
		}
		return $options;

	}

	function getSuggestedFields(){
		
		global $wpdb;
		
		$q = mysql_query($sql = "SELECT DISTINCT meta_key FROM $wpdb->postmeta WHERE meta_key NOT LIKE '\\_%'");

		$options = array('all'=>'All Fields');

		while($r = mysql_fetch_row($q))

			$options[$r[0]] = $r[0];
			
		return $options;
	}
	function getSuggestedFields2(){
		
		global $wpdb;

		
		$q = mysql_query($sql = "SELECT DISTINCT meta_key FROM $wpdb->postmeta WHERE meta_key='cp_price'");
		
		while($r = mysql_fetch_row($q))

			$options[$r[0]] = $r[0];
			
		return $options;
	}

}

class CategoryJoiner extends BaseJoiner {

	function sql_restrict($name,$index,$value,$comparison){
		// MOD CATEGORY
		//if(!($value || $this->params['required'])) return $join;
		//$table = 'meta'.$index;
		//return " AND ( ".$comparison->addSQLWhere("$table.name",$value).")";
	}

	function getTaxonomy(){
		//****** PATCH: WORKING WITH ADS CATEGORIES
		//return $this->param('taxonomy','category');
		return $this->param('taxonomy','ad_cat');
	}

	function getTaxonomyWhere($table){
		return "`$table`.taxonomy='".$this->getTaxonomy()."'";
	}

	// CATEGORY MOD
	/*
	function sql_join($join,$name,$index,$value)
	{

		if(!($value || $this->params['required'])) return $join;
		global $wpdb;
		$table = 'meta'.$index;
		$rel = 'rel'.$index;
		$tax = 'tax'.$index;
		return $join." JOIN $wpdb->term_relationships $rel ON $rel.object_id=$wpdb->posts.id JOIN  $wpdb->term_taxonomy $tax ON $tax.term_taxonomy_id=$rel.term_taxonomy_id JOIN $wpdb->terms $table ON $table.term_id=$tax.term_id AND ".$this->getTaxonomyWhere($tax);
	}*/
	function sql_join($join,$name,$index,$value){
		if(!($value || $this->params['required'])) return $join;
		global $wpdb;
		$table = 'meta'.$index;
		$rel = 'rel'.$index;
		$tax = 'tax'.$index;
		
		// MOD for Dieter
		$join.=" JOIN $wpdb->term_relationships $rel ON $rel.object_id=$wpdb->posts.id JOIN  $wpdb->term_taxonomy $tax ON $tax.term_taxonomy_id=$rel.term_taxonomy_id JOIN $wpdb->terms $table ON $table.term_id=$tax.term_id AND ".$this->getTaxonomyWhere($tax);
		
		$catid = get_term_by('name', $value, 'ad_cat');
		$catid = $catid->term_id;
		
		if (!empty($catid))
		{
			(array) $include_cats[] = $catid;
			$descendants = get_term_children((int)$catid, 'ad_cat');
			foreach ( $descendants as $k => $v )
			{
					$include_cats[] = $v;
			}
			
			$include_cats = '"' . implode( '", "', $include_cats ) . '"';		
			$join.= " AND $tax.term_id IN ($include_cats) ";
		}
		
		return $join;
	}
	//

	function getAllOptions($fieldName){

		global $wpdb;

		$sql = "SELECT distinct t.name FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id INNER JOIN $wpdb->term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id JOIN $wpdb->posts p ON tr.object_id=p.id AND p.post_status='publish' WHERE ".$this->getTaxonomyWhere('tt');

		$q = mysql_query($sql);

		if($e = mysql_error()) echo "<h1>SQL: $sql</h1>".mysql_error();

		$options = array();

		// WORKING ON: Subcategory Tree

		while($r = mysql_fetch_row($q))

		{

			$options[$r[0]] = $r[0];

		}

		return $options;

	}

	function needsField(){

		return false;

	}

	

}

class TagJoiner extends CategoryJoiner {

	function getTaxonomy(){

			//PATCH

			return $this->param('taxonomy','ad_tag');

	}

}



class PostTypeJoiner extends BaseJoiner {

	function process_where($where){

		global $wpdb;

		// TODO: Check here for PostType search!

		$where = preg_replace("/AND \($wpdb->posts. *= *'(ad_listing)'\)/","",$where);

		

		return $where;

	}

	function sql_restrict($name,$index,$value,$comparison){

		global $wpdb;

		if(!($value || $this->params['required'])) return $join;

		return " AND ( ".$comparison->addSQLWhere("$wpdb->posts.",$value).")";

	}

	function getAllOptions($fieldName){

		global $wpdb;

		$q = mysql_query("SELECT distinct FROM $wpdb->posts p WHERE post_status='publish'") or die(mysql_error());

		print_r($q);

		$options = array();

		while($r = mysql_fetch_row($q))

			$options[$r[0]] = $r[0];

		return $options;

	}

	function needsField(){

		return false;

	}

}



class PostDataJoiner extends BaseJoiner {

	function sql_restrict($name,$index,$value,$comparison){

		global $wpdb;

		$table = $wpdb->posts;

		if($name=='all'){

			$logic = array();

			foreach($this->getSuggestedFields() as $name=>$desc){

				if($name=='all') continue;

				$logic[] =  "( ".$comparison->addSQLWhere("$table.$name",$value).") ";

			}

			$logic = " AND (".join(" OR ",$logic).")";

			return $logic;

		} else {

			return " AND ( ".$comparison->addSQLWhere("$table.$name",$value).") ";

		}

	}

	function sql_join($join,$name,$index,$value){

		return $join;

	}

	function getAllOptions($fieldName){

		

		global $wpdb;

		global $post;



		$q = mysql_query("SELECT $fieldName FROM $wpdb->posts");

		$options = array();

		$i=0;

		while($r = mysql_fetch_row($q))

		{

			$options[$r[$i]] = $r[$i];

			$i++;

		}

		return $options;

		

	}

	

	function getSuggestedFields(){

		return array(

			'all'=>__('All Fields','wp-custom-fields-search'),

			'post_content'=>__('Body Text','wp-custom-fields-search'),

			'post_title'=>__('Title','wp-custom-fields-search'),

			'post_author'=>__('Author','wp-custom-fields-search'),

			'post_date'=>__('Date','wp-custom-fields-search'),

		);

	}

}



class CategorySearch {

}



class CustomSearchField extends SearchFieldBase {

	function CustomSearchField($nameOrParams,$input=false,$comparison=false,$joiner=false){

		CustomSearchField::__construct($nameOrParams,$input,$comparison,$joiner);

	}

	function __construct($nameOrParams,$input=false,$comparison=false,$joiner=false){

		parent::__construct();

		if(!is_array($nameOrParams)){

			$params = array('name'=>$nameOrParams);

		} else {

			$params = $nameOrParams;

		}

		$this->name = $params['name'];

		$this->params = $params;



		$this->joiner = $joiner;

		$this->comparison = $comparison;

		$this->input = $input;

		

		if(!is_object($this->input)){

			$input = $this->param('input','TextField');

			$this->input = new $input($params);

		}

		if(!is_object($this->comparison)){

			$comparison = $this->param('comparison','LikeComparison');

			$this->comparison = new $comparison();

		}

		if(!is_object($this->joiner)){

			$joiner = $this->param('joiner','CustomFieldJoiner');

			$this->joiner = new $joiner($this->param('name'),$this->params);
			
		}





	}

	function setIndex($n){

		$this->index=$n;

	}

	function param($key,$default=null){

		if(array_key_exists($key,$this->params)) return $this->params[$key];

		return $default;

	}



	function stripInitialForm($form){

		$pref='<!--cs-form-->';

		if(preg_match("/^$pref/",$form)) return $form;

		else return $pref;

	}



	function form_inputs($form){

		$form = $this->stripInitialForm($form);

		return $form.$this->getInput($this->name,$this->joiner);

	}

	function hasValue(){

		return $this->getValue();

	}

	function sql_restrict($where){
		
		if($this->hasValue()){

			$value = $this->getValue();

			$value = $GLOBALS['wpdb']->escape($value);

			$where.=$this->joiner->sql_restrict($this->name,$this->index,$value,$this->comparison);

		}

		if(method_exists($this->joiner,'process_where'))

			$where = $this->joiner->process_where($where);

		return $where;

	}

	function describeSearch($current){

		if($this->hasValue()){

			$current[] = $this->getLabel()." ".$this->comparison->describeSearch($this->getValue());

		}

		return $current;



	}

	function join_meta($join){

		global $wpdb;

		$join=$this->joiner->sql_join($join,$this->name,$this->index,$this->getValue(),$this->comparison);

		return $join;

	}



	function getQualifiedName(){

		// PATCH: Adding cat prefix to identify this kind of control

		if (get_class($this->joiner) == "CategoryJoiner")

			return 'cat-'.$this->name.'-'.$this->index;

		else

			return $this->name.'-'.$this->index;

	}

	function getOldValue(){ return $this->getValue(); }

	function getValue(){
		
		$v = $this->input->getValue($this->getQualifiedName(),$this->name);

		return $v;

	}

	function getLabel(){

		if(!$this->params['label']) $this->params['label'] = ucwords($this->name);

		return $this->params['label'];

	}



	function isHidden(){

		return $this->input->param('hidden',false);

	}

	function getInput($wrap=true){

		

		$input = $this->input->getInput($this->getQualifiedName(),$this->joiner,$this->name);

		if($wrap){

			$input = "<div class='searchform-param'><label class='searchform-label'>".$this->getLabel()."</label><span class='searchform-input-wrapper'>$input</span></div>";

		}

		return $input;

	}

	function getCSSClass(){

		return method_exists($this->input,'getCSSClass')?$this->input->getCSSClass():get_class($this->input);

	}

}



function wp_custom_search_fields_include_bridges(){

	$dir = opendir($path = dirname(__FILE__).'/bridges');

	while($file = readdir($dir)){

		if(is_file("$path/$file") && preg_match("/^[^.].*\.php$/",$file)){

			require_once("$path/$file");

		}

	}

}

wp_custom_search_fields_include_bridges();



if($debugMode){
	add_filter('posts_request','debug_dump_query');

	function debug_dump_query($query){

		echo "<h1>$query</h1>";
	
		return $query;

	}
}
?>