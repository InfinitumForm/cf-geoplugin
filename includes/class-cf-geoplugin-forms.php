<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Main Plugin Class
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if(!class_exists('CF_Geoplugin_Form')) :
class CF_Geoplugin_Form extends CF_Geoplugin_Global
{
	/* Array of the prepared fields */
	private $form_fields = array();
	/* Tabindex */
	public $tabindex = 1;

	function input( $arguments ){
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		$attr = apply_filters( 'cf_geeoplugin_form_input_arguments', array_merge(
			array(
				'label'				=> 'Input ' . $this->tabindex,
				'name'				=> '',
				'type'				=> 'text',
				'value'				=> '',
				'id'				=> 'input-field-' . $this->tabindex,
				'label_class' 		=> '',
				'input_class' 		=> '',
				'container_class' 	=> '',
				'html' 				=> '',
				'info' 				=> '',
				'disabled'			=> false,
				'readonly'			=> false,
				'attr'				=> array(),
				'license'			=> false,
				'license_message'	=> ''
			),
			$arguments
		), $arguments, $this);
		ob_start(); ?>
		<div class="form-group row align-items-center<?php 
			if( !empty($attr['container_class']) ) echo ' ' . esc_attr($attr['container_class']);
		?>">
            <label for="<?php 
				echo esc_attr($attr['id']); 
			?>" class="col-md-4 col-lg-3 col-form-label<?php 
				if( !empty($attr['label_class']) ) echo ' ' . esc_attr($attr['label_class']);
			?>"><?php 
				echo $attr['label'];
				if( !empty($attr['info'])) echo '<i class="fa fa-question-circle fa-cfgeo-help" data-toggle="tooltip" data-placement="top" title="' . esc_attr($attr['info']) . '"></i>';
			?></label>
            <div class="col-md-8 col-lg-9 cfgp-autosave">
            <?php if($attr['license'] && is_numeric($attr['license']) && self::access_level($CF_GEOPLUGIN_OPTIONS['license_sku']) < $attr['license']): ?>
            	<strong class="text-danger"><?php 
					echo esc_attr($attr['license_message']); 
				?></strong>
            <?php else: ?>
                <input tabindex="<?php echo $this->tabindex; ?>" type="<?php 
					echo esc_attr($attr['type']); 
				?>" class="form-control-plaintext<?php 
					if( !empty($attr['input_class']) ) echo ' ' . esc_attr($attr['input_class']);
					if( isset($attr['disabled']) && $attr['disabled'] === true ) echo ' disabled';
					if( isset($attr['readonly']) && $attr['readonly'] === true ) echo ' readonly';
				?>" name="<?php 
					echo esc_attr($attr['name']); 
				?>" id="<?php 
					echo esc_attr($attr['id']); 
				?>" value="<?php 
					echo esc_attr($this->post($attr['name'], 'string', $attr['value']));
				?>"<?php 
					if( isset($attr['attr']) && is_array($attr['attr']) && count($attr['attr']) > 0 ) {
						foreach($attr['attr'] as $a => $v) printf(' %1$s="%2$s"', $a, esc_attr($v));
					}
					if( isset($attr['disabled']) && $attr['disabled'] === true ) echo ' disabled';
					if( isset($attr['readonly']) && $attr['readonly'] === true ) echo ' readonly';
				?>><?php
					if( !empty($attr['html'])) echo $attr['html'];
				?>
            <?php endif; ?>
            </div>
        </div>
		<?php $this->form_fields[$attr['name']]= ob_get_clean();
		++$this->tabindex;
		$attr = NULL;
	}
	
	function radio( $arguments ){
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		$attr = apply_filters( 'cf_geeoplugin_form_radio_arguments', array_merge(
			array(
				'label'				=> 'Input ' . $this->tabindex,
				'label_class' 		=> '',
				'container_class' 	=> '',
				'name'				=> '',
				'default'			=> '',
				'html'				=> '',
				'info' 				=> '',
				'license'			=> false,
				'license_message'	=> ''
				/* DEMO *
				array(
					'text'				=> 'Radio 0',
					'value'				=> '',
					'id'				=> 'input-field-' . $this->tabindex,
					'input_class' 		=> '',
					'disabled'			=> false,
					'readonly'			=> false,
					'attr'				=> array()
				)
				*/
			),
			$arguments
		), $arguments, $this);
		ob_start(); ?>
		<div class="form-group row align-items-center<?php 
			if( !empty($attr['container_class']) ) echo ' ' . esc_attr($attr['container_class']);
		?>">
            <label for="<?php 
				echo isset($attr[0]['id']) ? esc_attr($attr[0]['id']) : ''; 
			?>" class="col-md-4 col-lg-3 col-form-label<?php 
				if( !empty($attr['label_class']) ) echo ' ' . esc_attr($attr['label_class']);
			?>"><?php 
				echo $attr['label'];
				if( !empty($attr['info'])) echo '<i class="fa fa-question-circle fa-cfgeo-help" data-toggle="tooltip" data-placement="top" title="' . esc_attr($attr['info']) . '"></i>';
			?></label>
            <div class="col-md-8 col-lg-9">
            <?php if($attr['license'] && is_numeric($attr['license']) && self::access_level($CF_GEOPLUGIN_OPTIONS['license_sku']) < $attr['license']): ?>
            	<strong class="text-danger"><?php 
					echo esc_attr($attr['license_message']); 
				?></strong>
            <?php else: ?>
            	<?php foreach($attr as $key => $obj) : if(is_numeric($key)) : ?>
                    <div class="custom-control custom-radio custom-control-inline cfgp-autosave">
                    	<input tabindex="<?php echo $this->tabindex; ?>" type="radio" class="custom-control-input<?php 
							if( !empty($obj['input_class']) ) echo ' ' . $obj['input_class'];
							if( isset($obj['disabled']) && $obj['disabled'] === true ) echo ' disabled';
							if( isset($obj['readonly']) && $obj['readonly'] === true ) echo ' readonly';
						?>" name="<?php 
							echo esc_attr($attr['name']); 
						?>" id="<?php 
							echo esc_attr($obj['id']); 
						?>" value="<?php 
							echo esc_attr($obj['value']); 
						?>"<?php 
							if(isset($obj['attr']) && is_array($obj['attr']) && count($obj['attr']) > 0 ) {
								foreach($obj['attr'] as $a => $v) sprintf(' %1$s="%2$s"', $a, esc_attr($v));
							};
							if( $obj['value'] == $this->post($attr['name'], 'string', (string)$attr['default']) ) echo ' checked';
							if( isset($obj['disabled']) && $obj['disabled'] === true ) echo ' disabled';
							if( isset($obj['readonly']) && $obj['readonly'] === true ) echo ' readonly';
						?>>
                        <label class="custom-control-label" for="<?php 
							echo $obj['id']; 
						?>"><?php 
							echo $obj['text']; 
						?></label>
                    </div>
                <?php ++$this->tabindex; endif; endforeach; ?>
                <?php 
					if( !empty($attr['html'])) echo $attr['html'];
				?>
            <?php endif; ?>
            </div>
        </div>
		<?php $this->form_fields[$attr['name']]= ob_get_clean();
		$attr = NULL;
	}
	
	function checkbox( $arguments ){
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		$attr = apply_filters( 'cf_geeoplugin_form_checkbox_arguments', array_merge(
			array(
				'label'				=> 'Input ' . $this->tabindex,
				'label_class' 		=> '',
				'container_class' 	=> '',
				'html'				=> '',
				'info' 				=> '',
				'license'			=> false,
				'license_message'	=> ''
				/* DEMO *
				array(
					'text'				=> 'Checkbox 0',
					'default'			=> '',
					'value'				=> '',
					'id'				=> 'input-field-' . $this->tabindex,
					'input_class' 		=> '',
					'disabled'			=> false,
					'readonly'			=> false,
					'name'				=> '',
					'attr'				=> array()
				)
				*/
			),
			$arguments
		), $arguments, $this);
		ob_start(); ?>
		<div class="form-group row align-items-center<?php 
			if( !empty($attr['container_class']) ) echo ' ' . esc_attr($attr['container_class']);
		?>">
            <label for="<?php 
				echo isset($attr[0]['id']) ? esc_attr($attr[0]['id']) : ''; 
			?>" class="col-md-4 col-lg-3 col-form-label<?php 
				if( !empty($attr['label_class']) ) echo ' ' . esc_attr($attr['label_class']);
			?>"><?php 
				echo $attr['label'];
				if( !empty($attr['info'])) echo '<i class="fa fa-question-circle fa-cfgeo-help" data-toggle="tooltip" data-placement="top" title="' . esc_attr($attr['info']) . '"></i>';
			?></label>
            <div class="col-md-8 col-lg-9">
           	<?php if($attr['license'] && is_numeric($attr['license']) && self::access_level($CF_GEOPLUGIN_OPTIONS['license_sku']) < $attr['license']): ?>
            	<strong class="text-danger"><?php 
					echo esc_attr($attr['license_message']); 
				?></strong>
            <?php else: ?>
            	<?php foreach($attr as $key => $obj) : if(is_numeric($key)) : ?>
                    <div class="custom-control custom-checkbox custom-control-inline cfgp-autosave">
                    	<input tabindex="<?php echo $this->tabindex; ?>" type="checkbox" class="custom-control-input<?php 
							if( !empty($obj['input_class']) ) echo ' ' . $obj['input_class'];
							if( isset($obj['disabled']) && $obj['disabled'] === true ) echo ' disabled';
							if( isset($obj['readonly']) && $obj['readonly'] === true ) echo ' readonly';
						?>" name="<?php 
							echo esc_attr($obj['name']); 
						?>" id="<?php 
							echo esc_attr($obj['id']); 
						?>" value="<?php 
							echo esc_attr($obj['value']); 
						?>"<?php 
							if(isset($obj['attr']) && is_array($obj['attr']) && count($obj['attr']) > 0 ) {
								foreach($obj['attr'] as $a => $v) sprintf(' %1$s="%2$s"', $a, esc_attr($v));
							};
							if( !is_array( $obj['default'] ) && $obj['value'] == $this->post($obj['name'], 'string', (string)$obj['default']) || $obj['value'] == $obj['default'] ) echo ' checked';
							elseif( is_array( $obj['default'] ) && in_array( $obj['value'], $obj['default'] ) ) echo ' checked';

							if( isset($obj['disabled']) && $obj['disabled'] === true ) echo ' disabled';
							if( isset($obj['readonly']) && $obj['readonly'] === true ) echo ' readonly';
						?>>
                        <label class="custom-control-label" for="<?php 
							echo $obj['id']; 
						?>"><?php 
							echo $obj['text']; 
						?></label>
                    </div>
                <?php ++$this->tabindex; endif; endforeach; ?>
                <?php
					if( !empty($attr['html'])) echo $attr['html'];
				?>
			<?php endif; ?>
            </div>
        </div>
		<?php $this->form_fields[$attr['label']]= ob_get_clean();
		$attr = NULL;
	}
	
	function select( $arguments ){
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		$attr = apply_filters( 'cf_geeoplugin_form_select_arguments', array_merge(
			array(
				'label'				=> 'Input ' . $this->tabindex,
				'label_class' 		=> '',
				'container_class' 	=> '',
				'name'				=> '',
				'default'			=> '',
				'html'				=> '',
				'info' 				=> '',
				'id'				=> 'select-field-' . $this->tabindex,
				'select_class' 		=> '',
				'disabled'			=> false,
				'readonly'			=> false,
				'attr'				=> array(),
				'license'			=> false,
				'license_message'	=> ''
				/* DEMO *
				array(
					'text'				=> '',
					'value'				=> ''
				)
				*/
			),
			$arguments
		), $arguments, $this);
		ob_start(); ?>
		<div class="form-group row align-items-center<?php 
			if( !empty($attr['container_class']) ) echo ' ' . esc_attr($attr['container_class']);
		?>">
            <label for="<?php 
				echo isset($attr[0]['id']) ? esc_attr($attr[0]['id']) : ''; 
			?>" class="col-md-4 col-lg-3 col-form-label<?php 
				if( !empty($attr['label_class']) ) echo ' ' . esc_attr($attr['label_class']);
			?>"><?php 
				echo $attr['label'];
				if( !empty($attr['info'])) echo '<i class="fa fa-question-circle fa-cfgeo-help" data-toggle="tooltip" data-placement="top" title="' . esc_attr($attr['info']) . '"></i>';
			?></label>
            <div class="col-md-8 col-lg-9 cfgp-autosave">
            <?php if($attr['license'] && is_numeric($attr['license']) && self::access_level($CF_GEOPLUGIN_OPTIONS['license_sku']) < $attr['license']): ?>
            	<strong class="text-danger"><?php 
					echo esc_attr($attr['license_message']); 
				?></strong>
            <?php else: ?>
            	<select class="form-control<?php 
					if( !empty($attr['select_class']) ) echo ' ' . esc_attr($attr['select_class']);
					if( isset($attr['disabled']) && $attr['disabled'] === true ) echo ' disabled';
					if( isset($attr['readonly']) && $attr['readonly'] === true ) echo ' readonly';
				?>" name="<?php 
					echo esc_attr($attr['name']); 
				?>" id="<?php 
					echo esc_attr($attr['id']); 
				?>"<?php 
					if( isset($attr['attr']) && is_array($attr['attr']) && count($attr['attr']) > 0 ) {
						foreach($attr['attr'] as $a => $v) printf(' %1$s="%2$s"', $a, esc_attr($v));
					}
					if( isset($attr['disabled']) && $attr['disabled'] === true ) echo ' disabled';
					if( isset($attr['readonly']) && $attr['readonly'] === true ) echo ' readonly';
				?>>
            	<?php foreach($attr as $key => $obj) : if(is_numeric($key)) : ?>
                	<option value="<?php echo $obj['value']; ?>"<?php
                    	if($obj['value'] == $attr['default']) echo ' selected';
					?>><?php
						echo $obj['text'];
					?></option>
                <?php ++$this->tabindex; endif; endforeach; ?>
                </select>
                <?php
					if( !empty($attr['html'])) echo $attr['html'];
				?>
            <?php endif; ?>
            </div>
        </div>
		<?php $this->form_fields[$attr['name']] = ob_get_clean();
		$attr = NULL;
	}
	
	function textarea( $arguments ){
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		$attr = apply_filters( 'cf_geeoplugin_form_textarea_arguments', array_merge(
			array(
				'label'				=> 'Textarea ' . $this->tabindex,
				'name'				=> '',
				'value'				=> '',
				'cols'				=> false,
				'rows'				=> false,
				'id'				=> 'textarea-field-' . $this->tabindex,
				'label_class' 		=> '',
				'input_class' 		=> '',
				'container_class' 	=> '',
				'html' 				=> '',
				'info' 				=> '',
				'disabled'			=> false,
				'readonly'			=> false,
				'attr'				=> array(),
				'license'			=> false,
				'license_message'	=> ''
			),
			$arguments
		), $arguments, $this);
		ob_start(); ?>
		<div class="form-group row<?php 
			if( !empty($attr['container_class']) ) echo ' ' . esc_attr($attr['container_class']);
		?>">
        <label for="<?php 
				echo isset($attr[0]['id']) ? esc_attr($attr[0]['id']) : ''; 
			?>" class="col-md-4 col-lg-3 col-form-label<?php 
				if( !empty($attr['label_class']) ) echo ' ' . esc_attr($attr['label_class']);
			?>"><?php 
				echo $attr['label'];
				if( !empty($attr['info'])) echo '<i class="fa fa-question-circle fa-cfgeo-help" data-toggle="tooltip" data-placement="top" title="' . esc_attr($attr['info']) . '"></i>';
			?></label>
            <div class="col-md-8 col-lg-9 cfgp-autosave">
            <?php if($attr['license'] && is_numeric($attr['license']) && self::access_level($CF_GEOPLUGIN_OPTIONS['license_sku']) < $attr['license']): ?>
            	<strong class="text-danger"><?php 
					echo esc_attr($attr['license_message']); 
				?></strong>
            <?php else: ?>
            	<textarea tabindex="<?php echo $this->tabindex; ?>" class="form-control<?php 
					if( !empty($attr['input_class']) ) echo ' ' . esc_attr($attr['input_class']);
					if( isset($attr['disabled']) && $attr['disabled'] === true ) echo ' disabled';
					if( isset($attr['readonly']) && $attr['readonly'] === true ) echo ' readonly';
				?>" name="<?php 
					echo esc_attr($attr['name']); 
				?>" id="<?php 
					echo esc_attr($attr['id']); 
				?>"<?php
					if(!empty($attr['cols'])) echo ' cols="'.$attr['cols'].'"';
					if(!empty($attr['rows'])) echo ' rows="'.$attr['rows'].'"';
					if( isset($attr['attr']) && is_array($attr['attr']) && count($attr['attr']) > 0 ) {
						foreach($attr['attr'] as $a => $v) printf(' %1$s="%2$s"', $a, esc_attr($v));
					}
					if( isset($attr['disabled']) && $attr['disabled'] === true ) echo ' disabled';
					if( isset($attr['readonly']) && $attr['readonly'] === true ) echo ' readonly';
				?>><?php 
					echo $this->post($attr['name'], 'html', $attr['value']);
				?></textarea><?php
					if( !empty($attr['html'])) echo $attr['html'];
				?>
            <?php endif; ?>
            </div>
        </div>
		<?php $this->form_fields[$attr['name']] = ob_get_clean();
		$attr = NULL;
	}
	
	function html( $html ){
		$this->form_fields[]=$html;
	}
		
	function form( $arguments = array() ){
		$url = self::URL();
		$attr = array_merge(
			array(
				'method'	=> 'post',
				'action'	=> ''
			),
			$arguments
		);
		$attr = apply_filters( 'cf_geeoplugin_form_attributes', array_filter($attr), $this);
		
		$fields = array();
		foreach($attr as $a => $v){
			if(!is_numeric($a)) $fields[]= sprintf('%1$s="%2$s"', $a, esc_attr($v));
		}
		
		printf(
			'<form %1$s>%2$s</form>',
			join(' ', $fields),
			join("\r\n", apply_filters(
				'cf_geeoplugin_form_items',
				$this->form_fields,
				$this
			))
		);
		$this->tabindex = 1;
		$this->form_fields = array();
	}
}
endif;