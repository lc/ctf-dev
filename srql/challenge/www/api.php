<?php
	  header('Content-Type: text/plain');
	  $call = $_GET["call"];
	  if(isset($call)) {
	  	  if($call == "") {
	  	  	echo 'Random Corp ApiV1';
	  	  	return 0;
	  	  }
	  	  if($call === "news" || $call === "changelog") {
				$tmp = "http://devrandom.corp.d.ctff.fun:8080/".$call.'/';
	  	  		echo curl($tmp);
			} else if(substr( $call, 0, 4 ) === "http") {
	  	  		echo curl($call);
		  } else if(substr($call,0,4) === "file") {
				echo "blocked by waf: crime detected";
		  }
		} else {
			echo 'Random Corp ApiV1';
		}
		function curl($call) {
		  $c = curl_init($call);
		  curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		  curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		  curl_setopt($c, CURLOPT_GET, true);
		  $out = curl_exec($c);
		  curl_close($c);
		  return $out;
		}
