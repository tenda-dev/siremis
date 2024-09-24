<?PHP
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin
 * @copyright Copyright &copy; 2005-2009, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id$
 */

/**
 * Resource class
 *
 * @package   openbiz.bin
 * @author    Rocky Swen <rocky@phpopenbiz.org>
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class Resource
{
    private static $_imageUrl;
    private static $_cssUrl;
    private static $_jsUrl;

    /**
     * Load message from file
     *
     * @param string $messageFile
     * @return mixed
     */
    public static function loadMessage($messageFile, $packageName="")
    {
      	if(isset($messageFile) && $messageFile != "")
        {
            if(is_file(MESSAGE_PATH."/".$messageFile)) 
            {
                return parse_ini_file(MESSAGE_PATH."/".$messageFile);
            }
            else
            {
                if(isset($packageName) && $packageName != "")
                {
	                $dirs = explode('.', $packageName);
		            $moduleName = $dirs[0];
		            //for($i=0;$i<count($dirs)-1;$i++){
		            //	$moduleName.="/".$dirs[$i];
		            //}
		            $msgFile = MODULE_PATH . "/$moduleName/message/" . $messageFile;
	                if(is_file($msgFile)) 
		            {
		                return parse_ini_file($msgFile);
		            }
		            else
		            {
		            	$errmsg = self::getMessage("SYS_ERROR_INVALID_MSGFILE",array($msgFile));
		            	trigger_error($errmsg, E_USER_ERROR);
		            }
                }
                else
                {
	            	$errmsg = self::getMessage("SYS_ERROR_INVALID_MSGFILE",array(MESSAGE_PATH."/".$messageFile));
		            trigger_error($errmsg, E_USER_ERROR);
                }
            }
        }
        return null;
    }

    /**
     * Get message from CONSTANT, translate and format it
     * @param string $msgId ID if constant
     * @param array $params parameter for format (use vsprintf)
     * @return string
     */
    public static function getMessage($msgId, $params=array())
    {
        $message = constant($msgId);
        if (isset($message))
        {
            $message=I18n::getInstance()->translate($message);
            $result = vsprintf($message,$params);
        }
        return $result;
    }

    /**
     * Get image URL
     * @return string
     */
    public static function getImageUrl()
    {
        if (isset(self::$_imageUrl))
            return self::$_imageUrl;
        $useTheme = !defined('USE_THEME') ? 0 : USE_THEME;
        $themeUrl = !defined('THEME_URL') ? "../themes" : THEME_URL;
        $themeName = !defined('THEME_NAME') ? "../default" : THEME_NAME;
        if ($useTheme)
            self::$_imageUrl = "$themeUrl/$themeName/images";
        else
            self::$_imageUrl = "../images";

        return self::$_imageUrl;
    }

    /**
     * Get CSS URL
     * @return string
     */
    public static function getCssUrl()
    {
        if (isset(self::$_cssUrl))
            return self::$_cssUrl;
        $useTheme = !defined('USE_THEME') ? 0 : USE_THEME;
        $themeUrl = !defined('THEME_URL') ? APP_URL."/themes" : THEME_URL;
        $themeName = !defined('THEME_NAME') ? APP_URL."/default" : THEME_NAME;
        if ($useTheme)
            self::$_cssUrl = "$themeUrl/$themeName/css";
        else
            self::$_cssUrl = APP_URL."/css";
        return self::$_cssUrl;
    }

    /**
     * Get JavaScript(JS) URL
     * @return string
     */
    public static function getJsUrl()
    {
        if (isset(self::$_jsUrl))
            return self::$_jsUrl;
        self::$_jsUrl = !defined('JS_URL') ? APP_URL."/js" : JS_URL;
        return self::$_jsUrl;
    }

    /**
     * Get smarty template
     * @return Smarty smarty object
     */
    public static function getSmartyTemplate()
    {
        include_once(SMARTY_DIR."Smarty.class.php");
        $smarty = new Smarty;

        $useTheme = !defined('USE_THEME') ? 0 : USE_THEME;
        if($useTheme)
        {
            $theme = isset($_GET['theme']) ? $_GET['theme'] : THEME_NAME;
            $themePath = $theme;    // BizSystem::configuration()->GetThemePath($theme);
            if(is_dir(THEME_PATH."/".$themePath."/template"))
            {
            	$templateRoot = THEME_PATH."/".$themePath."/template";
            }
            else
            {
            	$templateRoot = THEME_PATH."/".$themePath."/templates";
            }            
            $smarty->template_dir = $templateRoot;
            $smarty->compile_dir = $templateRoot."/cpl";
            $smarty->config_dir = $templateRoot."/cfg";
            // load the config file which has the images and css url defined
            $smarty->config_load('tpl.conf');
        }
        else
        {
            if (defined('SMARTY_TPL_PATH'))
                $smarty->template_dir = SMARTY_TPL_PATH;
            if (defined('SMARTY_CPL_PATH'))
                $smarty->compile_dir = SMARTY_CPL_PATH;
            if (defined('SMARTY_CFG_PATH'))
                $smarty->config_dir = SMARTY_CFG_PATH;
        }
        // load the config file which has the images and css url defined
        $smarty->assign('app_url',APP_URL);
		$smarty->assign('app_index',APP_INDEX);
        $smarty->assign('js_url',JS_URL);
        $smarty->assign('css_url',THEME_URL."/".THEME_NAME."/css");
        $smarty->assign('theme_js_url',THEME_URL."/".THEME_NAME."/js");
        $smarty->assign('theme_url',THEME_URL."/".THEME_NAME);
        $smarty->assign('image_url',THEME_URL."/".THEME_NAME."/images");
        $smarty->assign('lang', strtolower(I18n::getInstance()->getCurrentLanguage()));

        return $smarty;
    }

    /**
     * Get Zend Template
     * @return Zend_View zend view template object
     */
    public static function getZendTemplate()
    {
        // now assign the book data to a Zend_View instance
        //Zend_Loader::loadClass('Zend_View');
        require_once 'Zend/View.php';
        $view = new Zend_View();
        if (defined('SMARTY_TPL_PATH'))
            $view->setScriptPath(SMARTY_TPL_PATH);

        return $view;
    }

    /**
     * Get Xml file with path
     *
     * Search the object metedata file as objname+.xml in metedata directories
     * name convension: demo.BOEvent points to metadata/demo/BOEvent.xml
     * new in 2.2.3, demo.BOEvent can point to modules/demo/BOEvent.xml
     *
     * @param string $xmlObj xml object
     * @return string xml config file path
     **/
    public static function getXmlFileWithPath($xmlObj)
    {
        $xmlFile = $xmlObj;
        if (strpos($xmlObj, ".xml")>0)  // remove .xml suffix if any
            $xmlFile = substr($xmlObj, 0, strlen($xmlObj)-4);

        // replace "." with "/"
        $xmlFile = str_replace (".", "/", $xmlFile);
        $xmlFile .= ".xml";

        //if (file_exists($xmlfile))
        //   return $xmlfile;

        $xmlFile = "/" . $xmlFile;

        // search in modules directory first
        if(defined('TARGET_APP_HOME'))
        {
        	if (file_exists(TARGET_APP_HOME . $xmlFile))
                return TARGET_APP_HOME . $xmlFile;
        }
        if (file_exists(MODULE_PATH . $xmlFile))
            return MODULE_PATH . $xmlFile;
        if (file_exists(APP_HOME . $xmlFile))
            return APP_HOME . $xmlFile;
        if (file_exists(OPENBIZ_META . $xmlFile))
            return OPENBIZ_META . $xmlFile;

        return null;
    }

    /**
     * Get openbiz template file path by searching modules/package, /templates
     *
     * @param string $className
     * @return string php library file path
     **/
    public static function getTplFileWithPath($templateFile, $packageName)
    {
        //for not changing a lot things, the best injection point is added theme support here.
        $theme = isset($_GET['theme']) ? $_GET['theme'] : THEME_NAME;
        $themePath = $theme;    // BizSystem::configuration()->GetThemePath($theme);
        if($themePath)
            $templateRoot = THEME_PATH."/".$themePath."/template";
        else
            $templateRoot = SMARTY_TPL_PATH;

        $names = explode(".",$packageName);
        if (count($names)>0)
        	$moduleName = $names[0];
        $packagePath = str_replace('.', '/', $packageName);
        $searchTpls = array(MODULE_PATH."/$packagePath/template/$templateFile",
        					dirname(MODULE_PATH."/$packagePath")."/template/$templateFile",
        					MODULE_PATH."/$moduleName/template/$templateFile",
        					//MODULE_PATH."/common/template/$templateFile",
        					$templateRoot."/$templateFile"
        					);
       	foreach ($searchTpls as $tplFile)
       	{
       		if (file_exists($tplFile)){
       			return $tplFile;
       		}
       	}
       	$errmsg = BizSystem::getMessage("UNABLE_TO_LOCATE_TEMPLATE_FILE",array($templateFile));
      	trigger_error($errmsg, E_USER_ERROR);
        return null;
    }

    /**
     * Get openbiz library php file path by searching modules/package, /bin/package and /bin
     *
     * @param string $className
     * @return string php library file path
     **/
    public static function getLibFileWithPath($className, $packageName="")
    {
        if (!$className) return;
        // search it in cache first
        $cacheKey = $className."_path";
        if (extension_loaded('0') && ($filePath = apc_fetch($cacheKey)) != null)
            return $filePath;

        if (strpos($className, ".") > 0)
            $className = str_replace(".", "/", $className);
        
        $filePath = null;
        $classFile = $className . ".php";
        $classFile_0 = $className . ".php";
        // convert package name to path, add it to classfile
        $bFound = false;
        if ($packageName)
        {
            $path = str_replace(".", "/", $packageName);
            // search in apphome/modules directory first, search in apphome/bin directory then
            $classfiles[0] = MODULE_PATH . "/" . $path . "/" . $classFile;
            $classfiles[1] = APP_HOME . "/bin/" . $path . "/" . $classFile;
            $classfiles[2] = APP_HOME . "/bin/" . $classFile;
            foreach ($classfiles as $classFile)
            {
                if (file_exists($classFile))
                {
                    $filePath = $classFile;
                    $bFound = true;
                    break;
                }
            }
        }

        if (!$bFound)
            $filePath = self::_getCoreLibFilePath($className);
        // cache it to save file search
        if ($filePath && extension_loaded('apc'))
            apc_store($cacheKey, $filePath);
        return $filePath;
    }

    /**
     * Get core path of class
     *
     * @param string $className class name
     * @return string full file name of class
     */
    private static function _getCoreLibFilePath($className)
    {
        $classFile = $className.'.php';

        // TODO: search the file under bin/, bin/data, bin/ui. bin/service, bin/easy, bin/easy/element.
        $corePaths = array('', 'data/', 'easy/', 'easy/element/', 'ui/', 'service/');
        foreach($corePaths as $path)
        {
            $_classFile = OPENBIZ_BIN . $path . $classFile;
            if (file_exists($_classFile))
                return $_classFile;
        }
        return null;
    }

    /**
     * Get Xml Array.
     * If xml file has been compiled (has .cmp), load the cmp file as array;
     * otherwise, compile the .xml to .cmp first new 2.2.3, .cmp files
     * will be created in app/cache/metadata_cmp directory. replace '/' with '_'
     * for example, /module/demo/BOEvent.xml has cmp file as _module_demo_BOEvent.xml
     *
     * @param string $xmlFile
     * @return array
     **/
    public static function &getXmlArray($xmlFile)
    {
        $objXmlFileName = $xmlFile;
        //$objCmpFileName = dirname($objXmlFileName) . "/__cmp/" . basename($objXmlFileName, "xml") . ".cmp";
        $_crc32 = sprintf('%08X', crc32(dirname($objXmlFileName)));
        $objCmpFileName = CACHE_METADATA_PATH . '/' . $_crc32 . '_'
            . basename($objXmlFileName, "xml") . "cmp";

        $xmlArr = null;
        $cacheKey = $objXmlFileName;
        $findInCache = false;
        if( file_exists($objCmpFileName)
            && (filemtime($objCmpFileName) > filemtime($objXmlFileName)) )
        {
            // search in cache first
            if (!$xmlArr && extension_loaded('apc'))
            {
                if (($xmlArr = apc_fetch($cacheKey)) != null)
                {
                    $findInCache = true;
                }
            }
            if (!$xmlArr)
            {
                $content_array = file($objCmpFileName);
                $xmlArr = unserialize(implode("", $content_array));
            }
        }
        else
        {
            include_once(OPENBIZ_BIN . "util/xmltoarray.php");
            $parser = new XMLParserX($objXmlFileName, 'file', 1);
            $xmlArr = $parser->getTree();
	    // print_r($xmlArr);
            // simple validate the xml array
			error_log("----- xmlArr (" . __FILE__ . ":" . __LINE__ . " {| " . print_r($xmlArr, true) . " |}");
            $root_keys = array_keys($xmlArr);
			error_log("----- root_keys (" . __FILE__ . ":" . __LINE__ . " {| " . print_r($root_keys, true) . " |}");
            if (!$root_keys)
	    {
            	$xmlArrStr = serialize($xmlArr);
                trigger_error("Metadata file parsing error for file $objXmlFileName {| $xmlArrStr |}. Please double check your metadata xml file again.", E_USER_ERROR);
            }
            $root_key = $root_keys[0];
            if (!$root_key || $root_key == "")
            {
            	$xmlArrStr = serialize($xmlArr);
                trigger_error("No root node 0 for file $objXmlFileName ($root_key) {| $xmlArrStr |}. Please double check your metadata xml file again.", E_USER_ERROR);
            }
            $xmlArrStr = serialize($xmlArr);
            if (!file_exists(dirname($objCmpFileName)))
                mkdir(dirname($objCmpFileName));
            $cmp_file = fopen($objCmpFileName, 'w') or die("can't open cmp file to write");
            fwrite($cmp_file, $xmlArrStr) or die("can't write to the cmp file");
            fclose($cmp_file);
        }
        // save to cache to avoid file processing overhead
        if (!$findInCache && extension_loaded('apc'))
        {
            apc_store($cacheKey, $xmlArr);
        }
        return $xmlArr;
    }
}
