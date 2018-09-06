<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('lgnd_acf_field_csv_to_json') ) :


class lgnd_acf_field_csv_to_json extends acf_field {
	
	
	function __construct( $settings ) {
		
		$this->name = 'csv_to_json';

		$this->label = __('CSV to JSON', 'acf-csv-to-json');
		
		$this->category = 'content';
		
		$this->defaults = array(
			'min_size'		=> 0,
			'max_size'		=> 2
		);
		
		$this->settings = $settings;
		
		add_action('acf/save_post', array($this, 'save_post'), 5);
		add_action('post_edit_form_tag', array($this, 'post_edit_form_tag'));

		// do not delete!
    	parent::__construct();
    	
	}
	
	function post_edit_form_tag()
	{
			echo ' enctype="multipart/form-data"';
	}

	function save_post( $post_id )
	{
			// validate
			if( !isset($_FILES['fields']['error']) || empty($_FILES['fields']['error']) )
			{
					return;
			}
			
			
			// build $_POST data
			$this->build_post_data( $_POST['fields'], $_FILES['fields'] );
			
			
			// unset $_FILES
			unset( $_FILES['fields'] );
	}

	public function input_admin_enqueue_scripts()
	{
		$url = $this->settings['url'];
		$version = $this->settings['version'];
		
		wp_enqueue_media();
		wp_register_script( 'acf-input-csv-to-json', "{$url}assets/js/input.js", array('acf-input'), $version );
		wp_enqueue_script('acf-input-csv-to-json');
	}
	
	function render_field_settings( $field ) {

		$clear = array(
			'min_size',
			'max_size'
		);

		foreach( $clear as $k ) {
			if( empty($field[$k]) ) {				
				$field[$k] = '';
			}
		}

		// min
		acf_render_field_setting( $field, array(
			'label'			=> __('Minimum','acf'),
			'instructions'	=> __('Restrict which files can be uploaded','acf-csv-to-json'),
			'type'			=> 'text',
			'name'			=> 'min_size',
			'prepend'		=> __('File size', 'acf-csv-to-json'),
			'append'		=> 'MB',
		));
		
		// max
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum','acf'),
			'instructions'	=> __('Restrict which files can be uploaded','acf-csv-to-json'),
			'type'			=> 'text',
			'name'			=> 'max_size',
			'prepend'		=> __('File size', 'acf-csv-to-json'),
			'append'		=> 'MB',
		));
	}

	function render_field( $field ) {
		$o = array(
			'error'		=>	'',
			'class'		=>	'',
			'icon'		=>	'',
			'title'		=>	'',
			'size'		=>	'',
			'url'		=>	'',
			'name'		=>	'',
	);
	
	if( $field['value'] && is_numeric($field['value']) )
	{
			$file = get_post( $field['value'] );
			
			
			if( $file )
			{
					$o['class'] = 'active';
					$o['icon'] = wp_mime_type_icon( $file->ID );
					$o['title']	= $file->post_title;
					$o['size'] = @size_format(filesize( get_attached_file( $file->ID ) ));
					$o['url'] = wp_get_attachment_url( $file->ID );
					$tmp_url = explode('/', $o['url']);
					$o['name'] = end($tmp_url);				
			}
	}
	elseif( $field['value'] )
	{
			$o['error'] = $field['value'];
			$field['value'] = false;
			
	}
	?>
	<?php if( $file ) : $post_title = $file->post_title; ?>
	<p><strong>Current Data Set:</strong> <?=$post_title?></p>
	<?php endif; ?>
	<input class="acf-file-value" type="hidden" name="<?php echo $field['name']; ?>" value="<?php echo $field['value']; ?>"/>
		<div><button class="upload_image_button">Import New CSV File</button> <em class="file_preview"></em></div>
		<?php
	}
	
	function update_value( $value, $post_id, $field ) {
		
		return $value;

}
	
	
	
	function validate_value( $valid, $value, $field, $input ){
			// bail early if empty
		if( empty($value) ) return $valid;
		
		
		// bail ealry if is numeric
		if( is_numeric($value) ) {
			$type = get_post_mime_type($value);
			if ($type == 'text/csv') {
				return $valid;
			}
			print_r($type);
			return 'Invalid file type. Please upload a CSV file.';
		}
		
		
		// bail ealry if not basic string
		if( !is_string($value) ) return $valid;
			
		return false;
		
	}

	function format_value( $value, $post_id, $field ) {

		$file = get_attached_file( $value );
		$csv_content = file_get_contents( $file );
		$rows = explode("\n", trim($csv_content));
		$data = array_slice($rows, 1);
    $keys = array_fill(0, count($data), $rows[0]);
    $json = array_map(function ($row, $key) {
        return array_combine(str_getcsv($key), str_getcsv($row));
    }, $data, $keys);

    return json_encode($json, JSON_FORCE_OBJECT);
	}	
	
}

// initialize
new lgnd_acf_field_csv_to_json( $this->settings );


// class_exists check
endif;

?>