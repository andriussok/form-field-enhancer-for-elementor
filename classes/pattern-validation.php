<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

class FFEE_Forms_Patterns_Validation {

  public $allowed_fields = [
    'text',
    'email',
    'url',
    'password',
    'tel',
    'number',
  ];

  public function __construct() {
    // Add pattern attribute to form field render
    add_filter( 'elementor_pro/forms/render/item', [ $this, 'maybe_add_pattern' ], 10, 3 );
    add_action( 'elementor/element/form/section_form_fields/before_section_end', [ $this, 'add_pattern_field_control' ], 20, 2 );
  }

  /**
   * add_pattern_field_control
   * @param $element
   * @param $args
   */
  public function add_pattern_field_control( $element, $args ) {
    
    // Get control data from the form
    $control_data = $element->get_controls('form_fields');

    if ( is_wp_error( $control_data ) ) {
      return;
    }
    // create a new pattern control as a repeater field
    $tmp = new Elementor\Repeater();
  
    $tmp->add_control(
      'field_pattern',
      [
        'label' => esc_html__( 'Pattern', 'ffee-lang' ),
        'inner_tab' => 'form_fields_advanced_tab',
        'tab' => 'content',
        'tabs_wrapper' => 'form_fields_tabs',
        'type' => 'text',
        'conditions' => [
          'terms' => [
            [
              'name' => 'field_type',
              'operator' => 'in',
              'value' => $this->allowed_fields,
            ],
          ],
        ],
      ]
    );

    $tmp->add_control(
      'field_pattern_description',
      [
        'label' => esc_html__( 'Pattern description', 'ffee-lang' ),
        'inner_tab' => 'form_fields_advanced_tab',
        'tab' => 'content',
        'tabs_wrapper' => 'form_fields_tabs',
        'type' => 'text',
        'conditions' => [
          'terms' => [
            [
              'name' => 'field_type',
              'operator' => 'in',
              'value' => $this->allowed_fields,
            ],
          ],
        ],
      ]
    );


    $pattern_fields = $tmp->get_controls();
    $pattern_field = $pattern_fields['field_pattern'];
    $pattern_description_field = $pattern_fields['field_pattern_description'];

    // insert new pattern field in advanced tab before field ID control
    $new_order = [];
    foreach ( $control_data['fields'] as $field_key => $field ) {
      if ( 'custom_id' === $field['name'] ) {
        $new_order['field_pattern'] = $pattern_field;
        $new_order['field_pattern_description'] = $pattern_description_field;
      }
      $new_order[ $field_key ] = $field;
    }
    $control_data['fields'] = $new_order;

    $element->update_control( 'form_fields', $control_data );
  }
  
  /**
   * Add attributes to frontend
   * @param $field
   * @param $field_index
   * @param $form_widget
   */
  public function maybe_add_pattern( $field, $field_index, $form_widget ) {
    
    if ( ! empty( $field['field_pattern'] ) && in_array( $field['field_type'], $this->allowed_fields ) ) {

      $validation_description = $field['field_pattern_description'];
      if(empty($validation_description)) {
        $validation_description = '"Please match the requested format \"' . $field['field_pattern'] . '\" "'; 
      } else {
        $validation_description = '"' . $validation_description . '"';
      }

        $form_widget->add_render_attribute( 'input' . $field_index,
          [
            'pattern' => $field['field_pattern'],
            'oninvalid' => 'this.setCustomValidity(' . $validation_description . ')',
            'oninput' => 'this.setCustomValidity("")',
            'title' => $validation_description,
          ]
        );
    }
    return $field;
  }

}