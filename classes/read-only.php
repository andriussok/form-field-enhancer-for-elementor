<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

// redOnly option

class FFEE_Forms_Readonly {

  public $allowed_fields = [
    'text',
    'textarea',
    'email',
    'url',
    'password',
    'tel',
    'number',
  ];

  public function __construct() {
    // Add custom control fields to form field render
    add_filter( 'elementor_pro/forms/render/item', [ $this, 'maybe_add_readonly' ], 10, 3 );
    add_action( 'elementor/element/form/section_form_fields/after_section_end', [ $this, 'add_readonly_field_control' ], 20, 2 );
  }

  /**
   * add_readonly_field_control
   * @param $element
   * @param $args
   */
  public function add_readonly_field_control( $element, $args ) {

    // Get the control you want to update
    // Get control data from the form
    $control_data = $element->get_controls('form_fields');

    if ( is_wp_error( $control_data ) ) {
      return;
    }

    // create a new custom control field as a repeater field
    $tmp = new Elementor\Repeater();
    
    $tmp->add_control(
        'field_readonly',
        array(
            'label' => esc_html__( 'Readonly', 'ffee-lang' ),
            'inner_tab' => 'form_fields_content_tab',
            'tab' => 'content',
            'tabs_wrapper' => 'form_fields_tabs',
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'conditions' => [
              'terms' => [
                  [
                    'name' => 'field_type',
                    'operator' => 'in',
                    'value' => $this->allowed_fields,
                  ],
              ],
            ],
            'label_on' => esc_html__( 'Yes', 'ffee-lang' ),
            'label_off' => esc_html__( 'No', 'ffee-lang' ),
            'default' => '',
        )
    );

    $readonly_fields = $tmp->get_controls();
    $readonly_field = $readonly_fields['field_readonly'];  

    // insert new custom control field in content tab before field ID control
    $new_order = [];
    foreach ( $control_data['fields'] as $field_key => $field ) {
      if ( 'required' === $field['name'] ) {
        $new_order['field_readonly'] = $readonly_field;
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
  public function maybe_add_readonly( $field, $field_index, $form_widget ) {
    
    if ( ! empty( $field['field_readonly'] )
        && in_array( $field['field_type'], $this->allowed_fields )
    ) {

      $attributes = [
        'readonly' => 'readonly',
      ];

      // If attributes are not empty, add them to the element
      if ( !empty( $attributes ) ) {
        $type = 'textarea' === $field['field_type'] ? 'textarea' : 'input';
        $form_widget->add_render_attribute( $type . $field_index, $attributes );
      }
    }

    return $field;
  }

}