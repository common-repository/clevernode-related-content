<?php
/**
 * Display content for Banner in plugin settings page.
 * 
 * @package clevernode-related-content
 * @since   1.0.7
 */
?>
<div class="clevernode-banner hide">
    <div class="banner-content">
        <div class="col-1">
            <img src="<?php echo esc_url( $attr["logo_url"] ); ?>" alt="CleverNode Logo" width="100" height="100" />
        </div>
        <div class="col-2">
            <h2>Would you like better accuracy in choosing your related posts?</h2>
            <h3>Upgrade to the premium version!</h3>

            <p>You will be able to choose the content selection algorithm and customize the widget style.</p>
        </div>
        <div class="col-3">
            <a class="button button-primary" href="https://premium.clevernode.it" target="_blank">Go Premium!</a>
        </div>
    </div>
    <a id="close-banner" href="#">&times;</a>
</div>