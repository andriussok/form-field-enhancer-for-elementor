// Function to add character counter to input and textarea fields
function addEffcScripts($scope) {
    // sublabel
    const formFieldsSublabel = $scope.find('.elementor-field-textual[data-sublabel]');
    formFieldsSublabel.each(function() {
        // Get the field element
        let $field = jQuery(this);
        // Check if the field has data-sublabel attribute
        if ($field.data('sublabel')) {
            // Create and append sublabel element
            const sublabelElement = document.createElement('label');
            sublabelElement.classList.add('ffee-sublabel');
            sublabelElement.textContent = $field.data('sublabel');
            if($field.is('select')) {
                $field.closest('.elementor-select-wrapper').after(sublabelElement);
            } else {
                $field.after(sublabelElement);
            }
        }
    });

    // length validation
    const formFields = $scope.find('.maxlength-counter[maxlength]');
    formFields.each(function() {
        // Get the field element
        let $field = jQuery(this);
        // Check if the field has maxlength attribute
        if ($field.attr('maxlength')) {
            // Create and append counter element
            const counterElement = document.createElement('div');
            counterElement.classList.add('character-counter');
            $field.after(counterElement);
            // Update counter on input
            $field.on('input', function() {
                const remaining = $field.attr('maxlength') - $field.val().length;
                counterElement.textContent = remaining;
                if (remaining <= 0) {
                    counterElement.style.color = 'red';
                } else {
                    counterElement.style.color = ''; // Reset to default color
                }
            });
            // Initial update
            const remaining = $field.attr('maxlength') - $field.val().length;
            counterElement.textContent = remaining;
        }
    });
}

jQuery(window).on('elementor/frontend/init', function() {
    // This will run when the form widget is ready on the frontend.
    elementorFrontend.hooks.addAction('frontend/element_ready/form.default', function($scope) {
        // Call our script
        addEffcScripts($scope);
    });
});