<?php
/*
    Plugin Name: CF7 Multi Upload File
    Description: Multi Upload File for Contact Form 7
    Author: Duid from kwork
    Author URI: https://kwork.ru/user/duid
    Version: 1.0.2
*/

	if(!class_exists('duidCF7MUPL'))
	{
		class duidCF7MUPL
		{
			protected static $single_instance	= null;

			public static function get_instance()
			{
				if(null === self::$single_instance)
				{
					self::$single_instance = new self();
				}
				return self::$single_instance;
			}

			function __construct()
			{
				$this->plugin_name	= plugin_basename(__FILE__);
				$this->plugin_url	= trailingslashit(WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__)));


				add_action('plugins_loaded', array(&$this, 'load_text_domain'));
				add_filter('init', array(&$this, 'first_init'), 0);
			}

			function load_text_domain()
			{
				load_plugin_textdomain( 'duidCF7MUPL', false, basename( dirname( __FILE__ ) ) . '/languages' );
			}

			function first_init()
			{

				add_action('wpcf7_admin_init', 		array(&$this, 'duid_upload_cf7_add_tag_generator'), 50);
				add_action('wpcf7_init',		array(&$this, 'duid_cf7_upload_add_form_tag_file'));

				add_action('wp_enqueue_scripts', 	array(&$this, 'theme_add_bootstrap'));
				add_action('wpcf7_enqueue_scripts',	array(&$this, 'duid_cf7_scripts'));
				add_filter('wpcf7_form_enctype', 	array(&$this, 'duid_cf7_multifile_form_enctype_filter'));
				add_filter('wpcf7_mail_components', 	array(&$this, 'duid_cf7_mail_components'));

				add_filter('wpcf7_validate_dmfile', 	array(&$this, 'duid_cf7_multifile_validation_filter'), 10, 2);
				add_filter('wpcf7_validate_dmfile*',	array(&$this, 'duid_cf7_multifile_validation_filter'), 10, 2);

			}

			function duid_upload_cf7_add_tag_generator()
			{
				$tag_generator = WPCF7_TagGenerator::get_instance();
				$tag_generator->add('upload-file', __('duid file form upload', 'duid-upload-cf7'), array(&$this, 'duid_upload_cf7_tag_generator_file'));
			}

			function duid_upload_cf7_tag_generator_file($contact_form, $args = '')
			{
				$args = wp_parse_args($args, array());
				$type = 'dmfile';

				$description = __("Generate a form-tag for a file uploading field. For more details, see %s.", 'contact-form-7');
				$desc_link = wpcf7_link(__('https://contactform7.com/file-uploading-and-attachment/', 'contact-form-7'), __('File Uploading and Attachment', 'contact-form-7'));
?>
					<div class="control-box">
						<fieldset>
							<legend><?php echo sprintf(esc_html($description), $desc_link); ?></legend>
							<table class="form-table">
								<tbody>
									<tr>
										<th scope="row"><?php echo esc_html(__('Field type', 'contact-form-7')); ?></th>
										<td>
											<fieldset>
												<legend class="screen-reader-text"><?php echo esc_html(__('Field type', 'contact-form-7')); ?></legend>
												<label><input type="checkbox" name="required" /> <?php echo esc_html(__('Required field', 'contact-form-7')); ?></label>
											</fieldset>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="<?php echo esc_attr($args['content'] . '-name'); ?>"><?php echo esc_html(__('Name', 'contact-form-7')); ?></label></th>
										<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr($args['content'] . '-name'); ?>" /></td>
									</tr>
									<tr>
										<th scope="row"><label for="<?php echo esc_attr($args['content'] . '-filetypes'); ?>"><?php echo esc_html(__('Acceptable file types', 'contact-form-7')); ?></label></th>
										<td><input type="text" name="filetypes" class="filetype oneline option" placeholder="jpeg|png|jpg|gif" id="<?php echo esc_attr($args['content'] . '-filetypes'); ?>" /></td>
									</tr>
								</tbody>
							</table>
						</fieldset>
					</div>

					<div class="insert-box">
						<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />
						<div class="submitbox">
							<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr(__('Insert Tag', 'contact-form-7')); ?>" />
						</div>
						<br class="clear" />
						<p class="description mail-tag">
							<label for="<?php echo esc_attr($args['content'] . '-mailtag'); ?>"><?php echo sprintf(esc_html(__("To attach the file uploaded through this field to mail, you need to insert the corresponding mail-tag (%s) into the File Attachments field on the Mail tab.", 'contact-form-7')), '<strong><span class="mail-tag"></span></strong>'); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr($args['content'] . '-mailtag'); ?>" /></label>
						</p>
					</div>
<?php

			}

			function duid_cf7_upload_add_form_tag_file()
			{
				wpcf7_add_form_tag(array('dmfile', 'dmfile*'), array(&$this, 'duid_cf7_upload_form_tag_handler'), array('name-attr' => true));
			}

			function duid_cf7_upload_form_tag_handler($tag)
			{
				if(empty($tag->name))
				{
					return '';
				}

				$validation_error = wpcf7_get_validation_error($tag->name);
				$class = wpcf7_form_controls_class('duid-file d-none');

				if($validation_error)
				{
					$class .= ' wpcf7-not-valid';
				}

				$atts = array();

				$atts['tabindex'] = $tag->get_option('tabindex', 'signed_int', true);

				if($tag->is_required())
				{
					$atts['aria-required'] = 'true';
				}

				$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

				$atts['type'] = 'file';
				$atts['name'] = $tag->name."[]";
				$atts['data-type'] = $tag->get_option('filetypes','', true);
				$atts['data-limit'] = $tag->get_option('limit','', true);
				$atts['data-max'] = $tag->get_option('max-file','', true);

				$atts = wpcf7_format_atts($atts);

				return sprintf('<span class="wpcf7-form-control-wrap %1$s">
				<div>
				<div class="input-group mb-3">
				  <div class="custom-file">
				    <input type="file" class="custom-file-input" id="inputGroupFile02" %2$s />
				    <label class="custom-file-label" for="inputGroupFile02" aria-describedby="inputGroupFileAddon02">'.__('Attach a file', 'duidCF7MUPL').'</label>
				  </div>
				</div>
				</div>%3$s</span>
				<div><button class="form__add-file" type="button">+ '.__('Add more files', 'duidCF7MUPL').'</button></div>
				',	sanitize_html_class($tag->name), $atts, $validation_error);
			}

			function theme_add_bootstrap()
			{
				wp_enqueue_style('multupl-css', $this->plugin_url . 'inc/multupl.css');
			}

			function duid_cf7_scripts()
			{
				wp_enqueue_script('cf7-js', $this->plugin_url . 'inc/cf7-js.js', array('jquery'));
			}

			function duid_cf7_multifile_form_enctype_filter($enctype)
			{
				if(!function_exists('wpcf7_version'))
				{
					require_once '/includes/functions.php';
				}

				$result = wpcf7_version('');
				$wpcf7_version = (int)preg_replace('/[.]/', '', $result);

				if($wpcf7_version < 100)
				{
					$wpcf7_version = $wpcf7_version * 10;
				}

				if($wpcf7_version > 460)
				{
					$multipart = (bool) wpcf7_scan_form_tags(array('type' => array('dmfile', 'dmfile*')));
				}else 	$multipart = (bool) wpcf7_scan_shortcode(array('type' => array('dmfile', 'dmfile*')));

				if($multipart) {
					$enctype = 'multipart/form-data';
				}

				return $enctype;
			}

			function duid_cf7_mail_components($components)
			{
				global $CV_file;
			        $components['attachments'] = $CV_file;
			        return $components;
			}

			function duid_cf7_multifile_validation_filter($result, $tag)
			{
				global $CV_file;

				$tag = new WPCF7_Shortcode($tag);

				$name = $tag->name;
				$id = $tag->get_id_option();
				$uniqid = uniqid();

				$original_files_array = isset($_FILES[$name]) ? esc_html($_FILES[$name]) : null;

				if($original_files_array === null)
				{
					return $result;
				}

				$total = count($_FILES[$name]['name']);

				$files = array();
				$new_files = array();

				for ($i=0; $i<$total; $i++)
				{
					$files[] = array(
						'name'      => $original_files_array['name'][$i],
						'type'      => $original_files_array['type'][$i],
						'tmp_name'  => $original_files_array['tmp_name'][$i],
						'error'     => $original_files_array['error'][$i],
						'size'      => $original_files_array['size'][$i]
					);
				}

				foreach($files as $file)
				{
					if($file['error'] && UPLOAD_ERR_NO_FILE != $file['error'])
					{
						$result->invalidate($tag, ('upload_failed_php_error'));
						$this->dmultifile_remove($new_files);
						return $result;
					}

					if(empty($file['tmp_name']) && $tag->is_required())
					{
						$result->invalidate($tag, ('invalid_required'));
						return $result;
					}

					if(!is_uploaded_file($file['tmp_name']))
					{
						return $result;
					}

					$allowed_file_types = array();

					if($file_types_a = $tag->get_option('filetypes'))
					{
						foreach($file_types_a as $file_types)
						{
							$file_types = explode('|', $file_types);

							foreach($file_types as $file_type)
							{
								$file_type = trim($file_type, '.');
								$file_type = str_replace(array('.', '+', '*', '?'),
									array('\.', '\+', '\*', '\?'), $file_type);
									$allowed_file_types[] = $file_type;
							}
						}
					}

					$allowed_file_types = array_unique($allowed_file_types);
					$file_type_pattern = implode('|', $allowed_file_types);

					$allowed_size = apply_filters('cf7_multifile_max_size', 10048576);

					if($file_size_a = $tag->get_option('limit'))
					{
						$limit_pattern = '/^([1-9][0-9]*)([kKmM]?[bB])?$/';

						foreach($file_size_a as $file_size)
						{
        						if(preg_match($limit_pattern, $file_size, $matches))
							{
								$allowed_size = (int) $matches[1];

								if(!empty($matches[2]))
								{
									$kbmb = strtolower($matches[2]);

									if('kb' == $kbmb)
										$allowed_size *= 1024;
									elseif('mb' == $kbmb)
										$allowed_size *= 1024 * 1024;
								}

								break;
							}
						}
					}

					if('' == $file_type_pattern)
					{
						$file_type_pattern = 'jpg|jpeg|png|gif|pdf|doc|docx|ppt|pptx|odt|avi|ogg|m4a|mov|mp3|mp4|mpg|wav|wmv';

					}

					$file_type_pattern = trim($file_type_pattern, '|');
					$file_type_pattern = '(' . $file_type_pattern . ')';
					$file_type_pattern = '/\.' . $file_type_pattern . '$/i';

					if(!preg_match($file_type_pattern, $file['name']))
					{
						$result->invalidate($tag,  'upload_file_type_invalid');
						$this->dmultifile_remove($new_files);
						return $result;
					}

					if($file['size'] > $allowed_size)
					{
						$result->invalidate($tag,  'upload_file_too_large' );
						$this->dmultifile_remove($new_files);
						return $result;
					}

					wpcf7_init_uploads();
					$uploads_dir = wpcf7_upload_tmp_dir();
					$uploads_dir = wpcf7_maybe_add_random_dir($uploads_dir);

					$filename = $file['name'];
					$filename = wpcf7_canonicalize($filename);
					$filename = sanitize_file_name($filename);
					$filename = wpcf7_antiscript_file_name($filename);
					$filename = wp_unique_filename($uploads_dir, $filename);

					$new_file = trailingslashit($uploads_dir) . $filename;

					if(false === @move_uploaded_file($file['tmp_name'], $new_file))
					{
						$result->invalidate($tag, ('upload_failed'));
						$this->dmultifile_remove($new_files);
						return $result;
					}

					$new_files[] = $new_file;

					@chmod($new_file, 0400);
				}

				$valid_files = array();

				if(is_array($new_files))
				{
					foreach($new_files as $file)
					{
						if(file_exists($file))
						{
							$CV_file[] = $file;
						}
					}
				}

				return $result;
			}

			function dmultifile_remove($new_files)
			{
				if(!empty($new_files))
				{
					foreach($new_files as $to_delete)
					{
						@unlink($to_delete);
						@rmdir(dirname($to_delete));
					}
				}
			}
		}

		duidCF7MUPL::get_instance();
	}