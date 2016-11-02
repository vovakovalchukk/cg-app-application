define([
    'react',
    'react-dom'
], function(
    React,
    ReactDOM
) {
    "use strict";

    var ClickOutsideComponent = React.createClass({
        getDefaultProps: function () {
            return {
                className: "",
                onClickOutside: null
            }
        },
        componentDidMount: function () {
            var self = this;
            self.el = ReactDOM.findDOMNode(this);

            this.evt = function (e) {
                var children = self.el.contains(e.target);
                if (e.target != self.el && !children) {
                    self.props.onClickOutside(e)
                }
            };
            document.addEventListener('click', this.evt, false);
        },
        componentWillUnmount: function () {
            document.removeEventListener('click', this.evt, false);
        },
        render: function () {
            return (
                <div className={this.props.className}>{this.props.children}</div>
            );
        }
    });

    return ClickOutsideComponent;
});
