<?php
/**
* @file downloadPriceTemplate.php
* @author Tahir M. Malik
* @date 22 Nov 2020
* @copyright 2021 WebSoftOps Tahir M. Malik
* @brief PHP File to download the template for prices saved in the database
*/
  // Initialize a file URL to the variable
  $path = $_SERVER['DOCUMENT_ROOT'] . '/files/Musterdatei.xlsx';

  // Use basename() function to return the base name of file
  // $file_name = basename( $url );
  //
  // // Use file_get_contents() function to get the file
  // // from url and use file_put_contents() function to
  // // save the file by using base name
  // if( file_put_contents( $file_name, file_get_contents( $url ) ) ) {
  //   echo "File downloaded successfully";
  // }
  // else {
  //   echo "File downloading failed.";
  // }

  header( "Content-Description: File Transfer" );
  header( "Content-Type: application/octet-stream" );
  header( 'Content-Disposition: attachment; filename="'.basename( $path ).'"' );
  header( "Content-Transfer-Encoding: binary" );
  header( 'Expires: 0' );
  header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
  header( 'Pragma: public' );
  header( "Content-Type: application/force-download" );
  header( "Content-Type: application/download" );
  header( "Content-Length: ".filesize( $path ) );
  readfile( $path );
  exit;

?>
