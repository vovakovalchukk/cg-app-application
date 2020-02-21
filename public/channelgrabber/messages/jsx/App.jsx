import React, { useEffect } from 'react';
import MessageList from 'MessageCentre/Views/MessageList';
import MessageDetail from 'MessageCentre/Views/MessageDetail';
import navItems from 'MessageCentre/Nav/items';
import Sidebar from 'Common/Components/Sidebar';
import styled from 'styled-components';

import {
    Switch,
    Route,
    Redirect,
    useRouteMatch
} from 'react-router-dom';

const Grid = styled.div`
    display: grid;
    grid-template-columns: 200px 1fr 225px;
    grid-template-rows: min-content 1fr min-content;
    grid-template-areas:
        "left head right"
        "left main right"
        "left foot right";
    position: absolute;
    top: 50px;
    left: 0;
    right: 0;
    bottom: 0;
`;

const GridLeftSide = styled.div`
    grid-area: left;
    background-color: #efeeee;
`;

const App = (props) => {
    useEffect(() => {
        props.actions.fetchFilters();
    }, []);

    const match = useRouteMatch();

    const characterLimit = 160;

    const formattedThreads = formatThreads(props.threads.byId, props.messages.byId);

    return (
        <Grid>
            <GridLeftSide>
                <Sidebar
                    id={"Sidebar"}
                    sections={[{
                        header: 'Messages',
                        renderContent: (NavItemWrapper) => {
                            return <ul>
                                {renderNavItems((itemProps, NavComponent) => (
                                    <li className={"u-border-box"}>
                                        <NavItemWrapper>
                                            <NavComponent
                                                key={itemProps.id}
                                                to={`/messages${itemProps.to}`}
                                                {...itemProps}
                                            />
                                        </NavItemWrapper>
                                    </li>
                                ))}
                            </ul>
                        }
                    }]}
                />
            </GridLeftSide>
            <Switch>
                <Route path={`${match.path}list/:activeFilter`} render={({match}) => (
                    <MessageList
                        filters={props.filters}
                        actions={props.actions}
                        match={match}
                        threadsLoaded={props.threads.loaded}
                        {...formattedThreads}
                    />
                )}/>
                <Route path={`${match.path}thread/:threadId`} render={({match}) => (
                    <MessageDetail
                        {...props}
                        match={match}
                    />
                )}/>
                <Redirect to={`${match.path}list/unassigned`} />
            </Switch>
        </Grid>
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
                to: item.to,
                className: item.className
            };
            return renderItem(navItemProps, item.component);
        })
    }

    function formatThreads(threads, messages) {
        threads = Object.values(threads);
        messages = Object.values(messages);

        threads.forEach(thread => {
            let threadMessages = messages.filter(message => {
                return thread.messages.includes(message.id);
            });
            threadMessages.sort((a,b) => {
                let date_a = new Date(a.created);
                let date_b = new Date(b.created);
                if(date_a > date_b) return 1;
                if(date_a < date_b) return -1;
                return 0;
            });
            let div = document.createElement('div');
            div.innerHTML = threadMessages[0].body;
            thread.lastMessage = div.textContent.replace(/\s+/g, ' ').substring(0, characterLimit);
            if (thread.lastMessage.length > characterLimit) {
                thread.lastMessage = thread.lastMessage.trimEnd() + `...`;
            }
            div.remove();
        });
        return {
            formattedThreads: threads
        };
    }
};

export default App;
