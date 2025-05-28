<?php

chdir(__DIR__.'\..');
set_time_limit(0);
date_default_timezone_set('UTC');
ini_set('memory_limit',	'100M');
define('WINDOWS', strtolower(substr(PHP_OS,0,3))=='win');

new wav2pkg();

class wav2pkg {

	var $php;
	var $tar;
	var $encrypt;
	var $normalize;
	
	var $password = 'r0ckrobo#23456';
	
	function __construct() {
		
		if(isset($_SERVER['REQUEST_URI'])) {
			$file = '.'.str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['REQUEST_URI']);
			if(is_file($file)) {
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="'.basename($file).'"');
				header('Content-Length: '.filesize($file));
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Expires: 0');
				readfile($file);
			} else {
				header('HTTP/1.0 404 Not Found');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Expires: 0');
			}
		} else {
			if(WINDOWS) {
				$this->php       = '.\environment\php\php.exe';
				$this->tar       = '.\environment\7zip\7za.exe';
				$this->encrypt   = '.\environment\ccrypt\ccrypt.exe -eqfK '."'{$this->password}'";
				$this->decrypt   = '.\environment\ccrypt\ccrypt.exe -dqfK '."'{$this->password}'";
				$this->normalize = '.\environment\normalize\normalize.exe -q';
				$this->tmpfile  = '.\pkgs\tmp.tmp.pkg';
			}
			$this->write("<c:yellow>Creating voicepacks from wav-files:</c>", true);
			$voicepacks=(array)glob('.\voicepacks\*',GLOB_ONLYDIR);
			foreach($voicepacks as $key=>$val) {
				$this->encode_voicepack($val);
			}

			$this->write("\n\n<c:yellow>Extracting voicepacks to wav-files:</c>", true);
			$voicepacks=(array)glob('.\pkgs\*.pkg');
			foreach($voicepacks as $key=>$val) {
				$this->decode_voicepack($val);
			}
			$this->write("\n<c:green>DONE!</c> The program will be closed in 30 seconds.", true);
			sleep(30);
		}
	}

	function encode_voicepack($voicepack) {
		$name = pathinfo($voicepack, PATHINFO_BASENAME);
		@unlink("voicepacks\\{$name}.pkg");
		$this->write("Voicepack: <c:aqua>{$name}</c>");
		$this->write("Normalizing wav files: <c:white2>...</c>");
		system("{$this->normalize} {$voicepack}\\*.wav  >NUL 2>NUL");
		$this->write("Normalizing wav files: <c:green>OK</c>", true);
		$this->write("Creating voice package: <c:white2>...</c>");
		system("{$this->tar}  -so -ttar a {$name} {$voicepack}\\*.wav | {$this->tar} -si -tgzip a {$voicepack}\\{$name}.pkg >NUL 2>NUL");
		$this->write("Creating voice package: <c:green>OK</c>", true);
		$this->write("Encrypting voice package: <c:white2>...</c>");
		system("{$this->encrypt} {$voicepack}\\{$name}.pkg >NUL 2>NUL");
		rename("{$voicepack}\\{$name}.pkg.cpt","voicepacks\\{$name}.pkg");
		$this->write("Encrypting voice package: <c:green>OK</c>\n", true);
	}

	function decode_voicepack($voicepack) {
		$name = pathinfo($voicepack, PATHINFO_FILENAME);
		system("rmdir /S /Q pkgs\\{$name} 2>NUL");
		$this->write("Voicepack: <c:aqua>{$name}</c>");
		$this->write("Decrypting voice package: <c:white2>...</c>");
		@copy($voicepack, $this->tmpfile);
		system("{$this->decrypt} {$this->tmpfile} >NUL 2>NUL");
		$this->write("Decrypting voice package: <c:green>OK</c>", true);
		$this->write("Extracting voice package: <c:white2>...</c>");
		system("{$this->tar} -so -tgzip x {$this->tmpfile} | {$this->tar} -si -ttar x -opkgs\\{$name} >NUL 2>NUL");
		$this->write("Extracting voice package: <c:green>OK</c>\n", true);
		@unlink($this->tmpfile);
	}

	function write($message='', $clearline=false) {
		static $pattern = 'black|blue2|green2|aqua2|red2|purple2|yellow2|white2|gray|blue|green|aqua|red|purple|yellow|white';
		static $colors  = array('black'=>0,'blue2'=>1,'green2'=>2,'aqua2'=>3,'red2'=>4,'purple2'=>5,'yellow2'=>6,'white2'=>7,'gray'=>8,'blue'=>9,'green'=>10,'aqua'=>11,'red'=>12,'purple'=>13,'yellow'=>14,'white'=>15);
		static $history = array(array(15,0));
		if($clearline) {
			$position = wcli_get_cursor_position();
			wcli_clear_line($position[1]);
			wcli_set_cursor_position(1, $position[1]);
		}
		$messages = preg_split('#\r?\n#', $message);
		foreach($messages as $line=>$message) {
			if(!$clearline || $line) {
				wcli_echo(PHP_EOL.' ');
			}
			$submessages = explode("\n", preg_replace(array('#<c:#i','#</c>#i'),array("\n<c:","\n</c>"),$message));
			foreach($submessages as $submessage) {
				if(preg_match("#^</c>(?'m'.*)#i",$submessage,$matches)) {
					$submessage = $matches['m'];
					if(count($history)>1) {
						array_pop($history);
					}
				} else
				if(preg_match("#^<c:(?'f'(?:$pattern)?)(?::(?'b'$pattern)|())>(?'m'.*)#i",$submessage,$matches)) {
					$submessage = $matches['m'];
					$current    = end($history);
					$history[]  = array(
						$matches['f'] ? $colors[strtolower($matches['f'])] : $current[0],
						$matches['b'] ? $colors[strtolower($matches['b'])] : $current[1],
					);
				}
				$current = end($history);
				wcli_echo($submessage, $current[0], $current[1]);
			}
		}
	}
}