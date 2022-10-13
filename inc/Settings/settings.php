<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

$options = apply_filters('cfgp/settings', []);

?><div class="wrap cfgp-wrap" id="<?php echo esc_attr(CFGP_NAME); ?>-settings">
    <h1 class="wp-heading-inline"><i class="cfa cfa-globe"></i> <?php _e('Settings', 'cf-geoplugin'); ?></h1>
    <hr class="wp-header-end">

    <div id="post">
      <div id="poststuff" class="metabox-holder has-right-sidebar">

    		<div id="post-body">
    			<div id="post-body-content">
            <form method="post" action="<?php echo esc_url( CFGP_U::admin_url('admin.php?page=cf-geoplugin-settings&save_settings=true&nonce='.wp_create_nonce(CFGP_NAME.'-save-settings')) ); ?>">
  		        <div class="<?php echo esc_attr(CFGP_NAME); ?>-sticky-button"><?php submit_button(); ?></div>
                <div class="nav-tab-wrapper-chosen">
                    <nav class="nav-tab-wrapper">
                      <?php do_action('cfgp/settings/nav-tab/before'); ?>
                    <?php foreach($options as $o=>$option) : ?>
                        <a href="javascript:void(0);" class="nav-tab<?php echo esc_attr($o===0 ? ' nav-tab-active' : ''); ?>" data-id="#<?php echo esc_attr($option['id']); ?>"><?php echo esc_html($option['title']); ?></a>
                    <?php endforeach; ?>
                      <?php do_action('cfgp/settings/nav-tab/after'); ?>
                    </nav>
                    <?php do_action('cfgp/settings/tab-panel/before'); ?>
                <?php foreach($options as $o=>$option) : ?>
                    <div class="cfgp-tab-panel<?php echo esc_attr($o===0 ? ' cfgp-tab-panel-active' : ''); ?>" id="<?php echo esc_attr($option['id']); ?>">
                    <?php if(isset($option['sections']) && is_array($option['sections'])) :
        foreach($option['sections'] as $s=>$section) : if(isset($section['enabled']) && $section['enabled'] === false) continue; ?>
        			<section class="cfgp-tab-panel-section" id="<?php echo esc_attr(sanitize_title($section['title'])); ?>">
                      <?php if(!empty($section['title'])) : ?><h2 class="title"><?php echo esc_html($section['title']); ?></h2><?php endif; ?>
                        <?php if(!empty($section['desc'])) : ?>
                          <?php if(is_array($section['desc'])) : ?>
                            <?php foreach($section['desc'] as $desc_text) : ?>
                              <p class="cfgp-section-description"><?php echo esc_html($desc_text); ?></p>
                            <?php endforeach; ?>
                          <?php else : ?>
                            <p class="cfgp-section-description"><?php echo esc_html($section['desc']); ?></p>
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
									  <label for="cfgp-<?php echo esc_attr($input['name']); ?>"><?php echo esc_attr($input['label']); ?></label> <?php if(!empty($input['desc'])) : ?><i class="cfa cfa-question-circle fa-cfgeo-help cfgp-help" data-help="true" data-title="<?php echo esc_attr($input['desc']); ?>"></i><?php endif; ?>
									</th>
									<td valign="top" class="cfgp-field">
<?php
switch($input['type'])
{
	### INFOBOX
	case 'info': if(isset($input['info'])) : ?>
	<div class="cfgp-field-description cfgp-field-description-<?php echo esc_attr($input['type']); ?>"><?php echo wp_kses_post($input['info']); ?></div>
	<?php endif; break;
	
	
	### RADIO
	case 'radio':
	$default = CFGP_Options::get($input['name'], (isset($input['default']) ? $input['default'] : ''));
	if(isset($input['options'])) : foreach($input['options'] as $value => $name) : ?>
    <span class="input-radio<?php
          echo esc_html(isset($input['style']) ? ' ' . $input['style'] : '');
        ?>">
        <input type="radio" name="<?php
          echo esc_attr(CFGP_NAME);
        ?>[<?php
          echo esc_attr($input['name']);
        ?>]" value="<?php
          echo esc_attr($value);
        ?>" id="cfgp-<?php
          echo esc_attr($input['name']);
        ?>-<?php echo esc_attr($value); ?>"<?php
			if(isset($input['attr']) && !empty($input['attr']) && is_array($input['attr']))
			{
				foreach($input['attr'] as $attr=>$attr_value){
					printf(' %s="%s"', $attr, esc_attr($attr_value));
				}
			}
			echo ($default === $value ? ' checked' : '');
			echo (isset($input['readonly']) && $input['readonly'] ? ' readonly' : '');
			echo (isset($input['disabled']) && $input['disabled'] ? ' disabled' : '');
        ?>><?php echo esc_html($name); ?>
    </span>
	<?php endforeach; if(isset($input['info']) && !empty($input['info'])) : 
		?><br><div class="cfgp-field-description cfgp-field-description-<?php echo esc_attr($input['type']); ?>"><?php echo wp_kses_post($input['info']); ?></div><?php 
	endif; endif; break;


	### CHECKBOX
	case 'checkbox': 
	$default = CFGP_Options::get($input['name'], (isset($input['default']) ? $input['default'] : []));
	if(isset($input['options'])) : ?>
	<input type="hidden" name="<?php
	  echo esc_attr(CFGP_NAME);
	?>[<?php
	  echo esc_attr($input['name']);
	?>]" value="">
	<?php foreach($input['options'] as $i => $object) : ?>
    <span class="input-radio<?php
          echo esc_html(isset($input['style']) ? ' ' . $input['style'] : '');
        ?>">
        <input type="checkbox" name="<?php
          echo esc_attr(CFGP_NAME);
        ?>[<?php
          echo esc_attr($input['name']);
        ?>][<?php echo esc_attr($i); ?>]" value="<?php
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
        ?>><?php echo esc_html($object['label']); ?>
    </span>
	<?php endforeach; if(isset($input['info']) && !empty($input['info'])) : 
		?><br><div class="cfgp-field-description cfgp-field-description-<?php echo esc_attr($input['type']); ?>"><?php echo wp_kses_post($input['info']); ?></div><?php 
	endif; endif; break;
	
	### INPUT
	case 'number':
	case 'url':
	case 'tel':
	case 'text':
	case 'password':
	case 'email':
	$default = CFGP_Options::get($input['name'], (isset($input['default']) ? $input['default'] : '')); ?>
	<input type="<?php echo esc_attr($input['type']); ?>" name="<?php
	  echo esc_attr(CFGP_NAME);
	?>[<?php
	  echo esc_attr($input['name']);
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
	<?php if(isset($input['info']) && !empty($input['info'])) : 
		?><br><div class="cfgp-field-description cfgp-field-description-<?php echo esc_attr($input['type']); ?>"><?php echo wp_kses_post($input['info']); ?></div><?php 
	endif; ?>
	<?php break;
	
	### TEXTAREA
	case 'textarea':
	$default = CFGP_Options::get($input['name'], (isset($input['default']) ? $input['default'] : '')); ?>
	<textarea type="<?php echo esc_attr($input['type']); ?>" name="<?php
	  echo esc_attr(CFGP_NAME);
	?>[<?php
	  echo esc_attr($input['name']);
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
	?>><?php echo wp_kses_post($default); ?></textarea>
	<?php if(isset($input['info']) && !empty($input['info'])) : 
		?><br><div class="cfgp-field-description cfgp-field-description-<?php echo esc_attr($input['type']); ?>"><?php echo wp_kses_post($input['info']); ?></div><?php 
	endif; ?>
	<?php break;
	
	
	### SELECT
	case 'select': $default = CFGP_Options::get($input['name'], (isset($input['default']) ? $input['default'] : '')); ?>
	<select name="<?php
	  echo esc_attr(CFGP_NAME);
	?>[<?php
	  echo esc_attr($input['name']);
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
    	<option value="<?php echo esc_attr($value); ?>"<?php echo ($default == $value ? ' selected' : ''); ?>><?php echo esc_html($label); ?></option>
    <?php endforeach; ?>
    </select>
	<?php if(isset($input['info']) && !empty($input['info'])) : 
		?><br><div class="cfgp-field-description cfgp-field-description-<?php echo esc_attr($input['type']); ?>"><?php echo wp_kses_post($input['info']); ?></div><?php 
	endif; ?>
	<?php break;
	
	
	### MULTIPLE
	case 'multiple': $default = CFGP_Options::get($input['name'], (isset($input['default']) ? $input['default'] : [])); ?>
	<select name="<?php
	  echo esc_attr(CFGP_NAME);
	?>[<?php
	  echo esc_attr($input['name']);
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
    	<option value="<?php echo esc_attr($value); ?>"<?php echo (in_array($value, $default) ? ' selected' : ''); ?>><?php echo esc_html($label); ?></option>
    <?php endforeach; ?>
    </select>
	<?php if(isset($input['info']) && !empty($input['info'])) : 
		?><br><div class="cfgp-field-description cfgp-field-description-<?php echo esc_attr($input['type']); ?>"><?php echo wp_kses_post($input['info']); ?></div><?php 
	endif; ?>
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
			
			<div class="inner-sidebar" id="<?php echo esc_attr(CFGP_NAME); ?>-settings-sidebar">
    			<div id="side-sortables" class="meta-box-sortables ui-sortable">
    				<?php do_action('cfgp/page/settings/sidebar'); ?>
    			</div>
    		</div>
			
    		<br class="clear">
    	</div>
    </div>
</div>
