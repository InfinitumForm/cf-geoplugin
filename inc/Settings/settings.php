<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

$options = apply_filters('cfgp/settings', array());

?><div class="wrap cfgp-wrap" id="<?php echo CFGP_NAME; ?>-settings">
    <h1 class="wp-heading-inline"><i class="fa fa-globe"></i> <?php _e('Settings', CFGP_NAME); ?></h1>
    <hr class="wp-header-end">

    <div id="post">
      <div id="poststuff" class="metabox-holder has-right-sidebar">

        <div class="inner-sidebar" id="<?php echo CFGP_NAME; ?>-settings-sidebar">
    			<div id="side-sortables" class="meta-box-sortables ui-sortable">
    				<?php do_action('cfgp/page/settings/sidebar'); ?>
    			</div>
    		</div>

    		<div id="post-body">
    			<div id="post-body-content">
            <form method="post" action="<?php echo admin_url('/admin.php?page=cf-geoplugin-settings&save_settings=true&nonce='.wp_create_nonce(CFGP_NAME.'-save-settings')); ?>">
  		        <div class="<?php echo CFGP_NAME; ?>-sticky-button"><?php submit_button(); ?></div>
                <div class="nav-tab-wrapper-chosen">
                    <nav class="nav-tab-wrapper">
                      <?php do_action('cfgp/settings/nav-tab/before'); ?>
                    <?php foreach($options as $o=>$option) : ?>
                        <a href="javascript:void(0);" class="nav-tab<?php echo ($o===0 ? ' nav-tab-active' : ''); ?>" data-id="#<?php echo esc_attr($option['id']); ?>"><?php echo $option['title']; ?></a>
                    <?php endforeach; ?>
                      <?php do_action('cfgp/settings/nav-tab/after'); ?>
                    </nav>
                    <?php do_action('cfgp/settings/tab-panel/before'); ?>
                <?php foreach($options as $o=>$option) : ?>
                    <div class="cfgp-tab-panel<?php echo ($o===0 ? ' cfgp-tab-panel-active' : ''); ?>" id="<?php echo esc_attr($option['id']); ?>">
                    <?php if(isset($option['sections']) && is_array($option['sections'])) :
        foreach($option['sections'] as $s=>$section) : ?>
        			<section class="cfgp-tab-panel-section" id="cfgp-tab-panel-section-<?php echo $section['id']; ?>">
                      <?php if(!empty($section['title'])) : ?><h2 class="title"><?php echo $section['title']; ?></h2><?php endif; ?>
                        <?php if(!empty($section['desc'])) : ?>
                          <?php if(is_array($section['desc'])) : ?>
                            <?php foreach($section['desc'] as $desc_text) : ?>
                              <p class="cfgp-section-description"><?php echo $desc_text; ?></p>
                            <?php endforeach; ?>
                          <?php else : ?>
                            <p class="cfgp-section-description"><?php echo $section['desc']; ?></p>
                          <?php endif; ?>
                        <?php endif; ?>
                        <table class="form-table cfgp-form-table" role="presentation" id="<?php echo esc_attr($section['id']); ?>">
                            <tbody>
                            <?php if(isset($section['inputs']) && is_array($section['inputs'])) :
								foreach($section['inputs'] as $i=>$input) :
								$is_plugin = $is_plugin_active = false;
								if(isset($input['plugin_active']) && is_array($input['plugin_active'])) {
								  $is_plugin = true;
								  foreach($input['plugin_active'] as $plugin) {
									if( is_plugin_active($plugin . '.php') ){
									  $is_plugin_active = true;
									  break;
									}
								  }
								}
					
								if($is_plugin===true && $is_plugin_active === false) {
								  continue;
								}
					
							?>
								<tr>
									<th scope="row" valign="top" class="cfgp-label">
									  <label for="cfgp-<?php echo esc_attr($input['name']); ?>"><?php echo esc_attr($input['label']); ?></label> <?php if(!empty($input['desc'])) : ?><i class="fa fa-question-circle fa-cfgeo-help cfgp-help" data-help="true" title="<?php echo esc_attr($input['desc']); ?>"></i><?php endif; ?>
									</th>
									<td valign="top" class="cfgp-field">
<?php
switch($input['type'])
{
	### RADIO
	case 'radio': if(isset($input['options'])) : foreach($input['options'] as $value => $name) : ?>
    <span class="input-radio">
        <input type="radio" name="<?php
          echo CFGP_NAME;
        ?>[<?php
          echo $input['name'];
        ?>]" value="<?php
          echo esc_attr($value);
        ?>" id="cfgp-<?php
          echo esc_attr($input['name']);
        ?>-<?php echo $value; ?>"<?php
			if(isset($input['attr']) && !empty($input['attr']) && is_array($input['attr']))
			{
				foreach($input['attr'] as $attr=>$attr_value){
					printf(' %s="%s"', $attr, esc_attr($attr_value));
				}
			}
			echo (CFGP_Options::get($input['name'], (isset($input['default']) ? $input['default'] : '')) === $value ? ' checked' : '');
			echo (isset($input['readonly']) && $input['readonly'] ? ' readonly' : '');
			echo (isset($input['disabled']) && $input['disabled'] ? ' disabled' : '');
        ?>><?php echo $name; ?>
    </span>
	<?php endforeach; endif; break;


	### CHECKBOX
	case 'checkbox': $default = CFGP_Options::get($input['name'], (isset($input['default']) ? $input['default'] : [])); if(isset($input['options'])) : foreach($input['options'] as $i => $object) : ?>
    <span class="input-radio">
        <input type="checkbox" name="<?php
          echo CFGP_NAME;
        ?>[<?php
          echo $input['name'];
        ?>][<?php echo $i; ?>]" value="<?php
          echo esc_attr($object['value']);
        ?>" id="<?php
          echo esc_attr($object['id']);
        ?>"<?php
			if(isset($input['attr']) && !empty($input['attr']) && is_array($input['attr']))
			{
				foreach($input['attr'] as $attr=>$attr_value){
					printf(' %s="%s"', $attr, esc_attr($attr_value));
				}
			}
			echo (in_array($object['value'], $default) ? ' checked' : '');
			echo (isset($input['readonly']) && $input['readonly'] ? ' readonly' : '');
			echo (isset($input['disabled']) && $input['disabled'] ? ' disabled' : '');
        ?>><?php echo $object['label']; ?>
    </span>
	<?php endforeach; endif; break;
	
	### INPUT
	case 'number':
	case 'url':
	case 'tel':
	case 'text':
	case 'password':
	case 'email':
	$default = CFGP_Options::get($input['name'], (isset($input['default']) ? $input['default'] : '')); ?>
	<input type="<?php echo $input['type']; ?>" name="<?php
	  echo CFGP_NAME;
	?>[<?php
	  echo $input['name'];
	?>]" value="<?php
	  echo esc_attr($default);
	?>" id="cfgp-<?php
	  echo esc_attr($input['name']);
	?>"<?php
		
		if(isset($input['attr']) && !empty($input['attr']) && is_array($input['attr']))
		{
			foreach($input['attr'] as $attr=>$attr_value){
				printf(' %s="%s"', $attr, esc_attr($attr_value));
			}
		}
		
		echo (isset($input['readonly']) && $input['readonly'] ? ' readonly' : '');
		echo (isset($input['disabled']) && $input['disabled'] ? ' disabled' : '');
	?>>
	<?php break;
	
	### TEXTAREA
	case 'textarea':
	$default = CFGP_Options::get($input['name'], (isset($input['default']) ? $input['default'] : '')); ?>
	<textarea type="<?php echo $input['type']; ?>" name="<?php
	  echo CFGP_NAME;
	?>[<?php
	  echo $input['name'];
	?>]" id="cfgp-<?php
	  echo esc_attr($input['name']);
	?>"<?php
		
		if(isset($input['attr']) && !empty($input['attr']) && is_array($input['attr']))
		{
			foreach($input['attr'] as $attr=>$attr_value){
				printf(' %s="%s"', $attr, esc_attr($attr_value));
			}
		}
		
		echo (isset($input['readonly']) && $input['readonly'] ? ' readonly' : '');
		echo (isset($input['disabled']) && $input['disabled'] ? ' disabled' : '');
	?>><?php echo $default; ?></textarea>
	<?php break;
	
	
	### SELECT
	case 'select': $default = CFGP_Options::get($input['name'], (isset($input['default']) ? $input['default'] : '')); ?>
	<select name="<?php
	  echo CFGP_NAME;
	?>[<?php
	  echo $input['name'];
	?>]" id="cfgp-<?php
	  echo esc_attr($input['name']);
	?>"<?php
		
		if(isset($input['attr']) && !empty($input['attr']) && is_array($input['attr']))
		{
			foreach($input['attr'] as $attr=>$attr_value){
				printf(' %s="%s"', $attr, esc_attr($attr_value));
			}
		}
		
		echo (isset($input['readonly']) && $input['readonly'] ? ' readonly' : '');
		echo (isset($input['disabled']) && $input['disabled'] ? ' disabled' : '');
	?>>
	<?php foreach($input['options'] as $value => $label) : ?>
    	<option value="<?php echo esc_attr($value); ?>"<?php echo ($default == $value ? ' selected' : ''); ?>><?php echo $label; ?></option>
    <?php endforeach; ?>
    </select>
	<?php break;
	
	
	### MULTIPLE
	case 'multiple': $default = CFGP_Options::get($input['name'], (isset($input['default']) ? $input['default'] : [])); ?>
	<select name="<?php
	  echo CFGP_NAME;
	?>[<?php
	  echo $input['name'];
	?>]" id="cfgp-<?php
	  echo esc_attr($input['name']);
	?>"<?php
		
		if(isset($input['attr']) && !empty($input['attr']) && is_array($input['attr']))
		{
			foreach($input['attr'] as $attr=>$attr_value){
				printf(' %s="%s"', $attr, esc_attr($attr_value));
			}
		}
		
		echo (isset($input['readonly']) && $input['readonly'] ? ' readonly' : '');
		echo (isset($input['disabled']) && $input['disabled'] ? ' disabled' : '');
	?> multiple>
	<?php foreach($input['options'] as $value => $label) : ?>
    	<option value="<?php echo esc_attr($value); ?>"<?php echo (in_array($value, $default) ? ' selected' : ''); ?>><?php echo $label; ?></option>
    <?php endforeach; ?>
    </select>
	<?php break;
}
?>
                                    </td>
                                </tr>
                                <?php endforeach;
                        endif; ?>
                            </tbody>
                        </table>
                    </section>
                    <?php endforeach; ?>
                    <?php submit_button(); ?>
                    <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                  <?php do_action('cfgp/settings/tab-panel/after'); ?>
                </div>
            </form>
    			</div>
    		</div>
    		<br class="clear">
    	</div>
    </div>
</div>
