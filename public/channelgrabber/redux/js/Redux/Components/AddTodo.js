define(['react'], function (React) {
    "use strict";

    var AddTodoComponent = React.createClass({
        displayName: 'AddTodoComponent',

        input: null,
        getDefaultProps: function () {
            return {
                onAddClick: null
            };
        },
        onAddClick: function (e) {
            e.preventDefault();
            if (!this.input.value.trim()) {
                return;
            }
            var text = this.input.value;
            this.input.value = '';
            if (this.props.onAddClick) {
                this.props.onAddClick(text);
            }
        },
        render: function () {
            return React.createElement(
                'div',
                null,
                React.createElement(
                    'form',
                    { onSubmit: this.onAddClick.bind(this) },
                    React.createElement('input', {
                        style: { float: "none" },
                        ref: function (node) {
                            this.input = node;
                        }.bind(this) }),
                    React.createElement(
                        'button',
                        { type: 'submit' },
                        'Add Todo'
                    )
                )
            );
        }
    });

    return AddTodoComponent;
});
