import React from 'react';

class Tabs extends React.Component {
    static defaultProps = {
        selected: 0
    };

    state = {
        selected: this.props.selected
    };

    componentDidMount() {
        window.addEventListener('simpleTabChanged', this.onTabChanged, false);
    }

    componentWillUnmount() {
        window.removeEventListener('simpleTabChanged', this.onTabChanged, false);
    }

    onTabChanged = (event) => {
        this.setState({
            selected: event.detail.tabIndex
        });
    };

    getTabContent = () => {
        return (
            <div className="tabs-content">
                {this.props.children[this.state.selected]}
            </div>
        );
    };

    getTabTitles = () => {
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
    };

    handleClick = (index, event) => {
        event.preventDefault();
        this.setState({
            selected: index
        });
        window.triggerEvent('simpleTabChanged', {tabIndex: index});
    };

    render() {
        return (
            <div className="tabs">
                {this.getTabTitles()}
                {this.getTabContent()}
            </div>
        );
    }
}

export default Tabs;
