
<div class="wrap"> 


	<!-- Display Plugin Icon, Header, and Description -->
	<div class="icon32" id="icon-options-general"><br>
	</div>
	<h2><?php _e('Etsy Rhythm Options', 'etsyrhythm');?></h2>
	
<div class="left">	
	<!-- Beginning of the Plugin Options Form -->
	<form id="etsy-rhythm-form" method="post" action="options.php">
		<?php settings_fields('etsy_rhythm_plugin_options'); ?>
		<?php $options = get_option('etsy_rhythm_settings'); ?>
		<table class="form-table">
			<h3>
				<?php _e('General Settings', 'etsyrhythm');?>
			</h3>
			<?php 		/* 
						*	API Key entry 
						*/ 
				?>
			<tr valign="top">
				<th scope="row"> <label for="api_key"></label>
					<?php _e('Etsy API Key', 'etsyrhythm'); ?>
				</th>
				<td><input id="etsy_rhythm_settings[api_key]" name="etsy_rhythm_settings[api_key]" type="text" size="25" value="<?php echo $options['api_key']; ?>" class="regular-text green"/>					
					<?php $test = Etsy_Rhythm::testApiKey();
					if ( $test ) { /* A little unorthodox but it get's the job done for now */;?>
						<script type="text/javascript">
							document.getElementById('etsy_rhythm_settings[api_key]').style.border="2px solid green";
						</script>
					<?php } else { ;?>
						<script type="text/javascript">
							document.getElementById('etsy_rhythm_settings[api_key]').style.border="2px solid red";
						</script>
					<?php };?> 
					<p class="description"> <?php echo sprintf( __('You may get an Etsy API Key by <a href="%1$s">Creating a new Etsy App</a>', 'etsyrhythm' ), 'http://www.etsy.com/developers/register' ); ?> </p></td>
			</tr>
		
			<?php 		/* 
						* 	Cache Life
						*/ 
				?>
			<tr valign="top">
				<th scope="row"> <label for="etsy_rhythm_settings[cache_life]"></label>
					<?php _e('Cache life', 'etsyrhythm'); ?>
				</th>
				<td><input id="etsy_rhythm_settings[cache_life]" name="etsy_rhythm_settings[cache_life]" type="text" size="25" value="<?php echo $options['cache_life']; ?>" class="regular-text code" />
					<p class="description"> <?php echo __( 'How long until the cache file updates. If you are unsure, leave this alone. Default is set at 6 hours or 21600 seconds.', 'etsyrhythm' ); ?> </p></td>
			</tr>

			<?php 		/* 
						*	Reset Cache
						*/ 
				?>
			<tr valign="top">
				<th scope="row"> <label for="etsy_rhythm_settings[reset_cache]"></label>
					<?php _e('Reset Cache', 'etsyrhythm'); ?>
				</th>
				<td><input id="etsy_rhythm_settings[reset_cache]" name="etsy_rhythm_settings[reset_cache]" type="checkbox" value="1" <?php if (isset($optons['reset_cache'])) { checked('0', $options['reset_cache']); } ?> />
					<p class="description"> <?php echo __( 'Reset Cache', 'etsyrhythm' ); ?> </p></td>
			</tr>
		</table>
		
		
		
		<table class="form-table">
			<h3>
				<?php _e('Item Layout', 'etsyrhythm');?>
			</h3>
			<?php 		/* 
						*	Link to new window option 
						*/ 
				?>
			<tr valign="top">
				<th scope="row"> <label for="etsy_rhythm_settings[target_blank]"></label>
					<?php _e('Link to new window', 'etsyrhythm'); ?>
				</th>
				<td><input id="etsy_rhythm_settings[target_blank]" name="etsy_rhythm_settings[target_blank]" type="checkbox" value="1" <?php if (isset($options['target_blank'])) { checked('1', $options['target_blank']); } ?> />
					<p class="description"> <?php echo __( 'If you want your links to open a page in a new window', 'etsyrhythm' ); ?> </p></td>
			</tr>
			<?php 		/* 
						*	Number of items to list option 
						*/ 
				?>
			<tr valign="top">
				<th scope="row"> <label for="etsy_rhythm_settings[display_quantity]"></label>
					<?php _e('Number of items to list', 'etsyrhythm'); ?>
				</th>
				<td><input id="etsy_rhythm_settings[display_quantity]" name="etsy_rhythm_settings[display_quantity]" type="text" size="25" value="<?php echo $options['display_quantity']; ?>" class="regular-text code" />
					<p class="description"> <?php echo __( 'How many items to list ( Max 100 per page )', 'etsyrhythm' ); ?> </p></td>
			</tr>
			<?php 		/* 
						*	Title Trimming
						*/ 
				?>
			<tr valign="top">
				<th scope="row"> <label for="etsy_rhythm_settings[title_length]"></label>
					<?php _e('Length of Item Title ( in characters )', 'etsyrhythm'); ?>
				</th>
				<td><input id="etsy_rhythm_settings[title_length]" name="etsy_rhythm_settings[title_length]" type="text" size="25" value="<?php echo $options['title_length']; ?>" class="regular-text code" />
					<p class="description"> <?php echo __( '1-300', 'etsyrhythm' ); ?> </p></td>
			</tr>
			<?php 		/* 
						*	User Rows
						*/ 
				?>
			<tr valign="top">
				<th scope="row"> <label for="etsy_rhythm_settings[user_rows]"></label>
					<?php _e('Length of Rows', 'etsyrhythm'); ?>
				</th>
				<td><input id="etsy_rhythm_settings[user_rows]" name="etsy_rhythm_settings[user_rows]" type="text" size="25" value="<?php echo $options['user_rows']; ?>" class="regular-text code"/>
					<p class="description"> <?php echo __( 'Example: a setting of 1 will only display 1 item per row.', 'etsyrhythm' ); ?> </p></td>
			</tr>
		</table>
		
		
		<table class="form-table">
			<h3>
				<?php _e('Show Item Details', 'etsyrhythm' );?>
			</h3>
			<?php 		/* 
						*	Materials
						*/ 
				?>
			<tr valign="top">
				<th scope="row"> <label for="etsy_rhythm_settings[materials]"></label>
					<?php _e('Materials', 'etsyrhythm'); ?>
				</th>
				<td>
					<input id="etsy_rhythm_settings[materials]" name="etsy_rhythm_settings[materials]" type="checkbox" value="1" <?php if (isset($options['materials'])) { checked('1', $options['materials']); } ?> />
				</td>
			</tr>
			<?php 		/* 
						*	Who Made
						*/ 
				?>
			<tr valign="top">
				<th scope="row"> <label for="etsy_rhythm_settings[who_made]"></label>
					<?php _e('Maker', 'etsyrhythm'); ?>
				</th>
				<td>
					<input id="etsy_rhythm_settings[who_made]" name="etsy_rhythm_settings[who_made]" type="checkbox" value="1" <?php if (isset($options['who_made'])) { checked('1', $options['who_made']); } ?> />
				</td>
			</tr>
			<?php 		/* 
						*	When Made
						*/ 
				?>
			<tr valign="top">
				<th scope="row"> <label for="etsy_rhythm_settings[when_made]"></label>
					<?php _e('Date Made', 'etsyrhythm'); ?>
				</th>
				<td>
					<input id="etsy_rhythm_settings[when_made]" name="etsy_rhythm_settings[when_made]" type="checkbox" value="1" <?php if (isset($options['when_made'])) { checked('1', $options['when_made']); } ?> />
				</td>
			</tr>
			<?php 		/* 
						* 	Select Language
						*
				
			<!-- <tr valign="top">
				<th scope="row"> <label for="etsy_rhythm_settings[language]"></label>
					<?php _e('Language', 'etsyrhythm'); ?>
				</th>
				<td><select id="etsy_rhythm_settings[language]" name='etsy_rhythm_settings[language]'>
						<option value="en" <?php selected('English', $options['language']);?>>English</option>
						<option value="de" <?php selected('German', $options['language']);?>>German</option>
						<option value="fr" <?php selected('French' , $options['language']);?>>French</option>
						<option value="it" <?php selected('Italian' , $options['language']);?>>Italian</option>
						<option value="ru" <?php selected('Russian' , $options['language']);?>>Russian</option>
						<option value="nl" <?php selected('Dutch' , $options['language']);?>>Dutch</option>
						<option value="es" <?php selected('Spanish' , $options['language']);?>>Spanish</option>
						<option value="pt" <?php selected('Portuguese' , $options['language']);?>>Portuguese</option>
					</select>
					<p class="description"> <?php echo __( 'Select a language to view items in. Note that not all shops supply translations, so even though you select French, you may only get English.', 'etsyrhythm' );;?> </p></td>
			</tr> */ ?>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>
<div class="right">
	<h2><?php _e("Instructions", "etsyrhythm");?></h2>
	<p>When you sign up for an API key it is required that you add an item and open your store for it
	 to properly function. In some cases it can take a few minutes to a few hours for the API key 
	 to actually start working. Try and have some patience with Etsy and don't make multiple apps 
	 thinking your API key isn't working. </p>
	
	
	<p>The cache is a file in which your shop items are stored, in hopes of fewer http requests 
	and faster loading. It is set to overwrite the previous file every six hours. If you add an item
	 to the shop or change settings and those settings are not reflected on your WordPress page,
	  try resetting the cache.</p>
	  
	  <p>To use Etsy Rhythm, use the shortcode <pre><code>[etsy-rhythm]</code></pre> followed by three parameters.
	  <pre><code>shop_id=</code></pre>First is your shop id, which is the name of your shop.
	  <pre><code>shop_section=</code></pre>This is an optional parameter in which you can specify which section of your shop the plug-in will show.
	  <pre><code>quantity=</code></pre>Also an optional parameter, this number is how many items the plug-in will retrieve.
	  All together the shortcode will look:
	  <pre><code>[etsy-rhythm shop_id=name shop_section=wood quantity=5]</code></pre>
	  <p>Instead of using a shortcode, you can also place the listings anywhere in your blog using the WordPress
	  do_shortcode() function. Be sure to add this inside a ?php tag.</p>
	  
	  <pre><code>echo do_shortcode( ' [etsy-rhythm shop_id=shopname shop_section=optional quantity=5] ' )</code></pre>
	  
</div>
</div>
