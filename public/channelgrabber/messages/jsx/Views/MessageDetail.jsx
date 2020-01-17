import React, { useEffect } from 'react';
import styled from 'styled-components';
import ButtonLink from 'MessageCentre/Components/ButtonLink';
import ThreadHeader from 'MessageCentre/Components/ThreadHeader';
import ShadowDomDiv from 'MessageCentre/Components/ShadowDomDiv';
import ReplyBox from 'MessageCentre/Components/ReplyBox';

const FlexDiv = styled.div`
    justify-content: space-between;
`;

const GridDiv = styled.div`
    display: grid;
    grid-template-columns: 1fr max-content;
    grid-gap: 1rem;
`;

const MessageLi = styled.li`
    overflow: auto;
    padding-bottom: 1rem;
    border-bottom: 1px solid #ccc;
    margin-bottom: 1rem;
`;

const StyledSelect = styled.select`
    display: flex;
    max-width: 260px;
    width: 100%;
`

const printMessage = (message) => {
    const newWindow = window.open();
    newWindow.document.write(message.body);
    newWindow.print();
    newWindow.close();
};

const formatMessages = (thread, allMessages) => {
    const formattedMessages = [];
    thread.messages.forEach(messageId => {
        const message = allMessages.byId[messageId];
        formattedMessages.push(message);
    });
    return formattedMessages;
};

const getPersonSprite = (person) => {
    const staff = 'sprite-message-staff-21-blue';
    const customer = 'sprite-message-customer-21-red';
    return person === 'staff' ? staff : customer;
};

const formatAssignableUsers = (users) => {
    let formattedUsers = [];
    Object.entries(users).forEach(user => {
        formattedUsers.push({
            id: user[0],
            name: user[1],
        });
    });

    return formattedUsers;
};

const MessageDetail = (props) => {
    const {match, threads, actions, assignableUsers} = props;
    const {params} = match;
    const threadId = params.threadId.replace(':','');
    const thread = threads.byId[threadId];
    const messages = formatMessages(thread, props.messages);
    const headerProps = {
        thread: thread,
        threadIds: threads.allIds,
    }

    useEffect(() => {
        props.threads.viewing = thread.id;
    }, []);

    const formattedAssignableUsers = formatAssignableUsers(assignableUsers);

    return (
        <GridDiv>
            <div>
                <ThreadHeader {...headerProps} />
                <ol className={`u-padding-none`}>
                    {messages.map((message, index) => {
                        return (
                            <MessageLi key={message.id}>
                                <FlexDiv className={`u-display-flex`}>
                                    <div>
                                        <div
                                            title={message.personType}
                                            className={getPersonSprite(message.personType)}
                                        />
                                        <p>{message.name}</p>
                                    </div>
                                    <div>
                                        <p>{message.created}</p>
                                        <button type="button" onClick={() => printMessage(message)}>Print Message</button>
                                    </div>
                                </FlexDiv>
                                <ShadowDomDiv body={message.body} />
                                <ReplyBox
                                    actions={actions}
                                    thread={thread}
                                />
                            </MessageLi>
                        )
                    })}
                </ol>
            </div>
            <div style={{
                display: `flex`,
                flexDirection: `column`,
            }}>
                <StyledSelect value={thread.status} onChange={props.actions.saveStatus}>
                    <option value={'awaiting reply'}>Awaiting Reply</option>
                    <option value={'resolved'}>Resolved</option>
                    <option value={'new'}>New</option>
                </StyledSelect>
                <ButtonLink
                    to={thread.ordersLink}
                    text={`${thread.ordersCount} Orders from ${thread.externalUsername}`}
                />

                {/*
                TODO
                1. Make this dropdown searchable
                */}

                {formattedAssignableUsers.length > 1 ?
                    <select
                        value={thread.assignedUserId === null ? '' : thread.assignedUserId}
                        onChange={props.actions.assignThreadToUser}
                    >
                        <option value="">Assign</option>
                        {formattedAssignableUsers.map(user => (
                            <option value={user.id}>{user.name}</option>
                        ))}
                    </select>
                : null}

            </div>
        </GridDiv>
    );
};

export default MessageDetail;
