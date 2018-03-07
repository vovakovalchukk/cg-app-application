define([
    'react',
], function(
    React
) {
    "use strict";

    var AddTodoComponent = React.createClass({
        input: null,
        getDefaultProps: function() {
            return {
                onAddClick: null
            };
        },
        onAddClick: function(e) {
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
        render: function() {
            return (
                <div>
                    <form onSubmit={this.onAddClick.bind(this)}>
                        <input
                            style={{float: "none"}}
                            ref={function(node) {
                                this.input = node;
                            }.bind(this)} />
                        <button type="submit">
                            Add Todo
                        </button>
                    </form>
                </div>
            )
        }
    });

    return AddTodoComponent;
});