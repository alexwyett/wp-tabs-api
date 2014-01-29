<?php

// The word press loop...
// The cottage post type is just another page.
while (have_posts()) {    
    the_post(); 
    ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php

    // This function is defined in the wp-tabs-api-plugin.php file.  Used
    // just as an example to demonstrate abstracting some of the template
    // logic.
    echo WpTabsApi__getPropertySummary();
    ?>
    </article>

    <?php
}