<?php
class BaseModel extends Model
{
	public function as_json(){
		$array = $this->as_array();
		return json_encode($array);
	}
}
class AuthProto
{
	
	function attempt($data, $remember = false)
	{
		$pass = $data['password'];
		unset($data['password']);

		$users = model('User');
		foreach ($data as $key => $value) {
			$users = $users->where($key, $value);
		}
		$user = $users->find_one();
		if(!$user){
			return false;
		}
		if(pass()->check($pass, $user->password) ){
			$user->remember_token = strrand(40);
			$user->save();
			cookie()->set('ultraslim', $user->remember_token, '2 days');
			return true;
		}
		return false;
	}
	public function check(){
		$token = cookie()->get('ultraslim');
		$user = model('User')->where('remember_token', $token)->find_one();
		return !!$user;
	}
	public function guest(){
		$token = cookie()->get('ultraslim');
		$user = model('User')->where('remember_token', $token)->find_one();
		return !$user;
	}
	public function user(){
		$token = cookie()->get('ultraslim');
		$user = model('User')->where('remember_token', $token)->find_one();
		return $user;
	}
	public function logout(){
		cookie()->delete('ultraslim');
		if(auth()->check()){
			$user = auth()->user();
			$user->remember_token = null;
			$user->save();
		}
		return true;
	}
}
class CookieProto extends Proto {
	public function delete($name ,$path = null, $domain = null, $secure = null, $httponly = null){
		global $app;
		$app->deleteCookie($name, $path, $domain, $secure, $httponly);
	}
	public function get($name){
		global $app;
		return $app->getCookie($name);
	}
	public function all(){
		global $app;
		return $app->request->cookies;
	}
	public function has($key){
		global $app;
		if(is_array($key)){
			$has = true;
			foreach ($key as $a) {
				if ($app->getCookie($a) == null ? true : false){
					$has = false;
					break;
				}
				return $has;
			}
		} elseif($key !== null){
			return $app->getCookie($name) == null ? false : true;
		}
		return count($app->request->cookies) ? true: false;
	}
	public static function set($name = null, $value = null, $expiresAt = '2 days', $path = '/', $domain = null, $secure = false, $httponly = false){
		global $app;
		$app->setCookie(
		    $name,
		    $value,
		    $expiresAt,
		    $path,
		    $domain,
		    $secure,
		    $httponly
		);
		return true;
	}
}
class FilterProto{
	private static $filters = array();
	public function __construct($name, $callback){
		self::$filters[$name] = $callback;
	}
	public static function call($name){
		$filter = self::$filters[$name];
		$call = $filter();
		if($call instanceof Proto){
			$call->call();
		}
	}
}
class InputProto extends Proto
{	
	private $input = array();
	public function __construct(){
		global $app;
		$this->input = array_merge($app->request->get(), $app->request->post(), $app->request->put(), $app->request->patch(), $app->request->delete());
	}
	public function has($key = null){
		if(is_array($key)){
			$has = true;
			foreach ($key as $a) {
				if (!array_key_exists($a, $this->input)){
					$has = false;
					break;
				}
				return $has;
			}
		} elseif($key !== null){
			return array_key_exists($key, $this->input);
		}
		return count($this->input) ? true: false;
	}
	public function get($key){

		if(array_key_exists($key, $this->input)) return $this->input[$key];
		return null;
	}
	public function all(){
		return $this->input;
	}
}
class PassProto
{
	public function check($string, $hash){
		$phpassHash = new \Phpass\Hash;
		$password = $string;
		return $phpassHash->checkPassword($password, $hash);
	}
	public function hash($string){
		$phpassHash = new \Phpass\Hash;
		$hash = $phpassHash->hashPassword($string);
		return $hash;
	}
}
class Proto {
	public function call(){
		//Called Proto
		return;
	}
}
class RedirectProto extends Proto
{
	private $url;
	private $status;
	function __construct($url, $status)
	{
		$this->url = $url;
		$this->status = $status;
	}
	public function with($key, $value){
		session()->set($key, $value);
		return $this;
	}
	public function call(){
		global $app;
		$app->redirect($this->url, $this->status);
		return;
	}
}
class ResponseProto extends Proto
{
	public $body;
	public $status;
	public $headers;
	
	public function __construct($body, $status, $headers)
	{
		$this->body = $body;
		$this->status = $status;
		$this->headers = $headers;
	}
	public function header($header, $content = null){
		if($content === null){
			return $this->headers[$header];
		}
		$this->headers[$header] = $content;
		return $this;
	}
	public function headers(){
		return $this->headers;
	}
	public function status($code){
		$this->status = $code;
		return $this;
	}
	public function body($body = null){
		if($body = null){
			return $this->body;
		}
		$this->body = $body;
		return $this;
	}
	public function call(){
		global $app;
		$resp = $app->response;
		$resp->setStatus($this->status);
		foreach ($this->headers as $header => $content) {
			$resp->headers->set($header, $content);
		}
		$resp->setBody($this->body);
		return;
	}
}
class SessionProto extends Proto
{
	public function set($key, $value){
		global $app;
		$app->flash($key, $value);
		return $this;
	}
	public function get($key){

		return array_key_exists($key, $_SESSION['slim.flash']) ? $_SESSION['slim.flash'][$key] :null;
	}
	public function has($key){
		if(is_array($key)){
			$has = true;
			foreach ($key as $a) {
				if (!array_key_exists($a, $_SESSION['slim.flash'])){
					$has = false;
					break;
				}
				return $has;
			}
		}
		return array_key_exists($key, $_SESSION['slim.flash']);
	}
	public function all(){
		return $_SESSION['slim.flash'];
	}
}
class ViewProto extends Proto {
	private $name;
	private $data = array();
	public function __construct($name, $data){
		$this->name = $name;
		$this->data = $data;
	}
	public function call(){
		Mustache_Autoloader::register();
		$mustache = new Mustache_Engine(array(
		   'loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/../app/views') 
		));
		$template = $mustache->loadTemplate($this->name);
		return $template->render($this->data);
	}
	public function with($name, $data){
		$this->data[$name] = $data;
		return $this;
	}
	public function withMessages(){
		$this->data['session'] = session()->all();
		return $this;
	}
}