<?php
use Slim\Slim as Slim;
if(!session_id()) session_start();
if(!isset($_SESSION['slim.flash'])) $_SESSION['slim.flash'] = array();
function base_path(){
  return __DIR__;
}
function app_path(){
	return realpath(__DIR__.'/../app');
}
function domain(){
  return (sprintf(
    "%s://%s",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
    $_SERVER['SERVER_NAME']
  ));
}
function absolute($url){
	if(preg_match('/^(http|https):\/\//m', $url)){
		return $url;
	}
	return base_url().$url;
}
function method($method, $route, $callback){
	$app = Slim::getInstance();
	if(is_string($callback)) {
		return $app->map($route, function() use($callback){
			$callback = explode('@', $callback);
			require_once '../app/controllers/'.$callback[0].'.php';
			$controller = new $callback[0];
			$call = call_user_func_array(array($controller, $callback[1]), func_get_args());
			if($call instanceof Proto){
				echo $call->call();
			} else {
				echo $call;
			}
			return;
		})->via(strtoupper($method));
	}
	return $app->map($route, function() use($callback, $app){
		$call = call_user_func_array($callback, func_get_args());
		if($call instanceof Proto){
			echo $call->call();
		} else {
			echo $call;
		}
		return;
	})->via(strtoupper($method));
}
function strrand($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
function get($route, $callback){
	return method('get', $route, $callback);
}
function post($route, $callback){
	return method('post', $route, $callback);
}
function put($route, $callback){
	return method('put', $route, $callback);
}
function delete($route, $callback){
	return method('delete', $route, $callback);
}
function options($route, $callback){
	return method('options', $route, $callback);
}
function patch($route, $callback){
	return method('patch', $route, $callback);
}
function group($prefix, $callback){
	$app = Slim::getInstance();
	$app->group($prefix, $callback);
}
function route($name, $parameters = array()){
	$app = Slim::getInstance();
	return domain().$app->urlFor($name, $parameters);
}

function cookie(){
	return new CookieProto();
}

function session(){
	return new SessionProto;
}
function redirect($url, $status = null){
	return new RedirectProto($url, $status);
}
function filter($name, $callback = null){
	if($callback == null){
		if(is_array($name)){
			foreach ($name as $key) {
				FilterProto::call($key);
			}
		}
		return FilterProto::call($name);
	}
	new FilterProto($name, $callback);
}
function view($name, $data = array()){
	return new ViewProto($name, $data);
}

function response($body = null, $status = 200, $headers = array()){
	return new ResponseProto($body, $status, $headers);
}

function model($model){
	return Model::factory($model);
}

function pass(){
	return new PassProto();
}

function auth(){
	return new AuthProto;
}

function input(){
	return new InputProto;
}
function config($key = null){
	$config = (require_once '../app/config/app.php');
	if($key !== null){
		return $config[$key];
	}
	return $config;
}
function quote(){
	$quotes = array(
		'"Simplicity is the keynote of true elegance" - Coco Chanel',
		'"Through simplicity comes great beauty" - It\'s a mistery',
		'"Perfection is achieved, not when there is nothing left to add but when there is nothing left to remove." - Antoine de Saint - Exupéry',
		'"The secret of success is making your vocation a vacation" - Mark Twain',
		'"Apparently there is nothing that cannot happen today." - Mark twain',
		'"Imagination is more important than knowledge" - Albert Einstein'
	);
	return $quotes[array_rand($quotes, 1)];
}
if(config('use_db')){
	$mysql = config('mysql');
	ORM::configure('mysql:host='.$mysql['host'].';dbname='.$mysql['database']);
	ORM::configure('username', $mysql['username']);
	ORM::configure('password', $mysql['password']);
}



