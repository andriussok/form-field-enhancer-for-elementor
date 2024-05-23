<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

class FFEE_Forms_Length_Validation {

  public $allowed_fields = [
    'text',
    'textarea',
  ];

  public function __construct() {
    // Add custom control fields to form field render
    add_filter( 'elementor_pro/forms/render/item', [ $this, 'maybe_add_length' ], 10, 3 );
    add_action( 'elementor/element/form/section_form_fields/before_section_end', [ $this, 'add_length_field_control' ], 20, 2 );
  }

  /**
   * add_length_field_control
   * @param $element
   * @param $args
   */
  public function add_length_field_control( $element, $args ) {

    // Get control data from the form
    $control_data = $element->get_controls('form_fields');

    if ( is_wp_error( $control_data ) ) {
      return;
    }

    // create a new custom control field as a repeater field
    $tmp = new Elementor\Repeater();
    
    $tmp->add_control(
      'minlength',
      [
        'label' => esc_html__( 'Min Length', 'ffee-lang' ),
        'inner_tab' => 'form_fields_advanced_tab',
        'tab' => 'content',
        'tabs_wrapper' => 'form_fields_tabs',
        'type' => 'number',
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
      'maxlength',
      [
        'label' => esc_html__( 'Max Length', 'ffee-lang' ),
        'inner_tab' => 'form_fields_advanced_tab',
        'tab' => 'content',
        'tabs_wrapper' => 'form_fields_tabs',
        'type' => 'number',
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
      'maxlength_counter',
      [
        'label' => esc_html__( 'Counter', 'ffee-lang' ),
        'inner_tab' => 'form_fields_advanced_tab',
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
        'label_on' => esc_html__( 'Show', 'ffee-lang' ),
        'label_off' => esc_html__( 'Hide', 'ffee-lang' ),
        'default' => 'yes',
        'separator' => 'after',
      ]
    );

    $length_fields = $tmp->get_controls();
    $minlength_field = $length_fields['minlength'];
    $maxlength_field = $length_fields['maxlength'];
    $maxlength_field_counter = $length_fields['maxlength_counter'];

    // insert new custom control field in content tab before field ID control
    $new_order = [];
    foreach ( $control_data['fields'] as $field_key => $field ) {
      if ( 'custom_id' === $field['name'] ) {
        $new_order['minlength'] = $minlength_field;
        $new_order['maxlength'] = $maxlength_field;
        $new_order['maxlength_counter'] = $maxlength_field_counter;
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
  public function maybe_add_length( $field, $field_index, $form_widget ) {
    
    if ((  ! empty( $field['minlength'] ) || ! empty( $field['maxlength'] ))
        && in_array( $field['field_type'], $this->allowed_fields )
    ) {

      // Add maxlength-counter class if the switch is on, returns true/false
      $maxlength_counter = !empty($field['maxlength']) && $field['maxlength_counter'] === 'yes';

      $attributes = [
        'minlength' => $field['minlength'] ?? null,
        'maxlength' => $field['maxlength'] ?? null,
        'class'     => $maxlength_counter ? 'maxlength-counter' : null,
      ];

      // Filter out empty attributes
      $attributes = array_filter( $attributes );

      // If attributes are not empty, add them to the element
      if ( !empty( $attributes ) ) {
        $type = 'textarea' === $field['field_type'] ? 'textarea' : 'input';
        $form_widget->add_render_attribute( $type . $field_index, $attributes );
      }

      // Add maxlength-counter class if the switch is on
      if ( ! empty( $field['maxlength'] ) && $field['maxlength_counter'] === 'yes' ) {
          $form_widget->add_render_attribute( $type . $field_index, 'class', 'maxlength-counter' );
      }
    }

    return $field;
  }

}