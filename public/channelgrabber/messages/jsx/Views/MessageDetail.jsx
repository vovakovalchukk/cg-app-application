import React, { useEffect } from 'react';
import styled from 'styled-components';
import ThreadHeader from 'MessageCentre/Components/ThreadHeader';
import ShadowDomDiv from 'MessageCentre/Components/ShadowDomDiv';
import ReplyBox from 'MessageCentre/Components/ReplyBox';
import Select from 'Common/Components/Select';
import ScrollToTop from 'MessageCentre/Components/ScrollToTop';
import LoadingSpinner from 'MessageCentre/Components/LoadingSpinner';

const printMessage = (message) => {
    const newWindow = window.open();
    newWindow.document.write(message.body);
    newWindow.print();
    newWindow.close();
};

const collapsibleCss = `<style>
    .collapsible-section {
        display: none;
        color: #989898;
    }
    .message-section-collapser {
        cursor: pointer;
        outline: none;
        padding: 10px 0;
        width: 22px;
    }
    .message-section-collapser.active + .collapsible-section {
        display: block;
    }
    .message-section-collapser .message-collapser-img-wrap {
        background-color: #f5f5f5;
        border: 1px solid #c2c2c2;
        clear: both;
        line-height: 6px;
        outline: none;
        position: relative;
        width: 20px;
    }
    .message-section-collapser .message-collapser-img-wrap img {
        background: url(/channelgrabber/zf2-v4-ui/img/ellipsis.png) no-repeat;
        height: 8px;
        opacity: .3;
        width: 20px;
    }
    .message-section-collapser .message-collapser-img-wrap img:hover {
        opacity: 0.9;
    }
    .message-section-collapser .message-collapser-img-wrap:hover {
        background-color: #c2c2c2;
        border: 1px solid #989898;
        color: #444444;
    }
</style>`;

const collapsibleHtml = `<div class="message-collapser-wrap">
    <div class="message-section-collapser" title="Toggle Hidden Lines" onclick="this.classList.toggle('active')">
        <div class="message-collapser-img-wrap">
            <img src="/channelgrabber/zf2-v4-ui/img/transparent-square.gif" alt="" />
        </div>
    </div>
    <span class="collapsible-section">$&</span>
</div>`;

const MinifyString = string => {
    return string.replace(/(\r\n|\n|\r)/gm, '').replace(/\s\s+/g, '');
};

const formatMessageBody = messageBody => {
    const regex = RegExp(/((?:^\>.*?$[\r\n]*)+)/gm);
    const markup = MinifyString(collapsibleHtml);
    const tagsWithSpaces = RegExp(/>[\r\n]</g);
    return messageBody.replace(regex, markup).replace(tagsWithSpaces, '><').nl2br();
}

const formatMessages = (thread, allMessages) => {
    const formattedMessages = [];
    if (typeof thread !== 'undefined') {
        thread.messages.forEach(messageId => {
            const message = allMessages.byId[messageId];
            message.body = formatMessageBody(message.body);
            formattedMessages.push(message);
        });
    }
    return formattedMessages;
};

const getPersonSprite = (person) => {
    const staff = 'sprite-message-staff-21-blue';
    const customer = 'sprite-message-customer-21-red';
    return person === 'staff' ? staff : customer;
};

const formatUsers = (users) => {
    const formattedUsers = [];

    Object.entries(users).forEach(user => {
        formattedUsers.push({
            value: user[0],
            name: user[1],
        });
    });

    return formattedUsers;
};

const findAssignedUser = (userId, users) => {
    return users.find(user => {
        return Number(user.value) === userId;
    });
};

const MessageList = styled.ol`
    padding: 0;
    margin: 0;
`;

const Message = styled.li`
    margin-top: 1rem;
    border-top: 1px solid #dddddd;
    padding-top: 1rem;
    
    &:first-child {
        margin-top: 0;
        border-top: none;
        padding-top: 0;
    }
`

const MessageMeta = styled.div`
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
`;

const StyledSelect = styled.select`
    display: flex;
    max-width: 260px;
    width: 100%;
    margin: 0.5rem 0 2rem;
    background-color: #fff;
    border: 1px solid #c2c2c2;
    border-radius: 5px;
    height: 28px;
    background-color: #ffffff;
    background: -webkit-gradient(linear, left top, left bottom, from(#ffffff), to(#f5f5f5));
`;

const FlexAlignItemsCenter = styled.div`
    align-items: center;
`;

const GridHead = styled.div`
    grid-area: head;
    padding: 1rem;
`;

const GridMain = styled.div`
    grid-area: main;
    overflow: auto;
    padding: 1rem;
`;

const GridReply = styled.div`
    grid-area: foot;
    padding: 1rem;
`;

const GridRightSide = styled.div`
    grid-area: right;
    background-color: #efeeee;
    display: flex;
    flex-direction: column;
    padding: 1rem;
`;

const NoMessage = styled.div`
    padding: 2rem;
`;

const ButtonA = styled.a`
    -webkit-font-smoothing: antialiased;
    color: #222 !important;
    font-family: Lato, Helvetica, Arial, sans-serif;
    font-size: 100%;
    vertical-align: baseline;
    line-height: normal;
    text-transform: none;
    -moz-appearance: button;
    -webkit-appearance: button;
    appearance: button;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    word-break: break-all;
`;

const MessageDetail = (props) => {
    const {match, threads, messages, actions, assignableUsers, templates} = props;

    const {params} = match;

    const threadId = params.threadId.replace(':','');

    const thread = threads.byId[threadId];

    const formattedMessages = formatMessages(thread, messages);

    const headerProps = {
        thread: thread,
        threadIds: threads.allIds,
    };

    let ordersButtonText = 'Loading order count...';

    if (typeof thread !== 'undefined') {
        threads.viewing = thread.id;
        if (thread.ordersCount === '?') {
            actions.fetchThreadOrderCountByThreadId(threadId);
        } else {
            ordersButtonText = `${thread.ordersCount} orders from ${thread.name}`;
        }
    }

    useEffect(() => {
        if (typeof thread === 'undefined') {
            actions.fetchThreadById(threadId);
        }
    }, []);

    const formattedUsers = formatUsers(assignableUsers);

    if (!threads.loaded && typeof thread === 'undefined') {
        return (
            <React.Fragment>
                <LoadingSpinner />
            </React.Fragment>
        );
    }

    if (threads.loaded && typeof thread === 'undefined') {
        return (
            <NoMessage>
                <span className="heading-large u-margin-bottom-med">No message found</span>
                Please choose a filter from the sidebar to load available messages.
            </NoMessage>
        );
    }

    return (
        <React.Fragment>

            <ScrollToTop />

            <GridHead>
                <ThreadHeader {...headerProps} />
            </GridHead>

            <GridMain>
                <MessageList>
                    {formattedMessages.map((message) => {
                        return (
                            <Message key={message.id}>
                                <MessageMeta>
                                    <FlexAlignItemsCenter className={`u-display-flex`}>
                                        <div className={getPersonSprite(message.personType)} />
                                        <div className={`u-margin-left-xsmall`}>{message.name}</div>
                                    </FlexAlignItemsCenter>
                                    <FlexAlignItemsCenter className={`u-display-flex`}>
                                        <div>{message.created}</div>
                                        <button
                                            className={`u-margin-left-xsmall button`}
                                            type="button"
                                            onClick={() => printMessage(message)}
                                        >
                                            Print Message
                                        </button>
                                    </FlexAlignItemsCenter>
                                </MessageMeta>
                                <ShadowDomDiv
                                    body={message.body}
                                    styles={MinifyString(collapsibleCss)}
                                />
                            </Message>
                        )
                    })}
                </MessageList>
            </GridMain>

            <GridReply>
                <ReplyBox
                    actions={actions}
                    thread={thread}
                    templates={templates}
                />
            </GridReply>

            <GridRightSide>
                <label className={'heading-medium u-cursor-pointer'}>
                    Status:
                    <StyledSelect
                        value={thread.status}
                        onChange={actions.saveStatus}
                    >
                        <option value={'awaiting reply'}>Awaiting Reply</option>
                        <option value={'resolved'}>Resolved</option>
                        <option value={'new'}>New</option>
                    </StyledSelect>
                </label>
                <ButtonA
                    className={`u-margin-bottom-xsmall button`}
                    href={thread.ordersLink}
                >
                    {ordersButtonText}
                </ButtonA>

                <FlexAlignItemsCenter className={`u-display-flex u-margin-bottom-xsmall`}>
                    <div className={`sprite-messages-${thread.channel}-36`} />
                    <span className={`u-margin-left-xsmall`}>{thread.accountName}</span>
                </FlexAlignItemsCenter>

                {formattedUsers.length > 1 &&
                <label className={'heading-medium u-cursor-pointer'}>
                    <span className={'u-display-flex u-margin-bottom-xsmall'}>Assign:</span>
                    <Select
                        id={"assignableUserSelect"}
                        name={"assignableUserSelect"}
                        options={formattedUsers}
                        filterable={true}
                        autoSelectFirst={false}
                        selectedOption={findAssignedUser(thread.assignedUserId, formattedUsers)}
                        onOptionChange={(option) => actions.assignThreadToUser(option.value)}
                        classNames={'u-width-100pc'}
                    />
                </label>
                }
            </GridRightSide>

        </React.Fragment>
    );

};

export default MessageDetail;
