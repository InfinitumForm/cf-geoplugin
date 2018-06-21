<?php if(isset($data->team)): ?>
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
	<?php include plugin_dir_path( __FILE__ ) . 'settings-donation.php'; ?>
<?php endif;