<?php
/****************** DO NOT REMOVE OR ALTER THIS HEADER ***************
*                                                                    *
* Product: Coordinate                                                *
* A simple PHP class for Coordinates                                 *
*                                                                    *
* Latest version to download:                                        *
* https://github.com/sentfanwyaerda/Coordinate                       *
*                                                                    *
* Documentation:                                                     *
* https://github.com/sentfanwyaerda/Coordinate/blob/master/README.md *
*                                                                    *
* Authors:                                                           *
* Sent fan Wy&aelig;rda (fsnode@sent.wyaerda.org) [creator, main]    *
*                                                                    *
* License: cc-by-nd                                                  *
* Creative Commons, Attribution-No Derivative Works 3.0 Unported     *
* http://creativecommons.org/licenses/by-nd/3.0/                     *
* http://creativecommons.org/licenses/by-nd/3.0/legalcode            *
*                                                                    *
****************** CHANGES IN THE CODE ARE AT OWN RISK **************/
if(!class_exists('Xnode')){
	class Xnode { }
}
class coordinate extends Xnode {
	var $data; #x:y:z?:(r+s|type)?:t?:idref?
	
	function coordinate($value=NULL){
		if($this->is_well_formed($value)){ $this->data = $value; }
	}
	
	private function continue_array($array, $to=0, $value=NULL){
		if(!is_array($array)){ $array = array(); }
		for($i=0;$i<$to;$i++){
			if(!isset($array[$i])){ $array[$i] = $value; }
		}
		return $array;
	}
	
	function set($var, $value=NULL){
		$set = explode(':', $this->data);
		$set = self::continue_array($set, 6);
		switch(strtolower($var)){
			case 'x': $set[0] = $value; break;
			case 'y': $set[1] = $value; break;
			case 'z': $set[2] = $value; break;
			case 'r': case 'radius': /*empty*/ break;
			case 'type': /*empty*/ break;
			case 'scale': /*empty*/ break;
			case 't': case 'time': $set[4] = $value; break;
			case 'idref': $set[5] = $value; break;
			default: return /*error*/ FALSE;
		}	
		$this->data = implode(':', $set);
	}
	
	function set_x($v=NULL){ return self::set('x', $v); }
	function set_y($v=NULL){ return self::set('y', $v); }
	function set_z($v=NULL){ return self::set('z', $v); }
	function set_r($v=NULL){ return self::set('r', $v); }
	function set_radius($v=NULL){ return self::set_r($v); }
	function set_type($v=NULL){ return self::set('type', $v); }
	function set_scale($v=NULL){ return self::set('scale', $v); }
	function set_t($v=NULL){ return self::set('t', $v); }
	function set_time($v=NULL){ return self::set_t($v); }
	function set_idref($v=NULL){ return self::set('idref', $v); }

	function get($var /*, $other=FALSE*/){
		$set = explode(':', $this->data);
		$set = self::continue_array($set, 6);
		switch(strtolower($var)){
			case 'x': return (double) str_replace(',', '.', $set[0]); break;
			case 'y': return (double) str_replace(',', '.', $set[1]); break;
			case 'z': return (double) str_replace(',', '.', $set[2]); break;
			case 'r': case 'radius': 
				switch(strtoupper(self::get_type())){
					case 'EARTH':	return 6371000; break;
					case 'MOON':	return 1737100; break;
					case 'MARS':	return ((3396.2 + 3376.2)/2 * 1000); break;
					case 'MAP':		return NULL; break;
					default:
						return (double) preg_replace('#^([^0-9,.]+)?([0-9,.-]+)('.implode('|', self::_scales()).')?$#', '\\2', $set[3]);
				}
			break;
			case 'type':
				$options = array('EARTH','MOON','MARS','SPHERE','MAP');
				if(preg_match('#^(SPHERE\s)?[0-9,.-]+('.implode('|', self::_scales()).')$#i', $set[3])){ return 'SPHERE'; }
				return (in_array(strtoupper($set[3]), $options) ? strtoupper($set[3]) : (FALSE ? 'SPHERE' : 'MAP'));
				break;
			case 'scale-z': case 'scale': 
				switch(self::get_type()){
					case 'EARTH': case 'MOON': case 'MARS':
						if(strtolower($var) == 'scale-z'){ return 'm'; }
					case 'SPHERE':
						if(strtolower($var) != 'scale-z'){ return 'degree'; }
						break;
					default: /*MAP*/
				}
				$blob = preg_replace('#^(SPHERE\s)?([0-9,.-]+)?('.implode('|', self::_scales()).')$#i', '\\3', $set[3]);
				return /*mm|cm|inch|ft|m|km|miles|EA|lightyears*/ (strlen($blob) > 0 ? strtolower($blob) : NULL);
				break;
			case 't': case 'time': return $set[4]; break;
			case 'idref': return $set[5]; break;
			#case 'delta': return $this->get_delta($other); break;
			default: return /*error*/ FALSE;
		}
	}
	private function _scales(){ return array('mm','cm','inch','ft','m','km','miles','EA','lightyears'); }
	
	function get_x($with_scale=FALSE){ return ($with_scale == FALSE ? self::get('x') : (string) self::get('x').(self::get_scale() == 'degree' ? '&deg;' : self::get_scale())); }
	function get_y($with_scale=FALSE){ return ($with_scale == FALSE ? self::get('y') : (string) self::get('y').(self::get_scale() == 'degree' ? '&deg;' : self::get_scale())); }
	function get_z($with_scale=FALSE){ return ($with_scale == FALSE ? self::get('z') : (string) self::get('z').(self::get('scale-z') == 'degree' ? 'm' : self::get('scale-z'))); }
	function get_r($with_scale=FALSE){ return ($with_scale == FALSE ? self::get('r') : (self::get_scale() == 'degree' ?  (string) self::get('r').'m' : NULL)); }
	function get_radius($with_scale=FALSE){ return self::get_r($with_scale); }
	function get_type(){ return self::get('type'); }
	function get_scale(){ return self::get('scale'); }
	function get_scale_z(){ return self::get('scale-z'); }
	function get_t(){ return self::get('t'); }
	function get_time(){ return self::get_t(); }
	function get_idref(){ return self::get('idref'); }
	
	function get_delta($other=FALSE, $compare=array(/*x-x,y-y|y-y,z-z|x-x,z-z|x-x,y-y,z-z : count() = 2|3 */)){}
	function get_d($other){}
	function get_distance($other=FALSE){ return self::get_d($other); }
	
	function get_s($other){}
	function get_speed($other){ return self::get_s($other); }
	
	function get_a(/*array*/ $other){}
	function get_accelaration($other){ return self::get_a($other); }
	
	function is_degree($flag /*or value*/ ){
		switch($flag){
			case 'x': break;
			case 'y': break;
			default: /*assume (double) value*/
		}	
	}
	function is_valid(){ return self::is_well_formed(); }
	function is_well_formed($string=NULL){ return /*debug*/ TRUE; }

	public /*double|degree|FALSE*/ function pythagoras($a=NULL, $b=NULL, $c=NULL, $degree=90, $corner="opposite" /*|alpha|beta|gamma*/ ){
		/*********************************************************************************
		 * A triangle (sides a, b, c) with the angles:
		 * alpha is opposite of a
		 * beta is opposite of b
		 * gamma is opposite of c, at 90 degrees in the Pythagoran theorom ( a^2+b^2=c^2 )
		 *********************************************************************************/
		if($a !== NULL && $b !== NULL && $c !== NULL){ #find the corner in degrees
			$question = ((double) $degree == 90 || $degree == NULL ? $corner : $degree);
			switch(strtolower($question)){
				case 'alpha': return rad2deg(acos(( pow((double) $b, 2) + pow((double) $c, 2) - pow((double) $a, 2) ) / (2* (double) $b * (double) $c))); break;
				case 'beta': return rad2deg(acos(( pow((double) $a, 2) + pow((double) $c, 2) - pow((double) $b, 2) ) / (2* (double) $a * (double) $c))); break;
				case 'gamma': case 'opposite': return rad2deg(acos(( pow((double) $a, 2) + pow((double) $b, 2) - pow((double) $c, 2) ) / (2* (double) $a * (double) $b))); break;
				default:
					$set = array();
					foreach(array('alpha','beta','gamma') as $find){ $set[$find] = self::pythagoras($a, $b, $c, $find); }
					if(in_array($degree, $set)){ return array_search($degree, $set); }
					return /*error*/ FALSE;
			}
		}
		elseif((double) $degree == (double) 90){ #apply Pythagoran Theorom
			if($a === NULL && $b !== NULL && $c !== NULL){
				return sqrt( pow((double) $c, 2) - pow((double) $b, 2) );
			}
			elseif($a !== NULL && $b === NULL && $c !== NULL){
				return sqrt( pow((double) $c, 2) - pow((double) $a, 2) );
			}
			elseif($a !== NULL && $b !== NULL && $c === NULL){
				return sqrt( pow((double) $a, 2) + pow((double) $b, 2) );
			}
			else{ return /*error*/ FALSE; }
		} else { #apply Law of cosines: http://en.wikipedia.org/wiki/Law_of_cosines
			if($a === NULL && $b !== NULL && $c !== NULL){
				return sqrt( pow((double) $b, 2) + pow((double) $c, 2) - (2* (double) $b * (double) $c * cos(deg2rad( $degree /*opposite=alpha*/)) ) );
			}
			elseif($a !== NULL && $b === NULL && $c !== NULL){
				return sqrt( pow((double) $a, 2) + pow((double) $c, 2) - (2* (double) $a * (double) $c * cos(deg2rad( $degree /*opposite=beta*/)) ) );
			}
			elseif($a !== NULL && $b != NULL && $c === NULL){
				return sqrt( pow((double) $a, 2) + pow((double) $b, 2) - (2* (double) $a * (double) $b * cos(deg2rad( $degree /*opposite=gamma*/)) ) );
			}
			else{ return /*error*/ FALSE; }
		}
		return /*error*/ FALSE;
	}
}
?>