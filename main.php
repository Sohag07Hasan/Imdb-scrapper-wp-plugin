<?php
/*
 * plugin name: Imdb data auto insertion
 * author: Sohag Hasan
 * author uri: http://sohag.me
 *
 */ 
 define('IMDB',dirname(__FILE__));
 define('IMDBFILE',__FILE__);
 define('IMDB_CLASSES',IMDB . '/classes');
 define('IMDB_INCLUDES',IMDB . '/includes');
 
 //including the main files
 
 include IMDB_CLASSES . '/imdb.php';
 include IMDB_CLASSES . '/imdb-scraping-easy.php';

?>
