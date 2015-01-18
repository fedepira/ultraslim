<?php
class HomeController {
	public function welcome(){
		$quote = quote();
		return view('welcome', compact('quote'));
	}
}