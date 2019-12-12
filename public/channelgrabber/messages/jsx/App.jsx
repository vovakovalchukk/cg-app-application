import React, { useEffect } from 'react';
import MessageList from 'MessageCentre/Views/MessageList';
import MessageDetail from 'MessageCentre/Views/MessageDetail';

const VIEW_COMPONENT_MAP = {
    'messageList' : MessageList,
    'messageDetail': MessageDetail
};

function getView (key) {
    return VIEW_COMPONENT_MAP[key]
}

const App = (props) => {
    useEffect(() => {
        props.actions.fetchFilters();
        props.actions.fetchMessages();
    }, []);

    const View = getView('messageList');

    const activeFilter = props.filters.active;

    return (
        <div className="u-width-100pc u-display-flex">
            <div id="Sidebar" className="u-flex-1">
                <h1 className="u-width-100pc">sidebar</h1>
                <ol className="u-padding-none">
                    { isSingleUser() && <li>Unassigned <span>{getFilterCount('unassigned')}</span></li> }
                    { isSingleUser() && <li>Assigned <span>{getFilterCount('assigned')}</span></li> }
                    { isSingleUser() && <li>My Messages <span>{getFilterCount('myMessages')}</span></li> }
                    <li>Resolved <span>{getFilterCount('resolved')}</span></li>
                    <li>Open Count {getOpenCount()}</li>
                </ol>
            </div>
            <div id="Main" className="u-flex-5">
                <View
                    {...props}
                />
            </div>
        </div>
    );

    function isSingleUser () {
        return Object.keys(props.assignableUsers).length > 1;
    };

    function getFilterCount(id){
        if (!props.filters.byId[id]) {
            return null;
        }
        return props.filters.byId[id].count.toString();
    };

    function getOpenCount () {
        return (Number(getFilterCount('myMessages')) +
            Number(getFilterCount('unassigned')) +
            Number(getFilterCount('assigned'))).toString();
    }
};

export default App;