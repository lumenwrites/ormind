<?php get_header(); ?>

<style>
#orbitSlider {
	
}

#content {
	padding: 0;
}

#home {
	padding: 20px;
}

.row {
	margin: 0!important;
}

.articles h2 {
	text-align: center;
	color: white;
	margin: 0;
	font-size: 16px;
	color: rgb(209, 204, 199);
}

#about h2,#about h3,#about h4 {
	color: white;
	margin: 0;
	font-size: 16px;
	color: rgb(209, 204, 199);
	
}
#about h4 {
	font-size: 12px;
}
#about img {
	width: 150px;
	float: left;	
	margin-right: 10px;
}

.articles img {
	width: 100%;
}
</style>

<div id="home">
	<div id="content" class="large-12 columns" role="main">
		<div id="orbitSlider" >
			<ul class="orbit" data-orbit>
				  <li>
				    <a href="http://digitalmind.io/post/artificial-neural-network-tutorial"><img src="images/orbit/ann-tutorial.png" alt="ann" /></a>
				  </li>				
				  <li>
				    <a href="/cg-2d/"><img src="images/orbit/slideshow_01.jpg" alt="2D art" /></a>
				    <div class="orbit-caption">
					    My 2D art.
				    </div>
				  </li>		
				  <li>
				    <a href="/cg-3d/"><img src="images/orbit/slideshow_04.jpg" alt="3D att" /></a>
				    <div class="orbit-caption">
					    My 3D art.
				    </div>
				  </li>					  
			</ul>
		</div>


		<div class="row">
			<div class="large-3 columns articles" role="main">
				<a href="http://digitalmind.io/post/achieving-flow-state"><h2> Achieving the flow state </h2></a>
			    <a href="http://digitalmind.io/post/achieving-flow-state"><img src="images/blog_header/startup-ideas.png" alt="slide 1" /></a>
			    <p> Learn how to enter the state of flow - state of extreme productivity,
				    complete engagement, and full immersion into what you're doing. </p>
			</div>
			<div class="large-3 columns articles" role="main">
				<a href="http://digitalmind.io/post/thinking-tools-how-use-your-mind-solve-challenges"><h2> Creative thinking tools </h2></a>
			    <a href="http://digitalmind.io/post/thinking-tools-how-use-your-mind-solve-challenges"><img src="images/blog_header/creativeThinkingTools.jpg" alt="slide 1" /></a>	
			    <p> In this article you will learn how to use creative thinking tools
				    to invent new ideas, solve challenges, and achieve your goals. </p>			    			
			</div>
			<div class="large-3 columns articles" role="main">
				<a href="http://digitalmind.io/post/best-startup-books"><h2> Best startup books </h2></a>
			    <a href="http://digitalmind.io/post/best-startup-books"><img src="images/blog_header/rework.png" alt="slide 1" /></a>	
			    <p> This is the collection of my top-favorite best startup books.
				    They're must read for everyone interested in entrepreneurship and business. </p>			    			
			</div>
			<div class="large-3 columns articles" role="main">
				<a href="http://digitalmind.io/post/artificial-neural-network-tutorial"><h2> ANN tutorial </h2></a>
			    <a href="http://digitalmind.io/post/artificial-neural-network-tutorial"><img src="images/blog_header/artificial-neural-networks.png" alt="slide 1" /></a>	
			    <p> This is a series of posts on Artificial Neural Networks.
				    Learn everything you need to know to make basic ANN
				    - from neuroanatomy to coding it in lisp. </p>			    			
			</div>				
		</div>
		
		<div class="row">
			<div class="large-6 columns articles" role="main">
				<h2> New Video </h2>
			    <div class="flex-video widescreen vimeo">
				    <iframe src="http://player.vimeo.com/video/96782301?title=0&amp;byline=0&amp;portrait=0" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
				</div>
			</div>
			<div class="large-6 columns" id="about" role="main">
				<h2> About Orange Mind </h2>
			    <a href="/posts/"><img src="images/signature.png" alt="slide 1" /></a>					
				<h3 id="rayalez">Hi! I am Ray Alez</h3>
				<p> I am an entrepreneur, AI scientist and a blogger. </p>
				<p> Welcome to my personal blog. Philosophy, technology, wit, random sociopathic thoughts. Enjoy =) </p></div>
				</div>
		</div>		
	</div>	
	<div class="row"></div>
</div>
 

<?php get_footer(); ?>