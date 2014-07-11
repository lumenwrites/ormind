<?php
/**
 * The template for displaying the footer.
 *
 * Contains footer content and the closing of the
 * #main and #page div elements.
 *
 * @package WordPress
 * @subpackage WP_Starter
 * @since WP-Starter 1.0
 */
?>
	</section><!-- #main .wrapper -->
    
	<?php
        if ( ! is_404() )
        get_sidebar( 'footer' );
    ?>    
        
	<footer class="row" role="contentinfo">

        <div class="large-7 columns">
        
        	<?php wp_nav_menu( array(
            	'theme_location' => 'secondary',
                'container' => false,
                'menu_class' => 'inline-list left',
                'fallback_cb' => false
            ) ); ?>
                
       	</div><!-- .seven columns -->
             
		<div class="site-info large-5 columns">
        
            <?php esc_attr_e('Powered by', 'wpstarter'); ?><a href="<?php echo esc_url(__('http://wpstarter.themeawesome.com','wpstarter')); ?>" rel="follow" target="_blank" title="<?php esc_attr_e('A Child Theme for WP-Forge', 'wpstarter'); ?>">
            <?php printf('WP-Starter'); ?></a> &amp; <a href="<?php echo esc_url(__('http://wordpress.org/','wpstarter')); ?>" target="_blank" title="<?php esc_attr_e('WordPress', 'wpstarter'); ?>">
            <?php printf('WordPress'); ?></a>
            
		</div><!-- .site-info -->

	</footer><!-- .row -->
    
	</div><!-- #wrapper -->  
    
    <div id="backtotop">
    
        <i class="fa fa-chevron-circle-up fa-3x"></i>   
    
    </div><!-- #backtotop -->  

<?php wp_footer(); ?>
<script>
jQuery( document ).ready(function($) {
	$(".vitimage").hide();
	var divs = $("img.vitimage").get().sort(function(){ 
    	return Math.round(Math.random())-0.5; //so we get the right +/- combo
    }).slice(0,1);
    $(divs).show();
    
/*	$(".widget_text").hide();
	var affbanners = $(".widget_text").get().sort(function(){ 
    	return Math.round(Math.random())-0.5; //so we get the right +/- combo
    }).slice(0,4);
   $(affbanners).show();		
	$(".widget_text").first().show();//show first "get updages" text widget. */

jQuery(".orbit-bullets").remove();		
});
</script>
</body>
</html>