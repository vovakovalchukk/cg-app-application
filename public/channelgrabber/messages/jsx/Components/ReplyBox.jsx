import React from 'react';
import styled from 'styled-components';
import ButtonMultiSelect from 'Common/Components/ButtonMultiSelect';

const TextArea = styled.textarea`
    width: 100%;
    height: 20rem;
    resize: vertical;
    box-sizing: border-box;
    padding: 1rem;
`;

const ReplyBox = (props) => {
    console.log('ReplyBox props', props);
    let options = [];

    return (
        <div>
            <TextArea
                onChange={props.actions.replyInputType}
                className={`u-margin-top-med`}
                placeholder={'Compose your reply here'}
            />
            <div className={`u-clear-both u-margin-top-med`}>
                <ButtonMultiSelect
                    options={options}
                    buttonTitle={'Download'}
                    spriteClass={'sprite-download-pdf-22'}
                    onButtonClick={() => {console.log('TODO: ButtonMultiSelect click')}}
                />
            </div>
        </div>
    );
};

export default ReplyBox;