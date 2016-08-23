define([
    'react'
], function(
    React
) {
    "use strict";

    var SelectComponent = React.createClass({
        getDefaultProps: function () {
            return {
                options: []
            };
        },
        getInitialState: function () {
            return {
                selectedValue: ''
            }
        },
        onChange: function (e) {
            this.setState({
                selectedValue: e.target.value
            });
            console.log(this.state.selectedValue);
        },
        render: function () {
            console.log(this.props.options);
            return (
                <select value={this.state.selectedValue}>
                    {this.props.options.map(function(opt) {
                        return <option key={opt.value} value={opt.value}>{opt.name}</option>
                    })}
                </select>
            );
        }
    });

    return SelectComponent;
});