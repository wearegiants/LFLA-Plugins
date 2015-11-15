<?php

$bg 		= get_option( 'woocommerce_email_background_color' );
$bodybg		= get_option( 'woocommerce_email_body_background_color' );
$base 		= get_option( 'woocommerce_email_base_color' );
$text 		= get_option( 'woocommerce_email_text_color' );
$width 		= get_option( 'woocommerce_email_mbc_template_width' , 700 );
$logowidth 	= get_option( 'woocommerce_email_mbc_template_logo_width' , 175 );
$bodyfontsize = get_option( 'woocommerce_email_mbc_bodyfontsize' , 12 ).'px';

$dlfontsize = get_option( 'woocommerce_email_mbc_dlsize' , 12 ).'px';
$dltextcolor = get_option( 'woocommerce_email_mbc_dlcolor' , '#000000' );
$elcolor = get_option( 'woocommerce_email_mbc_extra_link_color', '#0000EE');

$h1 = get_option( 'woocommerce_email_mbc_h1size' , 18 ).'px';
$h2 = get_option( 'woocommerce_email_mbc_h2size' , 16 ).'px';
$h3 = get_option( 'woocommerce_email_mbc_h3size' , 14 ).'px';
$h1s = (get_option( 'woocommerce_email_mbc_h1size' , 18 ) - 2 ).'px';
$h2s = (get_option( 'woocommerce_email_mbc_h2size' , 16 ) - 2 ).'px';
$h3s = (get_option( 'woocommerce_email_mbc_h3size' , 14 ) - 2 ).'px';

$displayimage = false;
$optimage = get_option( 'woocommerce_email_mbc_displayimage');
if($optimage == 'yes')
$displayimage = true;

$imgsize = get_option( 'woocommerce_email_mbc_displayimage_size', 32 );

$bordercolor = get_option( 'woocommerce_email_mbc_bordercolor' , '#EEE' );


$h1style = "style=\"font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: $h1; margin: 0; padding: 0;color : $text\"";
$h2style = "style=\"font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: $h2; margin: 0; padding: 0;color : $text\"";
$h3style = "style=\"font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: $h3; margin: 0; padding: 0;color : $text\"";
$pstyle = "style=\"font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: $bodyfontsize; margin: 0; padding: 0;color : $text\"";
$missingstyle = "font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box;color:$text;font-size:$bodyfontsize;";
$orderref = "style=\"font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: $h2; margin: 0; padding: 5px; margin-bottom: 20px; margin-top: 20px; text-align:center; background-color: $bodybg; border:solid 1px $bordercolor; color : $text\"";

$dlstyle = "font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box;color:$dltextcolor;font-size:$dlfontsize;";

$fblogopath = get_option( 'woocommerce_email_mbc_facebook_img' , plugins_url( 'img/facebook.png', __FILE__ ) );
if($fblogopath === '')
	$fblogopath = plugins_url( 'img/facebook.png', __FILE__ );

$tlogopath = get_option( 'woocommerce_email_mbc_twitter_img' , plugins_url( 'img/twitter.png', __FILE__ ) );
if($tlogopath === '')
	$tlogopath = plugins_url( 'img/twitter.png', __FILE__ );

$ilogopath = get_option( 'woocommerce_email_mbc_instagram_img' , plugins_url( 'img/instagram.png', __FILE__ ) );
if($ilogopath === '')
	$ilogopath = plugins_url( 'img/instagram.png', __FILE__ );

$plogopath = get_option( 'woocommerce_email_mbc_pinterest_img' , plugins_url( 'img/pinterest.png', __FILE__ ) );
if($plogopath === '')
	$plogopath = plugins_url( 'img/pinterest.png', __FILE__ );

$glogopath = get_option( 'woocommerce_email_mbc_google_img' , plugins_url( 'img/googleplus.png', __FILE__ ) );
if($glogopath === '')
	$glogopath = plugins_url( 'img/googleplus.png', __FILE__ );
