import React from 'react';
import ReactDOM from 'react-dom';
import { createStore } from 'redux';
import { Provider } from 'react-redux';
import RootContainer from './Components/Root';
import CombinedReducer from './Reducers/Combined';

let App = function (mountingNode, data) {
    let store = createStore(CombinedReducer, {
        emailAccounts: data.emailAccounts
    });

    ReactDOM.render(React.createElement(
        Provider,
        { store: store },
        React.createElement(RootContainer, null)
    ), mountingNode);
};

export default App;
