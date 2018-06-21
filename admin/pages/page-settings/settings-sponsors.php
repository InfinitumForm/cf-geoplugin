<?php if(isset($data->sponsors)): ?>
<div id="sponsors">
	<h2><?php echo sprintf(__("Meet our valuable sponsors who support us or help our development team. If you like to be our sponsor and your logo appears here please contact us %s", CFGP_NAME),'<a href="mailto:creativform@gmail.com">creativform@gmail.com</a>'); ?></h2>
	<?php
		foreach($data->sponsors as $c)
		{
			echo sprintf('<a href="%s" target="_blank" id="sponsor" title="%s"><img src="%s" alt="%s"></a>', $c->url,$c->title,$c->img,$c->title);
		}
	?>
</div>
<?php endif;