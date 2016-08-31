define(['react'], function (React) {
    var Tabs = React.createClass({
        displayName: "Tabs",

        getDefaultProps: function () {
            return {
                selected: 0
            };
        },
        getInitialState: function () {
            return {
                selected: this.props.selected
            };
        },
        getTabContent: function () {
            return React.createElement(
                "div",
                { className: "tabs-content" },
                this.props.children[this.state.selected]
            );
        },
        getTabTitles: function () {
            return React.createElement(
                "ul",
                { className: "tabs-labels" },
                this.props.children.map(function (child, index) {
                    var activeClass = this.state.selected === index ? 'active' : '';
                    return React.createElement(
                        "li",
                        { key: index, className: activeClass },
                        React.createElement(
                            "a",
                            { href: "#", onClick: this.handleClick.bind(this, index) },
                            child.props.label
                        )
                    );
                }.bind(this))
            );
        },
        handleClick: function (index, event) {
            event.preventDefault();
            this.setState({
                selected: index
            });
        },
        render: function () {
            return React.createElement(
                "div",
                { className: "tabs" },
                this.getTabTitles(),
                this.getTabContent()
            );
        }
    });

    return Tabs;
});
