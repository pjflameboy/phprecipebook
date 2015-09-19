<?php
/*
	Misc functions that are used all over the place.  
	If they fit into a class then they should be moved there (Ingredient, Recipes, Units)
*/

/*
	Gets the first matching language that is set in the users browser that we support, if nothing
	matches then we just use english
*/
function getBrowserLanguage() {
	global $SMObj, $g_rb_language;
	$server = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE']: "en";
	$temp = explode(';',$server);
	$langs = explode(',',$temp[0]);
	foreach($langs as $key => $value) {
		if (preg_match("/(.*?)-.*/", $value,$matches)) {
			$langs[$key] = $value;
		}
		// Make sure they have set supported languages
		$arr = $SMObj->getSupportedLanguages();
		if (count($arr) > 0) {
			foreach ($arr as $item => $desc) {
				$value = trim($value);
				if ($item == $value) {
					return $value;
				}
			}
		}
	}
	return NULL;
}

/**
	Determines what platform the server is running on, kind of like osname, but osname is not available on
	win32 so we will rely on php to tell is what we have to work with
	@return the lower case name of the operating system
*/
function getOsName() {
	// TODO: make this more comprehensive for the platforms it will work on.
	preg_match("/(Linux|Win32|CentOS)/i", $_SERVER['SERVER_SOFTWARE'], $matches);
	$osname = (count($matches) > 1) ? strtolower($matches[1]) : "";
	if ($osname == 'linux' || $osname == 'CentOS') return 'unix';
	else if ($osname == 'win32') return 'windows';
	else return 'unix'; // default action
}

/** 
	simple way to uniformly add help links, will make translation easier later on....
*/
function getHelpLink($anchor) {
	$txt = '<SUP><a href="./docs/documentation.html#' . $anchor . '" target="help">[?]</a></SUP>';
	return $txt;
}

/**
	Find function written in php, you can't count on find
	being installed on Windows, so here it is, it is surprisingly
	fast.
*/
function find($path, &$a, $pattern, $separator) {
	$d=array(); $f=array();
	$nd=0;  $nf=0;
	$hndl=opendir($path);
	while($file=readdir($hndl)) {
		if ($file=='.' || $file=='..') continue;
		if (is_dir($path.$separator.$file))
			$d[$nd++]=$file;
		else if (preg_match($pattern,$file)) {
			// do a file type check at this point
			$f[$nf++]=$file;
		}
   }
   closedir($hndl);

   $n=count($a);
   for ($i=0;$i<count($d);$i++) {
	   find($path.$separator.$d[$i], $a, $pattern, $separator);
   }
   for ($i=0;$i<count($f);$i++) {
	   $a[$n++]= $path . $separator . $f[$i];
   }
}

/**
	Having find would not be complete without having grep, this is really
	just a wrapper to preg_grep, but it takes a array of files
*/
function grep($files,$pattern) {
	$arr = array();
	foreach($files as $k) {
		$file = file($k, "r");
		$lines = preg_grep($pattern, $file);
		foreach ($lines as $j) {
			$arr[] = $j;
		}
	}
	return $arr;
}

/**
	class to delete a value from an array and return the modified array
	from: http://www.phpbuilder.com/mail/php-developer-list/2001052/1147.php
	This was a proposed function to add to the base PHP code, but appears to have
	never been taken up, it is quite useful though.
*/
class ftk_array {
    var $data;
    
    function ftk_array($data=array()) {
        $this->data=$data;
    }

    function del($pos) {
        for($i=$pos+1;$i<count($this->data);$i++) {
            $this->data[($i-1)]=$this->data[$i];
        }
        unset($this->data[count($this->data)-1]);
    }
}

/**
	The behavior of array_unshift is less then desirable with it's reindexing of the
	array starting at zero, so this function replaces it
*/
function array_unshift_assoc(&$arr, $key, $val)
{
   $arr = array_reverse($arr, true);
   $arr[$key] = $val;
   $arr = array_reverse($arr, true);
   return count($arr);
}

/**
* Searches the array for a given object and returns the corresponding key if 
* successful or FALSE otherwise.
* @returns $id index of element in array
* CODE FROM: php.net
*/
function object_search($needle, $haystack, $strict=false) {
 if (!is_array($haystack)) return false;
 for ($i=0; $i<count($haystack); ++$i) {
   if ($strict) {
   // STRICT
   if ((get_class($needle)==get_class($haystack[$i])) && ($needle==$haystack[$i])) return $i;
   } else {
   // NO STRICT
   if ($needle==$haystack[$i]) return $i;
   }
 }
 return false;
}// function object_search

function array2json($arr) {
    //if(function_exists('json_encode')) return json_encode($arr); //Lastest versions of PHP already has this functionality.
    $parts = array();
    $is_list = false;

    //Find out if the given array is a numerical array
    $keys = array_keys($arr);
    $max_length = count($arr)-1;
    if(($keys[0] == 0) and ($keys[$max_length] == $max_length)) {//See if the first key is 0 and last key is length - 1
        $is_list = true;
        for($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position
            if($i != $keys[$i]) { //A key fails at position check.
                $is_list = false; //It is an associative array.
                break;
            }
        }
    }

    foreach($arr as $key=>$value) {
        if(is_array($value)) { //Custom handling for arrays
            if($is_list) $parts[] = array2json($value); /* :RECURSION: */
            else $parts[] = '"' . $key . '":' . array2json($value); /* :RECURSION: */
        } else {
            $str = '';
            if(!$is_list) $str = '"' . $key . '":';

            //Custom handling for multiple data types
            if(is_numeric($value)) $str .= $value; //Numbers
            elseif($value === false) $str .= 'false'; //The booleans
            elseif($value === true) $str .= 'true';
            else $str .= '"' . htmlentities($value) . '"'; //All other things
            // :TODO: Is there any more datatype we should be in the lookout for? (Object?)

            $parts[] = $str;
        }
    }
    $json = implode(',',$parts);
    
    if($is_list) return '[' . $json . ']';//Return numerical JSON
    return '{' . $json . '}';//Return associative JSON
} 
?>
