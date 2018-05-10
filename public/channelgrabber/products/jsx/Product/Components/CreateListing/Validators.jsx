define([], function() {
    var Validators = {
        required: function(value) {
            if (value instanceof Array) {
                return (value.length > 0 ? undefined : 'Required');
            }
            return (value ? undefined : 'Required');
        },
        shouldShowError: function(field) {
            // Only show errors on submission, otherwise they start out as error'd
            return field.meta.error && (field.meta.touched || field.meta.submitting);
        }
    };

    return Validators;
});