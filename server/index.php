<?php
error_reporting (E_ALL);

if (version_compare(phpversion(), '5.1.0', '<') == true) { die ('PHP5.1 Only'); }

//getting the site path
$site_path = realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR;
//include autoloader
include 'core/system/Autoloader.php';
//include config
include 'core/system/config/config.php';

//init autoload paths
spl_autoload_register(array('Autoloader' , 'load'));
Autoloader::registerPath('core/system');
Autoloader::registerPath('core/models');
Autoloader::registerPath('core/models/system');
Autoloader::registerPath('core/controllers');
Autoloader::registerPath('core/controllers/system');
Autoloader::registerPath('core/system/libraries');
Autoloader::registerPath('core/system/libraries/vk');

set_error_handler(array('Error', 'exceptionHandler'));

$GB = Globals::init();

//set site path
$GB->set(Globals::SITE_PATH_PARAM, $site_path);

//init GB with config
if(isset($config))
{
    foreach($config as $key => $val)
        $GB->set($key, $val);
}

//uri parsing
$uri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

//костыль по обрезанию index.php
if(strpos($uri, 'index.php')) {
    $uri = substr($uri, strlen($scriptName) + 1);
} else {
    $uri = '';
}

//ensures fixes
if (strncmp($uri, '?/', 2) === 0)
{
    $uri = substr($uri, 2);
}
$parts = preg_split('#\?#i', $uri, 2);
$uri = $parts[0];
if (isset($parts[1])) {
    $_SERVER['QUERY_STRING'] = $parts[1];
    parse_str($_SERVER['QUERY_STRING'], $_GET);
} else {
    $_SERVER['QUERY_STRING'] = '';
    $_GET = array();
}
$uri = parse_url($uri, PHP_URL_PATH);

$GB->set('uri', $uri);

//init GB with database connections settings
if(isset($db))
{
    $GB->set('db_config', $db);
    try
    {
        $GB->setDbConnection();
    } catch (Exception $e) {
        if($GB->getParam(Globals::ERROR_HANDLING_PARAM, true))
            $GB->getError()->htmlError($e);
        else
            $GB->getHttp()->notFound();
    }
}
    
//routing
$router = new Router();
try {
    //try to load controller with _params
    $router->pathParsing($uri);
} catch (Exception $e) {
    if($GB->getParam(Globals::ERROR_HANDLING_PARAM, true))
        $GB->getError()->htmlError($e);
    else
        $GB->getHttp()->notFound();
}





