console.log('in index.jsx');
import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';

export default (mountNode) => {
    ReactDOM.render(
        <App />,
        mountNode
    )
}

const something = 'something';