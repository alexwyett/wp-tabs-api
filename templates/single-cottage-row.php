<?php

$mainImage = $property->getMainImage();
if ($mainImage) {
    $mainImage = $mainImage->getFilename();
} else {
    $mainImage = '';
}

$shortlistLink = sprintf(
    '<p><a href="%s">Add to shortlist</a></p>',
    $wpTabsApi->getCottagePermalink($property, 'shortlist')
);
if ($property->isOnShortlist()) {
    $shortlistLink = sprintf(
        '<p><a href="%s">Remove from shortlist</a></p>',
        $wpTabsApi->getCottagePermalink($property, 'shortlist')
    );
}

echo sprintf(
    '<div class="cottage-row clear">
        <a href="%s">
            <img src="%s">
            <h2>%s</h2>
        </a>
        <div class="avail-desc">
            <p>%s</p>
            <p>Sleeps: %s | Bedrooms: %s</p>
            %s
        </div>
    </div>',
    $wpTabsApi->getCottagePermalink($property),
    WpTabsApi__getImageCache($property->getPropRef(), $mainImage, 'tocc', 200, 175),
    $property->getName(),
    $property->getAvailabilityDescription(),
    $property->getAccommodates(),
    $property->getBedrooms(),
    $shortlistLink
);