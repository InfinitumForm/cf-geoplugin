<div class="wrap" id="admin-page-geoplugin">
    <h2><span class="fa fa-info-circle"></span> <?php _e("Credits & Info",CFGP_NAME); ?></h2>
    <hr>
	<?php
		$data = CF_GEO_D::curl_get("http://cfgeoplugin.com/team.json");
		$data = json_decode($data);
	//	var_dump($data);
	?>
	<div class="nav-tab-body">
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab nav-tab-active" href="#credits"><span class="fa fa-info-circle"></span> <?php _e('Credits',CFGP_NAME); ?></a>
			<?php if(isset($data->team)): ?><a class="nav-tab" href="#team-members"><span class="fa fa-braille"></span> <?php _e('Development Team',CFGP_NAME); ?></a><?php endif; ?>
			<?php if(isset($data->sponsors)): ?><a class="nav-tab" href="#sponsors"><span class="fa fa-dollar"></span> <?php _e('Sponsors',CFGP_NAME); ?></a><?php endif; ?>
		</h2>
		<div class="nav-tab-item nav-tab-item-active" id="credits">
			<?php include plugin_dir_path( __FILE__ ) . 'page-settings/settings-credits.php'; ?>
		</div>
		<?php if(isset($data->team)): ?>
		<div class="nav-tab-item" id="team-members">
			<p class="manage-menus"><?php _e("Meet our valuable team members who work hard to improve this plugin and its functionality. If you like us you also can be a part of it and support us with new idea and functionality. Let's made miracle together!", CFGP_NAME); ?><p>
			<?php
			$contributor=$developers=$translator=array();
			foreach($data->team as $member)
			{
				if($member->level == 1)
				{
					$developers[]=(object)array(
						'name' => '<a href="'.$member->url.'" target="_blank">'.$member->name.'</a>',
						'img' => '<img src="'.$member->img.'" alt="'.$member->name.'" id="team-image">',
						'email' => '<a href="mailto:'.$member->email.'">'.$member->email.'</a>',
						'desc' => $member->desc,
					); 
				}
				else if($member->level == 2)
				{
					$translator[]=(object)array(
						'name' => '<a href="'.$member->url.'" target="_blank">'.$member->name.'</a>',
						'img' => '<img src="'.$member->img.'" alt="'.$member->name.'" id="team-image">',
						'email' => '<a href="mailto:'.$member->email.'">'.$member->email.'</a>',
						'desc' => $member->desc,
					); 
				}
				else if($member->level == 3)
				{
					$contributor[]=(object)array(
						'name' => '<a href="'.$member->url.'" target="_blank">'.$member->name.'</a>',
						'img' => '<img src="'.$member->img.'" alt="'.$member->name.'" id="team-image">',
						'email' => '<a href="mailto:'.$member->email.'">'.$member->email.'</a>',
						'desc' => $member->desc,
					); 
				}
			}
			?>
			
			<?php
				if(count($developers)>0)
				{
					echo '<h3 id="team-member-title"><span class="fa fa-code"></span> '.__("Development Team", CFGP_NAME).'<h3><hr>';
					foreach($developers as $c)
					{
						echo sprintf('<blockquote id="team">%s<h4>%s</h4><p>%s</p><p>%s</p></blockquote>',$c->img,$c->name,$c->desc,$c->email);
					}
				}
			?>
			<?php
				if(count($translator)>0)
				{
					echo '<h3 id="team-member-title"><span class="fa fa-language"></span> '.__("Translators", CFGP_NAME).'<h3><hr>';
					foreach($translator as $c)
					{
						echo sprintf('<blockquote id="team">%s<h4>%s</h4><p>%s</p><p>%s</p></blockquote>',$c->img,$c->name,$c->desc,$c->email);
					}
				}
			?>
			<?php
				if(count($contributor)>0)
				{
					echo '<h3 id="team-member-title"><span class="fa fa-gamepad"></span> '.__("Contributors", CFGP_NAME).'<h3><hr>';
					foreach($contributor as $c)
					{
						echo sprintf('<blockquote id="team">%s<h4>%s</h4><p>%s</p><p>%s</p></blockquote>',$c->img,$c->name,$c->desc,$c->email);
					}
				}
			?>
		</div>
		<?php endif; ?>
		<?php if(isset($data->sponsors)): ?>
		<div class="nav-tab-item" id="sponsors">
			<h2><?php echo sprintf(__("Meet our valuable sponsors who support us and help our development team. If you like to be our sponsor and your logo appears here please contact us %s", CFGP_NAME),'<a href="mailto:creativform@gmail.com">creativform@gmail.com</a>'); ?></h2>
			<?php
				foreach($data->sponsors as $c)
				{
					echo sprintf('<a href="%s" target="_blank" id="sponsor" title="%s"><img src="%s" alt="%s"></a>', $c->url,$c->title,$c->img,$c->title);
				}
			?>
		</div>
		<?php endif; ?>
	</div>
<?php include plugin_dir_path( __FILE__ ) . 'page-settings/settings-donation.php'; ?>
</div>