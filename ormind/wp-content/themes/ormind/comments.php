<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form. The actual display of comments is
 * handled by a callback to wpstarter_comment() which is
 * located in the functions.php file.
 *
 * @package WordPress
 * @subpackage WP_Forge
 * @since WP-Forge 1.0
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() )
	return;
?>

<div style="padding:10px; margin:13px;margin-top:32px; background:rgba(255,255,255,0.95); color:black; font-family: 'Titillium Web'; border-radius:0;">
		
			<div style="width:120px; height: 20px; margin-top:10px; padding:10; padding-top:4;float:right;">
				<span style="float:left;font-size:12;">Share:</span>		
				<div class="addthis_toolbox addthis_default_style addthis_16x16_style" style="margin-left:40;">
				<a class="addthis_button_google_plusone_share"></a>
				<a class="addthis_button_facebook"></a>
				<a class="addthis_button_twitter"></a>
				<a class="addthis_button_pinterest_share"></a>			
				</div>
				<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=xa-52748ecf6760beb3"></script>
			</div>
		
			<div style="float:left; margin:0; height:20px; width:400px; font-size:18px; color:#f55b2c; color:black;"> 
			<h4 style="float:left;" >Comments:</h4>
			</div> 					
	<hr style="	width: 885;
				border: 0;
				background: gray;
				height: 1px;
				margin: 30px 0 10px -10;"/>
    <div id="disqus_thread"></div>
    <script type="text/javascript">
        /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
        var disqus_shortname = 'ormind'; // required: replace example with your forum shortname

        /* * * DON'T EDIT BELOW THIS LINE * * */
        (function() {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
    </script>
    <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
    <a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>

</div><!-- #comments .comments-area -->