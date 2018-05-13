define([
    'react'
], function(
    React
) {
    var EditableField = React.createClass({
        getDefaultProps: function() {
            return {
                onChange: null,
                fieldId: null,
                classNames:['c-editable-field','u-heading-text u-margin-top-bottom-small']
            };
        },
        componentDidMount() {
            var editableField = document.getElementById(this.props.fieldId);
            editableField.addEventListener('input', function(e) {
                this.props.onChange(e);
            }.bind(this));
            editableField.addEventListener('keypress',function(e){
                if (e.key == 'Enter') {
                    e.preventDefault();
                }
            })
        },
        render: function() {
            return (
                <span
                    id={this.props.fieldId}
                    contentEditable={true}
                    className={this.props.classNames.join(' ')}
                >
                    Enter Product Name
                </span>
            );
        }
    });

    return EditableField;
});
