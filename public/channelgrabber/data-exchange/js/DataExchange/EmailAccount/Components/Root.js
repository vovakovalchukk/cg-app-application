import React from 'react';
import EmailAccounts from './EmailAccounts';

class RootComponent extends React.Component {
    render() {
        return React.createElement(EmailAccounts, null);
    }
}

export default RootComponent;
