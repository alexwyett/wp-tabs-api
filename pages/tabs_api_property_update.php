<?php

/**
 * Property Sync Script
 *
 * PHP Version 5.3
 * 
 * @category  WPTabsAPI
 * @package   Wordpress
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2013 Alex Wyett
 * @license   http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link      http://www.carltonsoftware.co.uk
 */

global $wpTabsApi;

if (count($_POST) > 0) {
    if (checkArrayKeyExistsAndHasAValue($_POST, "action", "update")) {
        if (checkArrayKeyExistsAndIsGreaterThanLength(
            $_POST, 
            WPTABSAPIPLUGIN_COTTAGE_POST_TYPE_KEY
        )) {
            $wpTabsApi->setCottagePostType(
                $_POST[WPTABSAPIPLUGIN_COTTAGE_POST_TYPE_KEY]
            );
        }
    }
    
    if (checkArrayKeyExistsAndHasAValue($_POST, "flush", "1")) {
        $wpTabsApi->flushCottageIndexes();
    }
    
    // Check setting has been set and update cottage index
    $wpTabsApi->updateCottageIndexes();
}

?>

<form method="post" action="">  
    <input type="hidden" name="action" value="update" />  
    <h3>Tabs API Plugin Settings</h3>
<?php

if ($wpTabsApi->getCottagePostType()) {
    
    ?>
    <p>Note, if there are new cottages which are not being shown.  
    Click on the button below to add them to the wordpress 
    page index.</p>
    <p>Also, if you are changing you post type...you will need to flush the
    rewrite rules.  You can do this by clicking the save changes button in
    the <strong>Settings > Permalinks</strong> section.</p>
    <?php

}

?>
    <table class="form-table">    
    <?php
        echo sprintf(
            '<tr>
                <th>
                    <label for="%s">Cottage Post Type: </label>
                </th>
                <td>
                    %s
                    <div class="field-description">
                        This is the stub you wish cottages to be added under.
                    </div>
                </td>
            </tr>',
            WPTABSAPIPLUGIN_COTTAGE_POST_TYPE_KEY,
            getInputField(
                WPTABSAPIPLUGIN_COTTAGE_POST_TYPE_KEY, 
                get_option(WPTABSAPIPLUGIN_COTTAGE_POST_TYPE_KEY, 'cottage'), 
                'text',
                'maxlength="100" size="50"'
            )
        );
        echo sprintf(
            '<tr>
                <th>
                    <label for="%s">Flush cottage indexes?: </label>
                </th>
                <td>
                    %s
                    <div class="field-description">
                        Choosing this option will return all of the cottages first before re-indexing
                    </div>
                </td>
            </tr>',
            'flush',
            getInputField(
                'flush', 
                '', 
                'checkbox'
            )
        );
    ?>
    </table>
    <p class="submit">
<?php

$btnLabel = 'Create Property Index';
if ($wpTabsApi->getCottagePostType(false)) {
    $btnLabel = 'Update Property Index';
}

?>
        <input type="submit" class="button-primary" value="<?php echo $btnLabel; ?>">
    </p>
</form>

<?php
    $cottagesIndexed = $wpTabsApi->getAllIndexedCottages();
if ($cottagesIndexed) {
    ?>
            <h3>There are <?php echo $cottagesIndexed->found_posts; ?> 
                cottages in your Wordpress.</h3>
    <?php
    while ($cottagesIndexed->have_posts()) {
        $cottagesIndexed->the_post();
        echo sprintf(
            '<p><a href="%s" target="_new">%s</a></p>',
            get_permalink(),
            get_the_title()
        );
    }
}