<?php
//
///*
// * Trigger this file on Plugin uninstall
// *
// *
// */
//
//if (! defined('WP_UNINSTAL_PLUGIN')){
//    die();
//}
//
////Clear Database Stored Data
//$books = get_posts(array('post_type'=>'book','numberposts'=>1));
//
//foreach ($books as $book){
//    wp_delete_post($book->ID,true);
//}
//
////Access the Database via SQL
////global $wpdb;
////$wpdb->query("DELETE FROM wp_posts WHERE post_type = 'book'");