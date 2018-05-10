define([], function() {
    var Validators = {
        required: function(value) {
            if (value instanceof Array) {
                return (value.length > 0 ? undefined : 'Required');
            }
            return (value ? undefined : 'Required');
        }
    };

    return Validators;
});