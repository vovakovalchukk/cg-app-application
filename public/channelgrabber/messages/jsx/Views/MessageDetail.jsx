import React from 'react';
import {Link} from 'react-router-dom';
import styled from 'styled-components';
import ButtonLink from 'MessageCentre/Components/ButtonLink';

function createMarkup(raw) {
    return {__html: raw};
}

const GridDiv = styled.div`
    display: grid;
    grid-template-columns: 1fr 300px;
`;

const MessageDetail = (props) => {
    const {match} = props;
    const {params} = match;
    const threadId = params.threadId.replace(':','');
    const thread = props.threads.byId[threadId];
    const totalMessageCount = props.threads.allIds.length;
    const thisThreadPosition = props.threads.allIds.indexOf(`${threadId}`);
    const prevThreadId = props.threads.allIds[thisThreadPosition - 1];
    const nextThreadId = props.threads.allIds[thisThreadPosition + 1]
    const prevThreadPath = thisThreadPosition !== 0 ? `/messages/thread/:${prevThreadId}` : `/messages/`;
    const nextThreadPath = thisThreadPosition !== totalMessageCount? `/messages/thread/:${nextThreadId}` : `/messages/`;
    const messages = [];
    thread.messages.forEach(messageId => {
        const message = props.messages.byId[messageId];
        messages.push(message);
    });
    const customerSprite = `sprite-message-customer-21-red`;
    const staffSprite = `sprite-message-staff-21-blue`;

    return (
        <GridDiv>

            <ButtonLink
                to={`/messages/`}
                text={`back button to close thread and go back to messagelist view`}
            />

            <h1 className='u-clear-both u-float-none'>{thread.subject}</h1>

            <ButtonLink
                to={prevThreadPath}
                text={`previous button to navigate to previous thread`}
            />

            <p>{thisThreadPosition + 1} / {totalMessageCount}</p>

            <ButtonLink
                to={nextThreadPath}
                text={`next button to navigate to next thread`}
            />

            <h2 className='u-clear-both u-float-none'>Last message content</h2>

            <div className='u-clear-both' dangerouslySetInnerHTML={createMarkup(thread.lastMessage)} />

            <h2 className='u-clear-both u-float-none'>Messages in thread</h2>

            <ol>
                {messages.map((message) => {
                    const spriteClass = message.personType === 'customer' ? customerSprite : staffSprite;
                    return <li key={message.id}>
                        <div title={message.personType} className={spriteClass} />
                        <p>{message.name}</p>
                        <p>{message.created}</p>
                        <div className='u-clear-both' dangerouslySetInnerHTML={createMarkup(message.body)} />
                    </li>
                })}
            </ol>

            <h2 className='u-clear-both u-float-none'>Right hand actions</h2>

            <ButtonLink
                to={thread.ordersLink}
                text={`${thread.ordersCount} Orders from ${thread.externalUsername}`}
            />

        </GridDiv>
    );
};

export default MessageDetail;
