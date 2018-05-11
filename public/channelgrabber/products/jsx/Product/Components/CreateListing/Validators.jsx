define([], function() {
    var Validators = {
        required: function(value) {
            if (value instanceof Array) {
                if (value.length === 0) {
                    return 'Required';
                }
                if (value.filter(arrayValue => arrayValue).length === 0) {
                    return 'Required';
                }
                return undefined;
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