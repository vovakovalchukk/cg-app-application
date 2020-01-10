import React from 'react';
import styled from 'styled-components';
import ButtonSelect from 'Common/Components/ButtonSelect';

const TextArea = styled.textarea`
    width: 100%;
    height: 20rem;
    resize: vertical;
    box-sizing: border-box;
    padding: 1rem;
`;

const ReplyBox = (props) => {
    const {actions, thread} = props;

    let options = [
        {
            name: 'Send and resolve',
            value: 'send-and-resolve'
        },
        {
            name: 'Send',
            value: 'send'
        }
    ];

    return (
        <div>
            <TextArea
                onChange={actions.replyInputType}
                className={`u-margin-top-med`}
                placeholder={'Compose your reply here'}
            />
            <div className={`u-clear-both u-margin-top-med`}>
                {thread.status === 'resolved' &&
                    <button
                        type={`button`}
                        onClick={actions.addMessage}
                    >Send</button>
                }
                {thread.status !== 'resolved' &&
                    <ButtonSelect
                        options={options}
                        ButtonTitle={() => (
                            <span>Send and resolve</span>
                        )}
                        spriteClass={'sprite-email-20-dblue'}
                        multiSelect={false}
                        onButtonClick={actions.addMessage}
                        onSelect={actions.addMessage}
                    />
                }
            </div>
        </div>
    );
};

export default ReplyBox;