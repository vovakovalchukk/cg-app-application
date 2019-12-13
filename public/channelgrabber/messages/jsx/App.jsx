import React, { useEffect } from 'react';
import MessageList from 'MessageCentre/Views/MessageList';
import MessageDetail from 'MessageCentre/Views/MessageDetail';
import NavItem from 'MessageCentre/Components/NavItem';
import {
    BrowserRouter as Router,
    Switch,
    Route,
    Link,
    Redirect,
    useRouteMatch,
    useParams
} from "react-router-dom";


const navItems = [
    {
        id: 'unassigned',
        displayText: 'Unassinged',
        to: `/list/:filterId`
    },
    {
        id: 'assigned',
        displayText: 'Assigned',
        to: `/list/:filterId`
    },
    {
        id: 'my-messages',
        displayText: 'My Messages',
        to: `/list`
    },
    {
        id: 'resolved',
        displayText: 'Resolved',
        alwaysDisplay: true,
        to: `/list`
    },
    {
        id: 'open-count',
        displayText: 'OpenCount',
        alwaysDisplay: true,
        to: `/list`
    }
];

const App = (props) => {
    useEffect(() => {
        props.actions.fetchFilters();
        props.actions.fetchMessages();
    }, []);

    let match = useRouteMatch();

    const activeFilter = props.filters.active;

    const formattedThreads = formatThreads(props.threads.byId, props.messages.byId);

    return (
        <div className="u-width-100pc u-display-flex">
            <div id="Sidebar" className="u-flex-1">
                <h1 className="u-width-100pc">sidebar</h1>
                <ol className="u-padding-none">
                    {renderNavItems((itemProps) => (
                        <NavItem key={itemProps.id} {...itemProps} to={`${match.path}list/${itemProps.id}`} />
                    ))}
                </ol>
            </div>
            <div id="Main" className="u-flex-5">
                <Switch>
                    <Route path={"/test"} render={(props)=>{return <div>teststsetset</div>}} />
                    <Route path={`${match.path}list/:filterId`} render={() => {
                        return <MessageList {...props} {...formattedThreads} />
                    }}/>
                    <Route path={`${match.path}message/:messageId`} render={(props) => (
                        <MessageDetail />
                    )}/>
                    <Redirect from={match.path} exact to={`${match.path}list/unassigned`} />
                </Switch>
            </div>
        </div>
    );

    function renderNavItems(renderItem) {
        return navItems.map((item) => {
            let navItemProps = {
                id: item.id,
                displayText: item.displayText,
                filterCount: item.id ==='openCount' ? getOpenCount() : getFilterCount(item.id),
                shouldDisplay: item.alwaysDisplay || isSingleUser()
            };
            return renderItem(navItemProps);
        })
    }

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