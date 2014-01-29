<?php

$mainImage = $property->getMainImage();
if ($mainImage) {
    $mainImage = $mainImage->getFilename();
} else {
    $mainImage = '';
}

?>
<article class="entry-header cottage-summary clear">
    <div class="main-image">
        <img src="<?php echo WpTabsApi__getImageCache($property->getPropRef(), $mainImage, 'tocc', 200, 175); ?>" alt="<?php the_title(); ?>">
    </div>
    <div class="summary">
        <h2 class="entry-title">
            <?php the_title(); ?>
        </h2>
        <h3>Sleeps: <?php echo $property->getAccommodates(); ?></h3>
        <h4>Bedrooms: <?php echo $property->getBedrooms(); ?></h4>
        <p><?php echo $property->getAvailabilityDescription(); ?></p>
        <?php
            if ($property->isOnShortlist()) {
                echo sprintf(
                    '<p><a href="%s">Remove from shortlist</a></p>',
                    WpTabsApi__getEndPointPermalink($post->ID, 'shortlist')
                );
            } else {
                echo sprintf(
                    '<p><a href="%s">Add to shortlist</a></p>',
                    WpTabsApi__getEndPointPermalink($post->ID, 'shortlist')
                );
            }
        ?>
    </div>
</article>