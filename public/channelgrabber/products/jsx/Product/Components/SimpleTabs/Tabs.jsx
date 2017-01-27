define([
    'React'
], function (React) {
    var Tabs = React.createClass({
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
            return (
                <div className="tabs-content">
                    {this.props.children[this.state.selected]}
                </div>
            );
        },
        getTabTitles: function () {
            return (
                <ul className="tabs-labels">
                    {this.props.children.map(function (child, index) {
                        var activeClass = (this.state.selected === index ? 'active' : '');
                        return (
                            <li key={index} className={activeClass}>
                                <a href="#" onClick={this.handleClick.bind(this, index)}>
                                    {child.props.label}
                                </a>
                            </li>
                        );
                    }.bind(this))}
                </ul>
            );
        },
        handleClick: function (index, event) {
            event.preventDefault();
            this.setState({
                selected: index
            });
        },
        render: function () {
            return (
                <div className="tabs">
                    {this.getTabTitles()}
                    {this.getTabContent()}
                </div>
            );
        }
    });

    return Tabs;
});