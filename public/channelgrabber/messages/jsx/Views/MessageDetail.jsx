import React from 'react';
import styled from 'styled-components';
import ButtonLink from 'MessageCentre/Components/ButtonLink';
import ThreadHeader from 'MessageCentre/Components/ThreadHeader';
import ShadowDomDiv from 'MessageCentre/Components/ShadowDomDiv';

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

const MessageDetail = (props) => {
    const {match, threads} = props;
    const {params} = match;
    const threadId = params.threadId.replace(':','');
    const thread = threads.byId[threadId];
    const messages = formatMessages(thread, props.messages);

    const customerSprite = `sprite-message-customer-21-red`;
    const staffSprite = `sprite-message-staff-21-blue`;

    const headerProps = {
        thread: thread,
        threadIds: threads.allIds,
    }

    return (
        <GridDiv>
            <div>
                <ThreadHeader {...headerProps} />
                <ol>
                    {messages.map((message, index) => {
                        const spriteClass = message.personType === 'customer' ? customerSprite : staffSprite;
                        return (
                            <MessageLi key={message.id}>
                                <FlexDiv className={`u-display-flex`}>
                                    <div>
                                        <div title={message.personType} className={spriteClass} />
                                        <p>{message.name}</p>
                                    </div>
                                    <div>
                                        <p>{message.created}</p>
                                        <button type="button" onClick={() => printMessage(message)}>Print Message</button>
                                    </div>
                                </FlexDiv>
                                <ShadowDomDiv body={message.body} />
                            </MessageLi>
                        )
                    })}
                </ol>
            </div>
            <div>
                <ButtonLink
                    to={thread.ordersLink}
                    text={`${thread.ordersCount} Orders from ${thread.externalUsername}`}
                />
            </div>
        </GridDiv>
    );
};

export default MessageDetail;
