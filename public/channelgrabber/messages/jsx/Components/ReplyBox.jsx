import React from 'react';
import styled from 'styled-components';

const TextArea = styled.textarea`
    width: 100%;
    height: 20rem;
    resize: vertical;
    box-sizing: border-box;
    padding: 1rem;
`;

const ButtonGroup = styled.div`
    
`;

const ButtonGroupPrimary = styled.div`
    display: flex;
    
    & button:first-child {
        flex-grow: 1;
    }
`;

const ButtonGroupSecondary = styled.div`
    display: ${props => props.visible ? "flex" : "none"};
    flex-direction: column;
`;

const ReplyBox = (props) => {
    console.log('ReplyBox props', props);

    return (
        <div>
            <TextArea
                onChange={props.actions.replyInputType}
                className={`u-margin-top-med`}
                placeholder={'Compose your reply here'}
            />
            <div className={`u-clear-both u-margin-top-med`}>
                <ButtonGroup className={`u-width-300px`}>
                    <ButtonGroupPrimary>
                        <button type="button">Primary button</button>
                        <button type="button" onClick={props.actions.replyActionsToggleVisibility}>
                            <span className={`sprite-arrow-down-10-black`} />
                        </button>
                    </ButtonGroupPrimary>
                    <ButtonGroupSecondary visible={props.threads.secondaryReplyActionsVisible}>
                        <button type="button">Secondary button 1</button>
                        <button type="button">Secondary button 2</button>
                        <button type="button">Secondary button 3</button>
                    </ButtonGroupSecondary>
                </ButtonGroup>
            </div>
        </div>
    );
};

export default ReplyBox;