<div class="wrap">
		
			<!-- Display Plugin Icon, Header, and Description -->
			<div class="icon32" id="icon-options-general"><br></div>
			<h2>Plugin Options Starter Kit</h2>
			<p>Below is a collection of sample controls you can use in your own Plugins. Or, you can analyse the code and learn how all the most common controls can be added to a Plugin options form. See the code for more details, it is fully commented.</p>

			<!-- Beginning of the Plugin Options Form -->
			<form method="post" action="options.php">
				<?php settings_fields('etsy_rhythm_plugin_options'); ?>
				<?php $options = get_option('etsy_rhythm_settings'); ?>

				 <table class="form-table">
			<h3><?php _e('General Settings', 'etsyshoprhythm');?></h3>
            
				<?php 	/* 
						*	API Key entry 
						*/ 
				?>
                <tr valign="top">
                    <th scope="row">
                        <label for="api_key"></label><?php _e('Etsy API Key', 'etsyshoprhythm'); ?>
                    </th>
                    <td>
                        <input id="etsy_rhythm_settings[api_key]" name="etsy_rhythm_settings[api_key]" type="text" size="25" value="<?php echo $options['api_key']; ?>" class="regular-text code" />
                            <?php /*if ( !is_wp_error( EtsyAPI::testAPIKey()) ) { */?>
                                <span id="etsy_shop_api_key_status" class="green" >Your API Key is valid</span>
                            <?php /*} elseif ( $options['api_key']  ) {*/ ?>
                                <span id="etsy_shop_api_key_status" class="red">You API Key is invalid</span>
                            <?php /* } */ ?>
                            <p class="description">
								<?php echo sprintf( __('You may get an Etsy API Key by <a href="%1$s">Creating a new Etsy App</a>', 'etsyshoprhythm' ), 'http://www.etsy.com/developers/register' ); ?>
							</p>
                    </td>
                 </tr>
 
				 <?php 	/* 
						*	Link to new window option 
						*/ 
				?>
                 <tr valign="top">
                     <th scope="row">
                         <label for="etsy_rhythm_settings[target_blank]"></label><?php _e('Link to new window', 'etsyshoprhythm'); ?>
					</th>
                    <td>
						<input id="etsy_rhythm_settings[target_blank]" name="etsy_rhythm_settings[target_blank]" type="checkbox" value="1" <?php if (isset($options['target_blank'])) { checked('1', $options['target_blank']); } ?> />
							<p class="description">
                               <?php echo __( 'If you want your links to open a page in a new window', 'etsyshoprhythm' ); ?>
                            </p>
                    </td>
                 </tr>
				 
				 <?php 	/* 
						* 	Cache Life
						*/ 
				?>
                 <tr valign="top">
                     <th scope="row">
                         <label for="etsy_rhythm_settings[cache_life]"></label><?php _e('Cache life', 'etsyshoprhythm'); ?>

					</th>
                    <td>
                        <input id="etsy_rhythm_settings[cache_life]" name="etsy_rhythm_settings[cache_life]" type="text" size="25" value="<?php echo $options['cache_life']; ?>" class="regular-text code" />
                            <p class="description">
                                <?php echo __( 'How long until the cache file updates. If you are unsure, leave this alone. Default is set at 6 hours or 21600 seconds.', 'etsyshoprhythm' ); ?>
                            </p>
                    </td>
                 </tr>
				 
				 
				 <?php /*
						* Cache Status
						*/
				?>
                 <tr valign="top">
                    <th scope="row"><?php _e('Cache Status', 'etsyshoprhythm'); ?></th>
                        <td>
                            <?php if ( $options['api_key'] )  { ?>
                            <table class="wp-list-table widefat fixed">
                                <thead>
                                    <tr>
                                        <th>Shop Section</th>
                                        <th>Filename</th>
                                        <th>Last update</th>
                                    </tr>
                                </thead>
                                <?php 
                                $files = glob( dirname( __FILE__ ).'/tmp/*.json' );
                                $time_zone = get_option('timezone_string');
                                date_default_timezone_set( $time_zone );
                                foreach ($files as $file) {
                                    $etsy_shop_section = explode( "-", substr( basename( $file ), 0, strpos( basename( $file ), '_cache.json' ) ) );
                                    $etsy_shop_section_info = $this->getShopSection($etsy_shop_section[0], $etsy_shop_section[1]);
                                    if ( !is_wp_error( $etsy_shop_section_info ) ) {
                                        echo '<tr><td>' . $etsy_shop_section[0] . ' / ' . $etsy_shop_section_info->results[0]->title . '</td><td>' . basename( $file ) . '</td><td>' .  date( "Y-m-d H:i:s", filemtime( $file ) ) . '</td></tr>';
                                    } else {
                                        echo '<tr><td>' . $etsy_shop_section[0] . ' / <span style="color:red;">Error on API Request</span>' . '</td><td>' . basename( $file ) . '</td><td>' .  date( "Y-m-d H:i:s", filemtime( $file ) ) . '</td></tr>';
                                    }
                                }
                                    ?></table><?php } else { _e('You must enter your Etsy API Key to view cache status!', 'etsyshoprhythm'); } ?>
                                <p class="description"><?php _e( '', 'etsyshoprhythm' ); ?></p>
                                </td>
                </tr>
				

				<?php 	/* 
						*	Reset Cache
						*/ 
				?>
                 <tr valign="top">
                     <th scope="row">
                         <label for="etsy_rhythm_settings[reset_cache]"></label><?php _e('Reset Cache', 'etsyshoprhythm'); ?>
					</th>
                    <td>
						<input id="etsy_rhythm_settings[reset_cache]" name="etsy_rhythm_settings[reset_cache]" type="checkbox" value="1" <?php if (isset($optons['reset_cache'])) { checked('0', $options['reset_cache']); } ?> />
							<p class="description">
                               <?php echo __( 'Reset Cache', 'etsyshoprhythm' ); ?>
                            </p>
                    </td>
                 </tr>
             </table>
             
             
             
             
             
             
             
             <table class="form-table">
             <h3><?php _e('Look & Feel', 'etsyshoprhythm');?></h3>    
				 <?php 	/* 
						*	Number of items to list option 
						*/ 
				?>
				 <tr valign="top">
                     <th scope="row">
                         <label for="etsy_rhythm_settings[quantity]"></label><?php _e('Number of items to list', 'etsyshoprhythm'); ?>
					</th>
                    <td>
                        <input id="etsy_rhythm_settings[quantity]" name="etsy_rhythm_settings[quantity]" type="text" size="25" value="<?php echo $options['quantity']; ?>" class="regular-text code" />
                            <p class="description">
                                <?php echo __( 'How many items to list ( 1 - 25 )', 'etsyshoprhythm' ); ?>
                            </p>
                    </td>
                 </tr>
                 <?php 	/* 
						*	Title Trimming
						*/ 
				?>
				 <tr valign="top">
                     <th scope="row">
                         <label for="etsy_rhythm_settings[title_length]"></label><?php _e('Length of Item Title ( in characters )', 'etsyshoprhythm'); ?>
					</th>
                    <td>
                        <input id="etsy_rhythm_settings[title_length]" name="etsy_rhythm_settings[title_length]" type="text" size="25" value="<?php echo $options['title_length']; ?>" class="regular-text code" />
                            <p class="description">
                                <?php echo __( '1-300', 'etsyshoprhythm' ); ?>
                            </p>
                    </td>
                 </tr>
                 
                <?php 	/* 
						*	User Rows
						*/ 
				?>
				 <tr valign="top">
                     <th scope="row">
                         <label for="etsy_rhythm_settings[user_rows]"></label><?php _e('Length of Rows', 'etsyshoprhythm'); ?>
					</th>
                    <td>
                        <input id="etsy_rhythm_settings[user_rows]" name="etsy_rhythm_settings[user_rows]" type="text" size="25" value="<?php echo $options['user_rows']; ?>" class="regular-text code"/>
                            <p class="description">
                                <?php echo __( 'Example: a setting of 1 will only display 1 item per row.', 'etsyshoprhythm' ); ?>
                            </p>
                    </td>
                 </tr>
                 
				<?php 	/* 
						*	Materials
						*/ 
				?>
				 <tr valign="top">
                     <th scope="row">
                         <label for="etsy_rhythm_settings[materials]"></label><?php _e('Display materials used in making item', 'etsyshoprhythm'); ?>
					</th>
                    <td>
                        <input id="etsy_rhythm_settings[materials]" name="etsy_rhythm_settings[materials]" type="checkbox" value="1" <?php if (isset($options['materials'])) { checked('1', $options['materials']); } ?> />
                            <p class="description">
                                <?php echo __( 'For instance if the creator used oil paint, this will show "Oil paints"', 'etsyshoprhythm' ); ?>
                            </p>
                    </td>
                 </tr>
                 
				<?php 	/* 
						*	Who Made
						*/ 
				?>
				 <tr valign="top">
                     <th scope="row">
                         <label for="etsy_rhythm_settings[who_made]"></label><?php _e('Display who made the item', 'etsyshoprhythm'); ?>
					</th>
                    <td>
                        <input id="etsy_rhythm_settings[who_made]" name="etsy_rhythm_settings[who_made]" type="checkbox" value="1" <?php if (isset($options['who_made'])) { checked('1', $options['who_made']); } ?> />
                            <p class="description">
                                <?php echo __( '', 'etsyshoprhythm' ); ?>
                            </p>
                    </td>
                 </tr>
                 
				<?php 	/* 
						*	When Made
						*/ 
				?>
				 <tr valign="top">
                     <th scope="row">
                         <label for="etsy_rhythm_settings[when_made]"></label><?php _e('Display when the item was made', 'etsyshoprhythm'); ?>
					</th>
                    <td>
                        <input id="etsy_rhythm_settings[when_made]" name="etsy_rhythm_settings[when_made]" type="checkbox" value="1" <?php if (isset($options['when_made'])) { checked('1', $options['when_made']); } ?> />
                            <p class="description">
                                <?php echo __( 'example: July 2002', 'etsyshoprhythm' ); ?>
                            </p>
                    </td>
                 </tr>
				 <?php 	/* 
						* 	Select Language
						*/ 
				?>
                 <tr valign="top">
                     <th scope="row">
                         <label for="etsy_rhythm_settings[language]"></label><?php _e('Language', 'etsyshoprhythm'); ?>
					</th>
                    <td>
                        <select id="etsy_rhythm_settings[language]" name='etsy_rhythm_settings[language]'>
							<option value="en" <?php selected('English', $options['language']);?>>English</option>
							<option value="de" <?php selected('German', $options['language']);?>>German</option>
							<option value="fr" <?php selected('French' , $options['language']);?>>French</option>
							<option value="it" <?php selected('Italian' , $options['language']);?>>Italian</option>
							<option value="ru" <?php selected('Russian' , $options['language']);?>>Russian</option>
							<option value="nl" <?php selected('Dutch' , $options['language']);?>>Dutch</option>
							<option value="es" <?php selected('Spanish' , $options['language']);?>>Spanish</option>
							<option value="pt" <?php selected('Portuguese' , $options['language']);?>>Portuguese</option>
						</select>
						
                            <p class="description">
                                <?php echo __( 'Select a language to view items in. Note that not all shops supply translations, so even though you select French, you may only get English.', 'etsyshoprhythm' );;?>
                            </p>
                    </td>
                 </tr>
        </table>
				<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			</form>



		</div>