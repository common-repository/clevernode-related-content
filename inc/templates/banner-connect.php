<?php
/**
 * Display content for Modal in plugin settings page.
 * 
 * @package clevernode-related-content
 * @since   1.0.7
 */
?>
<div class="clevernode-modal clevernode-modal-connect">
    <div class="modal-inner">
        <div class="modal-content">
            <div class="heading">
                <img src="<?php echo esc_url( $attr["logo_url"] ); ?>" alt="CleverNode Logo" width="60" height="60" />
                <h2>CleverNode is learning...</h2>
            </div>
            <h3>Give it up to 10 minutes to analyze your content and save your preferences</h3>

            <p><img src="<?php echo esc_url( $attr["icon_hand_r"] ); ?>" width="24" height="24"> Try browsing through the articles on your site to give CleverNode time to process your content.</p>
        </div>
        <a id="close-modal" href="#">&times;</a>
    </div>
</div>