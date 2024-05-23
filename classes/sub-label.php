<?php

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

class FFEE_Forms_Sub_Label {

  public $allowed_fields = [
    'text',
    'textarea',
    'email',
    'url',
    'password',
    'tel',
    'number',
    'select',
  ];

  public function __construct() {

    // Add custom control fields to form field render
    add_filter('elementor_pro/forms/render/item', [$this, 'maybe_add_sublabel'], 10, 3);
    add_action('elementor/element/form/section_form_fields/before_section_end', [$this, 'add_sublabel_field_control'], 20, 2);
  }

  /**
   * add_sublabel_field_control
   * @param $element
   * @param $args
   */
  public function add_sublabel_field_control($element, $args) {

    // Get control data from the form
    $control_data = $element->get_controls('form_fields');

    if (is_wp_error($control_data)) {
      return;
    }

    // create a new custom control field as a repeater field
    $tmp = new Elementor\Repeater();

    $tmp->add_control(
      'field_sublabel',
      [
        'label'        => esc_html__( 'Sub Label', 'ffee-lang' ),
        'inner_tab'    => 'form_fields_content_tab',
        'tab'          => 'content',
        'tabs_wrapper' => 'form_fields_tabs',
        'type'         => 'text',
        'conditions'   => [
          'terms' => [
            [
              'name'     => 'field_type',
              'operator' => 'in',
              'value'    => $this->allowed_fields,
            ],
          ],
        ],
        'dynamic'      => [
          'active' => true,
        ],
      ]
    );

    $sublabel_fields = $tmp->get_controls();
    $sublabel_field = $sublabel_fields['field_sublabel'];

    // insert new custom control field in content tab before field ID control
    $new_order = [];
    foreach ($control_data['fields'] as $field_key => $field) {
      if ('field_label' === $field['name']) {
        $new_order['field_sublabel'] = $sublabel_field;
      }
      $new_order[$field_key] = $field;
    }
    $control_data['fields'] = $new_order;

    $element->update_control('form_fields', $control_data);
  }

  /**
   * Add attributes to frontend
   * @param $field
   * @param $field_index
   * @param $form_widget
   */
  public function maybe_add_sublabel($field, $field_index, $form_widget) {
    
    if (!empty($field['field_sublabel'])
      && in_array($field['field_type'], $this->allowed_fields)
    ) {

      $attributes = [
        'data-sublabel' => $field['field_sublabel'] ?? null,
      ];

      // Filter out empty attributes
      $attributes = array_filter($attributes);

      // If attributes are not empty, add them to the element
      if (!empty($attributes)) {
        $field_types = [
            'textarea' => 'textarea',
            'select' => 'select',
            // Add more mappings if needed
        ];

        $type = $field_types[$field['field_type']] ?? 'input';
        $form_widget->add_render_attribute($type . $field_index, $attributes);
      }
    }

    return $field;
  }

}
