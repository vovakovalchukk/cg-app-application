import React, {useState} from "react";
import Select from 'Common/Components/Select';

const dummyOptions = [
    {name: 'option1', label: 'option1', value: 'option1', template: '<div>this is option1</div>'},
    {name: 'option2', label: 'option2', value: 'option2', template: 'this is option 2'},
    {name: 'option3', label: 'option3', value: 'option3', template: '<h2> this is option 3 </h2>'}
];

function TemplateEditor(props) {
    const [textEditorPosition, setTextEditorPosition] = useState(dummyOptions[0]);
    const [tagSelectValue, setTagSelectValue] = useState({});

    return (<div className={"u-margin-top-med"}>
        <h3>Template Designer</h3>

        <div className={"u-margin-top-small u-flex-v-center"}>
            <Select
                autoSelectFirst={true}
                title={'choose your tag'}
                selectedOption={tagSelectValue}
                onOptionChange={(option) => {
                    setTagSelectValue(option);
                }
                }
                options={dummyOptions}
            />
            <button onClick={() => {
                props.templateHTML.setTag(tagSelectValue.value, textEditorPosition)
            }}
                className={'u-margin-left-small'}
            >
                add tag
            </button>
        </div>
        <div className={"u-margin-top-small"}>
            <textarea
                style={{width: "600px", minHeight: '300px'}}
                value={props.templateHTML.value}
                onChange={props.templateHTML.onChange}
                onBlur={(e) => {
                    setTextEditorPosition(e.target.selectionStart);
                }
                }
            />
        </div>
    </div>);
}

TemplateEditor.defaultProps = {
    templateHTML: {}
};

export default TemplateEditor;