import React from 'react';
import styled from 'styled-components';
import ButtonLink from 'MessageCentre/Components/ButtonLink';
import ThreadHeader from 'MessageCentre/Components/ThreadHeader';

function createMarkup(raw) {
    return {__html: raw};
}

const GridDiv = styled.div`
    display: grid;
    grid-template-columns: 1fr 300px;
    grid-gap: 20px;
`;

const MessageDetail = (props) => {
    const {match, threads} = props;
    const {params} = match;
    const threadId = params.threadId.replace(':','');
    const thread = threads.byId[threadId];
    const totalThreadCount = threads.allIds.length;
    const thisThreadPosition = threads.allIds.indexOf(`${threadId}`);
    const prevThreadId = threads.allIds[thisThreadPosition - 1];
    const nextThreadId = threads.allIds[thisThreadPosition + 1]
    const prevThreadPath = thisThreadPosition !== 0 ? `/messages/thread/:${prevThreadId}` : `/messages/`;
    const nextThreadPath = thisThreadPosition !== totalThreadCount ? `/messages/thread/:${nextThreadId}` : `/messages/`;

    const headerProps = {...thread};
    headerProps.nextThreadPath = nextThreadPath;
    headerProps.prevThreadPath = prevThreadPath;
    headerProps.threadPosition = thisThreadPosition + 1;
    headerProps.totalThreadCount = totalThreadCount;

    const messages = [];
    thread.messages.forEach(messageId => {
        const message = props.messages.byId[messageId];
        messages.push(message);
    });

    const customerSprite = `sprite-message-customer-21-red`;
    const staffSprite = `sprite-message-staff-21-blue`;

    return (
        <GridDiv>

            <div>

                <ThreadHeader {...headerProps}/>

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

            </div>

            <div>

                <h2 className='u-clear-both u-float-none'>Right hand actions</h2>

                <ButtonLink
                    to={thread.ordersLink}
                    text={`${thread.ordersCount} Orders from ${thread.externalUsername}`}
                />

            </div>

        </GridDiv>
    );
};

export default MessageDetail;
