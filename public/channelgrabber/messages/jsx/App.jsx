import React, { useEffect } from 'react';
import MessageList from 'MessageCentre/Views/MessageList';
import MessageDetail from 'MessageCentre/Views/MessageDetail';
import TemplateManager from 'MessageCentre/Views/TemplateManager';
import navItems from 'MessageCentre/Nav/items';
import {
    Switch,
    Route,
    Redirect,
    useRouteMatch
} from 'react-router-dom';

const App = (props) => {
    useEffect(() => {
        props.actions.fetchFilters();
    }, []);

    const match = useRouteMatch();
    const formattedThreads = formatThreads(props.threads.byId, props.messages.byId);

    return (
        <div className="u-width-100pc u-display-flex">
            <div id="Sidebar" className="u-flex-1">
                <h1 className="u-width-100pc">sidebar</h1>
                <ol className="u-padding-none">
                    {renderNavItems((itemProps, NavComponent) => (
                       <NavComponent key={itemProps.id} {...itemProps} to={`/messages${itemProps.to}`}/>
                    ))}
                </ol>
            </div>
            <div id="Main" className="u-flex-5">
                <Switch>
                    <Route path={`${match.path}list/:activeFilter`} render={({match}) => (
                        <MessageList {...props} match={match} {...formattedThreads} />
                    )}/>
                    <Route path={`${match.path}thread/:threadId`} render={({match}) => (
                        <MessageDetail {...props} match={match}/>
                    )}/>
                    <Route path={`${match.path}templates`} render={({match}) => (
                        <TemplateManager {...props} match={match}/>
                    )}/>
                    <Redirect from={match.path} exact to={`${match.path}list/unassigned`} />
                </Switch>
            </div>
        </div>
    );

    function renderNavItems(renderItem) {
        const filteredItems = navItems.filter((item) => {
            return typeof item.shouldDisplay !== 'function' || item.shouldDisplay({ous: Object.values(props.assignableUsers)});
        });

        return filteredItems.map((item) => {
            let navItemProps = {
                id: item.id,
                displayText: item.displayText,
                filterCount: props.filters.byId[item.filterId] && props.filters.byId[item.filterId].count,
                to: item.to
            };
            return renderItem(navItemProps, item.component);
        })
    }

    function formatThreads(threads, messages) {
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
};

export default App;
