define([
    'react',
    'react-router-dom',
    'Redux/Components/TodoListApp',
    'Redux/Components/ContactFormApp'
], function(
    React,
    ReactRouterDom,
    TodoListApp,
    ContactFormApp
) {
    "use strict";

    var RootComponent = React.createClass({
        getInitialState: function() {
            return {
                rootPath: "/"
            };
        },
        componentWillMount: function() {
            this.setState({rootPath: window.location.pathname});
        },
        render: function()
        {
            var NavLink = ReactRouterDom.NavLink;
            var Switch = ReactRouterDom.Switch;
            var Route = ReactRouterDom.Route;
            var rootPath = this.state.rootPath;
            return (
                <div style={{width: "500px"}}>
                    <p>Which prototype would you like to try?:</p>
                    <NavLink to={rootPath + '/todo'} activeClassName="disabled">Todo List</NavLink>
                    {" | "}
                    <NavLink to={rootPath + '/contact'} activeClassName="disabled">Contact Form</NavLink>
                    <Switch>
                        <Route path={rootPath + '/todo'} component={TodoListApp} />
                        <Route path={rootPath + '/contact'} component={ContactFormApp} />
                    </Switch>
                </div>
            );
        }
    });

    return RootComponent;
});