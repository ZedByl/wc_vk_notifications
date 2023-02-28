<div class="wrap">
<h1>Настройка оповещений VK</h1>
<?php settings_errors(); ?>
<form method="post" action="options.php">
<?php 
	settings_fields('vk_options_group');
	do_settings_sections('vk_plugin');
	submit_button();
	?>
	</form>
</div>