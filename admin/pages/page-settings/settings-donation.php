<div class="welcome-panel">
    <h1><span class="fa fa-heartbeat"></span> <?php _e('DONATE',CFGP_NAME); ?></h1>
    <p class="about-description">
	<?php echo sprintf(__("If you really like this plugin, we will continue to develop and update it. You can donate some money to our development team because in the future we plan to improve this plugin and add new functions and better user experience.%s Thank you for your concern.%s Sincerely, your %s",CFGP_NAME),'<br><br>','<br><br>', '<a href="http://cfgeoplugin.com" target="_blank">'.__('CF GeoPlugin team',CFGP_NAME).'</a>'); ?></p><br>
    
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
    
        <!-- Identify your business so that you can collect the payments. -->
        <input type="hidden" name="business"
            value="creativform@gmail.com">
        
        <!-- Specify a Donate button. -->
        <input type="hidden" name="cmd" value="_donations">
        
        <!-- Specify details about the contribution -->
        <input type="hidden" name="item_name" value="<?php _e('CF GeoPlugin Donation',CFGP_NAME); ?>">
        <input type="hidden" name="item_number" value="<?php _e('Donation to CF team for the improvement and maintenance of CF GeoPlugin',CFGP_NAME); ?>">
        <input type="hidden" name="currency_code" value="USD">
        
        <!-- Display the payment button. -->
        <input type="image" name="submit" border="0"
        src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif"
        alt="PayPal - The safer, easier way to pay online">
        <img alt="" border="0" width="1" height="1" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" >
    
    </form>
</div>