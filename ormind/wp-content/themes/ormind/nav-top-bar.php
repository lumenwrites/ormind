    <div class="navcontainer large-6 columns">
        <nav class="top-bar">
<!--            <ul class="title-area">
                <li class="name">
                    <h1><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">Home</a></h1>
                </li>
                <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
            </ul> -->
            <section class="top-bar-section">
            <?php
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'container' => false,
                    'depth' => 0,
                    'items_wrap' => '<ul class="right">%3$s</ul>',
                    'fallback_cb' => 'wpforge_menu_fallback', // workaround to show a message to set up a menu
                    'walker' => new wpforge_walker( array(
                        'in_top_bar' => true,
                        'item_type' => 'li'
                    ) ),
                ) );
            ?>
            </section>
        </nav><!-- End of Top-Bar -->
    </div><!-- .columns -->    
