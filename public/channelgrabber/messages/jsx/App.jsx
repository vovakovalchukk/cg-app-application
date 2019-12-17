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

    // console.log('App re-render', JSON.stringify(props.threads.byId, null, 1));


    const View = getView('messageList');

    const activeFilter = props.filters.active;

    const formattedThreads = formatThreads(props.threads.byId, props.messages.byId);

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
                    {...formattedThreads}
                />
            </div>
        </div>
    );

    function formatThreads (threads, messages) {
        threads = Object.values(threads);
        messages = Object.values(messages);
        threads.forEach(thread => {
            let threadMessages = messages.filter(function(message){
                return thread.messages.includes(message.id);
            });
            threadMessages.sort(function(a,b){
                let date_a = new Date(a.created);
                let date_b = new Date(b.created);
                if(date_a > date_b) return 1;
                if(date_a < date_b) return -1;
                return 0;
            });
            let div = document.createElement('div');
            div.innerHTML = threadMessages[0].body;
            thread.lastMessage = div.textContent;
            div.remove();
        });
        return {
            formattedThreads: threads
        };
    }

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