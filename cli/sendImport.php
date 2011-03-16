<?php
/**
* Send import file to import service.
* 
* @author Sardar Yumatov (ja.doma@gmail.com)
*/

//default parameters
$file = null;
$key_file = 'wsmr_accepte_private.pem';
$host = 'backoffice.watstemtmijnraad.accepteproject.nl';
$port = 80;
$out_file = null;

for($i = 1; $i < sizeof($argv); $i++) {
	if($argv[$i][0] == '-') {
		switch ($argv[$i]) {
			case '-k':
			case '--key': if($i + 1 >= $argc) die("Error: option '--key' requres path to private file\n");
			              $key_file = $argv[++$i];
			              break;
			
			case '-h':
			case '--host': if($i + 1 >= $argc) die("Error: option '--host' requres valid host name\n");
			               $host = $argv[++$i];
			               break;
			               
			case '-p':
			case '--port': if($i + 1 >= $argc) die("Error: option '--post' requres port number\n");
			               $port = $argv[++$i];
			               break;
			               
			default: echo "Ingoring unknown argument: {$argv[$i]}\n";
		}
	} else {
		if($file == null) $file = $argv[$i];
		else $out_file = $argv[$i];
	}
}

//check arguments
if($file == null) {
	echo "Usage: php sendImport.php [options] <IN FILE> [<OUT FILE>]\n";
	echo "  Options, [defaults]:";
	echo "    -k --key <file>   path to private key file (.pem) [wsmr_accepte_private.pem]\n";
	echo "    -h --host <host>  where to connect [backoffice.watstemtmijnraad.accepteproject.nl]\n";
	echo "    -p --port <num>   port number [80]\n\n";
	exit(1);
}

if($out_file == null) {
	$out_file = dirname($file).DIRECTORY_SEPARATOR.basename($file, '.xml')."_response.xml";
}

if(!is_file($key_file) || !is_readable($key_file) || !($key_contents = file_get_contents($key_file)) || !($private = openssl_pkey_get_private($key_contents))) {
	echo "Error: can't read key file: {$key_file}\n";
	exit(1);
}

if(!is_file($file) || !is_readable($file) || !($contents = file_get_contents($file))) {
	echo "Error: can't read data file: {$file}\n";
	exit(1);
}


//sign the message
$sig = null;
if(!openssl_sign($contents, $sig, $private)) {
	echo "Error: failed to sign the file '{$file}' with key {$key_file}\n";
	exit(1);
}

//unpack binary to hex string
$text_sig = reset(unpack('H*', $sig));
echo "File signed: {$file}\n";


//send POST query
echo "Sending request to: {$host}:{$port}\n";
$fp = fsockopen($host, $port);
if(!$fp) {
	echo("Error: can't connect to {$host}:{$port}\n");
	exit(1);
}

//send the request headers:
fwrite($fp, "POST /import?user=accepte&sig={$text_sig} HTTP/1.1\r\n");
fwrite($fp, "Host: {$host}\r\n");
fwrite($fp, "Content-type: text/xml\r\n");
fwrite($fp, "Content-length: ". strlen($contents) ."\r\n");
fwrite($fp, "Connection: close\r\n\r\n");
fwrite($fp, $contents);

//read response
$response = stream_get_contents($fp);

//done
fclose($fp);

//write response
echo "Writing response to: {$out_file}\n";
file_put_contents($out_file, $response);

exit(0);
?>