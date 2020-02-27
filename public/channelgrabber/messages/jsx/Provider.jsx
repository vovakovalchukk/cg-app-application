import React from 'react';
import {applyMiddleware, createStore} from 'redux';
import {Provider} from 'react-redux';
import MessageCentreRoot from 'MessageCentre/Root';
import initializeStore from './store';
import { BrowserRouter as Router, Route } from 'react-router-dom'

const MessageCentreProvider = (props) => {
    const {messageTemplates, ...remainingProps} = props;

    return (
        <Provider
            store={initializeStore(props)}
        >
            <Router>
                <Route path="/messages/" render={() => (
                    <MessageCentreRoot
                        {...remainingProps}
                    />
                )}/>
            </Router>
        </Provider>
    );
};

export default MessageCentreProvider;