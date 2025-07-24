<?php 
require_once 'terceros/dropbox/vendor/autoload.php';
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;

$dropboxKey ="qipt30t6dulv8pz";
$dropboxSecret ="ihiznom4l7ok5cu";
$dropboxToken="";

$app = new DropboxApp($dropboxKey,$dropboxSecret,$dropboxToken);
$dropbox = new Dropbox($app);

if(!empty($_FILES)){
    $nombre = uniqid();
    $tempfile = $_FILES['file']['tmp_name'];
    $ext = explode(".",$_FILES['file']['name']);
    $ext = end($ext);
    $nombredropbox = "/" .$nombre . "." .$ext;

   try{
        $file = $dropbox->simpleUpload( $tempfile,$nombredropbox, ['autorename' => true]);
        echo "archivo subido";
   }catch(\exception $e){
        print_r($e);
        
   }



}


?>