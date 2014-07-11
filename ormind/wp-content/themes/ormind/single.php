<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage WP_Starter
 * @since WP-Forge 1.0
 */

get_header(); ?>
<?php //include('blog-header.html') ?>
	<div id="content" class="large-9 columns" role="main">
    
    	<?php if ( function_exists('yoast_breadcrumb') ) { yoast_breadcrumb('<ul class="breadcrumbs">','</ul>'); } ?>

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', get_post_format() ); ?>




<div style="padding: 10px 10px 0 10px; margin: 12px;height: 52px;" class="panel">

<div id="mc_embed_signup" style="float:right;width: 690px;">
<p style="float:left;margin-top:4px">Liked this post? Receive weekly updates!</p>
<form action="http://digital-mind.us7.list-manage.com/subscribe/post?u=0f23c7984541d915d1108a16e&amp;id=ec535fdd6a" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate="">
			
			<div class="mc-field-group">
				<input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL" placeholder="Enter your email" style="width:200px;	height:30px; border: 0;padding: 10px;margin: 0 0 0 20px;float:left;					
				"><input type="submit" id="subscribeButton" value="Get updates!" name="subscribe" class="small button" style="border:0;width:200px;height:30px; margin: 0 0 0 0;cursor:pointer;font-size:16px;line-height: 0px;">				
			</div>
			<div id="mce-responses" class="clear">
			<div class="response" id="mce-error-response" style="display:none"></div>
			<div class="response" id="mce-success-response" style="display:none"></div></div></form>
</div>
</div>



				<nav class="nav-single">
					<span class="nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '&laquo;', 'Previous post link', 'wpstarter' ) . '</span> %title' ); ?></span>
					<span class="nav-next"><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&raquo;', 'Next post link', 'wpstarter' ) . '</span>' ); ?></span>
				</nav><!-- .nav-single -->

<!-- BANNER WEBDEV -->
<!--
<div style="margin: 12px; background:none; border:none; margin-top:20px;" class="panel">
  <a href="http://digitalmind.io/services/">
		<img width="100%" src="http://digitalmind.io/static/img/blog_header/banner.png">
  </a>
</div>
-->

				<?php comments_template( '', true ); ?>

			<?php endwhile; // end of the loop. ?>

	</div><!-- #content -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>