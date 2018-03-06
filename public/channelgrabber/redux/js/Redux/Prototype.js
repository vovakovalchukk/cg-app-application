define(['react', 'react-dom', 'redux', 'react-redux', 'Redux/Components/Root', 'Redux/Reducers/Combined'], function (React, ReactDOM, Redux, ReactRedux, RootComponent, Reducer) {
    var Prototype = function (mountingNode) {
        var store = Redux.createStore(Reducer);
        var Provider = ReactRedux.Provider;
        ReactDOM.render(React.createElement(
            Provider,
            { store: store },
            React.createElement(RootComponent, null)
        ), mountingNode);
    };

    return Prototype;
});
