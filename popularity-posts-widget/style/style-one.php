
<?php

$css_path = plugins_url('ppw.css', __FILE__);
wp_enqueue_style('popularity-posts-widget', $css_path, false);

?>
<li>
<div class="row">
	<div class="desktop-3 tablet-2 mobile-1">
		<?php
			 if ($instance['show_thumbs']) {
			?> 

			 <a href="<?php echo $permalink; ?>" title="<?php echo $title_posts; ?>" >
		    <?php echo ppw_get_Thumbs($row['id'], $thumbs_settings); ?>
			 </a>
			 	
			 <?php
			 }
			 ?>
	</div>
	<div class="desktop-9 tablet-4 mobile-2">
		<span class="post-stats">
		<span class="ppw-views"><?php echo $hits_to_show; ?></span><?php echo $com_pref; ?>
		<span class="ppw-comments"><?php echo $comments_to_show; ?></span> 
		<span class="ppw-date"><?php echo $date_pref.ppw_get_PostDate($row['id'], $instance['date_checkbox'], $instance['date_format'] ); ?></span>
		</span>
		<span class="ppw-post-title"><a href="<?php echo $permalink; ?>" title="<?php echo $title_posts; ?>" rel="<?php echo 'nofollow'; ?>"><?php echo ppw_get_TrimTitle($title_posts, $instance['posts_title_length']); ?></a></span>
	</div>
</div>
</li>