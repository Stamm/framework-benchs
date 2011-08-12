<?php
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

if(!isset($_GET['file'])){
    $_GET['file'] = 'target.csv';
}

$apps = fetch_target_list($_GET['file']);

$base_url = dirname($_SERVER['REQUEST_URI']).'/';

?>
<html>
    <head>
        
    </head>
    <body>
<?php
foreach($apps as $app){
    echo '<h1>'.$app['name'].' (v. '.$app['version'].')</h1>';
    echo '<iframe src="'.$base_url.$app['path'].'" width="300" height="50" ></iframe>';
}
?>
<body>
</html>