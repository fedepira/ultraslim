<?php
filter('auth', function(){
	if(auth()->guest()){
		return redirect('login');
	}
});
filter('guest', function(){
	if(auth()->check()){
		return redirect('/ultraslim/public');
	}
});