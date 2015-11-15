<?php
if( ! defined( 'MC4WP_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

// prevent caching, declare constants
if( ! defined( 'DONOTCACHEPAGE' )) {
	define( 'DONOTCACHEPAGE', true );
}

if( ! defined( 'DONOTMINIFY' )) {
	define( 'DONOTMINIFY', true );
}

if( ! defined( 'DONOTCDN' )) {
	define( 'DONOTCDN', true );
}

// render simple page with form in it.
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
	<link type="text/css" rel="stylesheet" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
	<?php wp_head(); ?>
    
    <style type="text/css">
        html, 
        body{ 
            min-height: 100% !important; 
            height: auto !important; 
        }

        body{ 
            padding:20px !important; 
            background:white; 
            width: 100%; 
            max-width: 100% !important;
            box-sizing: border-box;
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
         }

        #blackbox-web-debug, 
        #wpadminbar{ 
            display:none !important; 
        }
    </style>

    <style type="text/css" id="custom-css"></style>
    <!--[if IE]>
        <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>
<body <?php body_class(); ?>>
	<?php mc4wp_form( absint( $_GET['form_id'] ) ); ?>
	<?php wp_footer(); ?>
</body>
</html>
