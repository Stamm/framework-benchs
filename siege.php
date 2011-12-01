#!/usr/bin/env php
<?php
ini_set('error_reporting', E_ALL|E_STRICT);
ini_set('display_errors', true);

// reads a csv file of targets
function fetch_target_list($file)
{

    $files = file($file);
    
    $list = array();
    
    foreach($files as $file){
        if($file[0] == '-'){
            continue;
        }
        $f = explode('/', $file);
        
        $list[] = array(
            'name' => $f[0],
            'version' => $f[1],
            'path' => $file
        );
    }

    return $list;
}

// write out the siegerc file, mostly so we can maintain a log location
function write_siege_file($vars = array())
{
    // the base config vars
    $base = array (
        'verbose'           => 'false',
        // csv              => true,
        // fullurl          => true,
        // display-id       => '',
        'show-logfile'      => 'false',
        'logging'           => 'true',
        // 'logfile'           => '',
        'protocol'          => 'HTTP/1.0',
        'chunked'           => 'true',
        'connection'        => 'close',
        'concurrent'        => '10',
        'time'              => '60s',
        // reps             => '',
        // file             => '',
        // url              => '',
        // 'delay'             => '1',
        // timeout          => '',
        // expire-session   => '',
        // failures         => '',
        // 'internet'          => 'false',
        'benchmark'         => 'true',
        // user-agent       => '',
        // 'accept-encoding'   => 'gzip',
        'spinner'           => 'false',
        // login            => '',
        // username         => '',
        // password         => '',
        // ssl-cert         => '',
        // ssl-key          => '',
        // ssl-timeout      => '',
        // ssl-ciphers      => '',
        // login-url        => '',
        // proxy-host       => '',
        // proxy-port       => '',
        // proxy-login      => '',
        // follow-location  => '',
        // zero-data-ok     => '',
    );
    
    // make sure we have base vars for everything
    $vars = array_merge($base, $vars);
    
    // build the text for the file
    $text = '';
    foreach ($vars as $key => $val) {
        $text .= "$key = $val\n";
    }
    
    // write the siegerc file
    file_put_contents("/root/.siegerc", $text);
}

// store logs broken down by time
$time = date("Y-m-d\TH:i:s");
passthru("mkdir -p ./log/$time");

// run each benchmark target
$list = fetch_target_list($_SERVER['argv'][1]);
foreach ($list as $framework) {
    
    $name = $framework['name'].'-'.$framework['version'];
    
    // write the siegerc file
    write_siege_file(array(
        'logfile' => './log/'.$time.'/'.$name.'.log',
    ));
    
    // restart the server for a fresh environment
    passthru("/etc/init.d/nginx restart");
    passthru("/etc/init.d/php5-fpm restart");
    
    // what href are we targeting?
    $href = "http://localhost/".$framework['path'];
    
    // prime the cache
    echo $name.": prime the cache\n";
    passthru("curl $href");
    echo "\n";
    
    // bench runs
    for ($i = 1; $i <= 5; $i++) {
        echo $name.": pass $i\n";
        passthru("siege $href");
        echo "\n";
    }
}

// do reporting
echo "Logs saved at ./log/$time.\n\n";
passthru("php ./report.php ./log/$time");
exit(0);
