<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {html_image} function plugin
 *
 * Type:     function<br>
 * Name:     html_image<br>
 * Date:     Feb 24, 2003<br>
 * Purpose:  format HTML tags for the image<br>
 * Input:<br>
 *         - file = file (and path) of image (required)
 *         - height = image height (optional, default actual height)
 *         - width = image width (optional, default actual width)
 *         - maxheight, maxwidth = maximal sizes for height and width
 *         - basedir = base directory for absolute paths, default
 *                     is environment variable DOCUMENT_ROOT
 *         - path_prefix = prefix for path output (optional, default empty)
 *
 * Examples: {html_image file="/images/masthead.gif"}
 * Output:   <img src="/images/masthead.gif" width=400 height=23>
 * @link http://smarty.php.net/manual/en/language.function.html.image.php {html_image}
 *      (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @author credits to Duda <duda@big.hu> - wrote first image function
 *           in repository, helped with lots of functionality
 * @version  1.0
 * @param array
 * @param Smarty
 * @return string
 * @uses smarty_function_escape_special_chars()
 */
function smarty_function_html_image($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('shared','escape_special_chars');
    
    $alt = '';
    $file = '';
    $height = '';
    $width = '';
    $maxheight = '';
    $maxwidth = '';
    $extra = '';
    $prefix = '';
    $suffix = '';
    $path_prefix = '';
    $server_vars = ($smarty->request_use_auto_globals) ? $_SERVER : $GLOBALS['HTTP_SERVER_VARS'];
    $basedir = isset($server_vars['DOCUMENT_ROOT']) ? $server_vars['DOCUMENT_ROOT'] : '';
    foreach($params as $_key => $_val) {
        switch($_key) {
            case 'file':
            case 'height':
            case 'width':
            case 'maxheight':
            case 'maxwidth':
            case 'dpi':
            case 'path_prefix':
            case 'basedir':
                $$_key = $_val;
                break;

            case 'alt':
                if(!is_array($_val)) {
                    $$_key = smarty_function_escape_special_chars($_val);
                } else {
                    $smarty->trigger_error("html_image: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;

            case 'link':
            case 'href':
                $prefix = '<a href="' . $_val . '">';
                $suffix = '</a>';
                break;

            default:
                if(!is_array($_val)) {
                    $extra .= ' '.$_key.'="'.smarty_function_escape_special_chars($_val).'"';
                } else {
                    $smarty->trigger_error("html_image: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }

    if (empty($file)) {
        $smarty->trigger_error("html_image: missing 'file' parameter", E_USER_NOTICE);
        return;
    }

    if (substr($file,0,1) == '/') {
        $_image_path = $basedir . $file;
    } else { //customized for watstemtmijnraad (yeah, ugly...)
        $dir = Dispatcher::inst()->activeSite['publicdir'];
        $_image_path = $basedir . "/images/{$dir}/{$file}";
        $file = "/images/{$file}";
    }
    
    if(!isset($params['width']) || !isset($params['height'])) {
        if(!$_image_data = @getimagesize($_image_path)) {
            if(!file_exists($_image_path)) {
                $smarty->trigger_error("html_image: unable to find '$_image_path'", E_USER_NOTICE);
                return;
            } else if(!is_readable($_image_path)) {
                $smarty->trigger_error("html_image: unable to read '$_image_path'", E_USER_NOTICE);
                return;
            } else {
                $smarty->trigger_error("html_image: '$_image_path' is not a valid image file", E_USER_NOTICE);
                return;
            }
        }
        if ($smarty->security &&
            ($_params = array('resource_type' => 'file', 'resource_name' => $_image_path)) &&
            (require_once(SMARTY_CORE_DIR . 'core.is_secure.php')) &&
            (!smarty_core_is_secure($_params, $smarty)) ) {
            $smarty->trigger_error("html_image: (secure) '$_image_path' not in secure directory", E_USER_NOTICE);
        }        
        
        if(!isset($params['width'])) {
            $width = $_image_data[0];
        }
        if(!isset($params['height'])) {
            $height = $_image_data[1];
        }
    }
    
    $hg = intval($height);
    $mhg = intval($maxheight);
    $wd = intval($width);
    $mwd = intval($maxwidth);
    $scale_height = ($maxheight != '' && $height != '' && $mhg > 0 && $hg > $mhg)? ($mhg / $hg): 1;
    $scale_width = ($maxwidth != '' && $width != '' && $mwd > 0 && $wd > $mwd)? ($mwd / $wd): 1;
    $min_scale = min($scale_height, $scale_width);
    
    if($height != '') $height = round($hg * $min_scale);
    if($width != '') $width = round($wd * $min_scale);

    if(isset($params['dpi'])) {
        if(strstr($server_vars['HTTP_USER_AGENT'], 'Mac')) {
            $dpi_default = 72;
        } else {
            $dpi_default = 96;
        }
        $_resize = $dpi_default/$params['dpi'];
        $width = round($width * $_resize);
        $height = round($height * $_resize);
    }

    return $prefix . '<img src="'.$path_prefix.$file.'" alt="'.$alt.'" width="'.$width.'" height="'.$height.'"'.$extra.' />' . $suffix;
}

/* vim: set expandtab: */

?>