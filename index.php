<?php 
if (isset($_FILES['MyFile']) AND $_FILES['MyFile']['error']
== 0)
{
   if ($_FILES['MyFile']['size'] <= 1000000)
   {
      $infosfichier = pathinfo($_FILES['MyFile']['name']);
      $extension_upload = $infosfichier['extension'];
      $extensions_autorisees = array('jpg', 'jpeg', 'gif','png');
      if (in_array($extension_upload, $extensions_autorisees))
      {
         move_uploaded_file($_FILES['MyFile']['tmp_name'], 'uploads/' . basename($_FILES['MyFile']['name']));

         $directory_name = rand(1, 99999);

         $full_path_directory_name = 'uploads/'.$directory_name.'/';

         mkdir('uploads/'.$directory_name, 0700);

         $zip = new ZipArchive();
         $zip_filename = 'uploads/'.$directory_name.'.zip';
         if ($zip->open($zip_filename, ZipArchive::CREATE)!==TRUE) {
            exit("Impossible d'ouvrir le fichier <$zip_filename>\n");
         }
         $all_sizes = array ('16', '24', '32', '48', '64', '57', '72', '96', '120', '128', '144', '152', '195', '228');
         $all_type = array ('ico', 'png');

         for ($count = 0; $count < 6; $count++) {
            $tmp_size = $all_sizes[$count];
            resize_and_save($tmp_size, ico, $full_path_directory_name);
            if ($tmp_size == 32) {
               $zip->addFile($thisdir.''.$full_path_directory_name.'favicon.ico');
            }
            else {
               $zip->addFile($thisdir.''.$full_path_directory_name.'favicon-'.$tmp_size.'.ico');
            }
         }

         for ($count = 2; $count < 14; $count++) {
            $tmp_size = $all_sizes[$count];
            resize_and_save($tmp_size, png, $full_path_directory_name);
            $zip->addFile($thisdir.''.$full_path_directory_name.'favicon-'.$tmp_size.'.png');
         }
      
         $zip->addFile($thisdir.'favicon.html');         
         $zip->close();
         $get_zip = $_GET[''.$directory_name.'.zip'];
         if (file_exists('uploads/'.$directory_name.'.zip') || is_readable('uploads/'.$directory_name.'.zip')) {
            if (ini_get('zlib.output_compression')) {
               ini_set('zlib.output_compression', 'Off');
            }
            header('Cache-Control: no-cache, must-revalidate');
            header('Cache-Control: post-check=0,pre-check=0');
            header('Cache-Control: max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Content-Type: application/force-download');
            header('Content-Disposition: attachment; filename="favicon.zip"');
            readfile('uploads/'.$directory_name.'.zip');
            rrmdir('uploads/'.$directory_name);
            unlink('uploads/'.$directory_name.'.zip');
            unlink('uploads/' . basename($_FILES['MyFile']['name']));
            exit;
         }
         else { 
            header("HTTP/1.1 404 Not Found");
         }
      }
   }
}
else {
   ?><!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8" />
      <title>Favicon Creator</title>
      <link rel="stylesheet" href="style.css" />
   </head>

   <body>
      <form action="index.php" method="post" enctype="multipart/form-data">
         <p>
            Formulaire d'envoi de fichier :<br />
            <input type="file" name="MyFile" /><br />
            <input type="submit" value="Send the file" />
         </p>
      </form>
   </body>
   <?php
}

function resize_and_save($size, $new_type, $full_path_directory_name){
   $image = new SimpleImage();
   $image->load('uploads/' . basename($_FILES['MyFile']['name']));
   $image->resize($size,$size); 
   if ($size == 32 || $new_type == ico) {
      $image->save(''.$full_path_directory_name.'favicon.'.$new_type.'');
   }
   $image->save(''.$full_path_directory_name.'favicon-'.$size.'.'.$new_type.'');
}    
   
function rrmdir($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
       }
     }
     reset($objects);
     rmdir($dir);
   }
 }  

class SimpleImage {
 
   var $image;
   var $image_type;
 
   function load($filename) {
 
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
 
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
 
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
 
         $this->image = imagecreatefrompng($filename);
      }
   }
   function save($filename, $permissions=null) {
         imagepng($this->image,$filename);
      if( $permissions != null) {
 
         chmod($filename,$permissions);
      }
   }
   function getWidth() {
 
      return imagesx($this->image);
   }
   function getHeight() {
 
      return imagesy($this->image);
   }
   function resize($width,$height) {
   $new_image = imagecreatetruecolor($width, $height);
   if( $this->image_type == IMAGETYPE_GIF || $this->image_type == IMAGETYPE_PNG ) {
      $current_transparent = imagecolortransparent($this->image);
      if($current_transparent != -1) {
         $transparent_color = imagecolorsforindex($this->image, $current_transparent);
         $current_transparent = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
         imagefill($new_image, 0, 0, $current_transparent);
         imagecolortransparent($new_image, $current_transparent);
      } elseif( $this->image_type == IMAGETYPE_PNG) {
         imagealphablending($new_image, false);
         $color = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
         imagefill($new_image, 0, 0, $color);
         imagesavealpha($new_image, true);
      }
   }
   imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
   $this->image = $new_image; 
}
}
?>

