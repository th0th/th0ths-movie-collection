<div class="wrap">
	<h2><?php _e("Plugin Options"); ?></h2>
	
	<?php if ( isset($this->msg_settings) ) { ?>
	<div id="setting-error-settings_updated" class="updated settings-error"> 
		<p><strong><?php echo $this->msg_settings; ?></strong></p>
	</div>
	<?php } ?>

	<form method="post">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label for="fields_to_display"><?php _e("Fields to display"); ?></label>
					</th>
					<td>
						<select multiple="multiple" size="10" name="fields_to_display[]">
							<?php foreach ( $this->imdb_fields as $field ) { ?>
							<option<?php if ( in_array($field, $current_options['fields_to_display']) ) { ?> selected="selected"<?php } ?>><?php echo $field; ?></option>
							<?php } ?>
						</select>
						<p class="description"><?php _e("Press and hold CTRL button to select multiple."); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php _e("Comments"); ?>
					</th>
					<td>
						<fieldset>
							<label for="allow_comments">
								<input id="allow_comments" type="checkbox" name="allow_comments" <?php if ( $current_options['allow_comments'] ) { ?>checked="checked" <?php } ?>/>
								Enable comments for movies
							</label>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<input type="submit" id="submit" class="button-primary" value="Save Changes" />
		</p>
	</form>
</div>