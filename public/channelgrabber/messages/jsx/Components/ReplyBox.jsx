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
    console.log('ReplyBox props', props);
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
                onChange={props.actions.replyInputType}
                className={`u-margin-top-med`}
                placeholder={'Compose your reply here'}
            />
            <div className={`u-clear-both u-margin-top-med`}>

                <ButtonSelect
                    options={options}
                    ButtonTitle={() => (
                        <span>Send and resolve</span>
                    )}
                    spriteClass={'sprite-email-20-dblue'}
                    multiSelect={false}
                    onButtonClick={() => {
                        console.log('ButtonSelect onButtonClick id');
                    }}
                    onSelect={(ids) => {
                        console.log('ButtonSelect onSelect ids', ids);
                    }}
                />

            </div>
        </div>
    );
};

export default ReplyBox;