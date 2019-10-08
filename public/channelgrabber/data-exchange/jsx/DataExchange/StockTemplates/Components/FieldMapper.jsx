
import React from 'react';
import FieldWithLabel from 'Common/Components/FieldWithLabel';
import Input from 'Common/Components/Input';
import Select from 'Common/Components/Select';

const FieldRows = (column) => {
      return (
          <div>

          </div>
      )
};

const FieldMapper = props => {
    const rows = [{}, {}, {}, {}];



    const FieldRows = ({renderRow}) => {
        return rows.map(row => renderRow(row));
    };

    return (<div>
            <FieldRows
                renderRow={(row) => (
                    <div>
                        <FieldWithLabel label={'Template Name'} className={'u-margin-top-small'}>
                            <Input
                                inputClassNames={'inputbox u-border-box'}
                            />
                        </FieldWithLabel>
                        <Select
                            options={props.options}
                            filterable={true}
                            autoSelectFirst={false}
                            title={"choose your template to load"}
                            selectedOption={props.selectedOption}
                            onOptionChange={props.onOptionChange}
                            classNames={'u-inline-block'}
                        />
                        <button onClick={props.deleteTemplate} className={"button u-margin-left-small"}>
                            Delete
                        </button>
                    </div>
                )}
            />
        </div>
    );
};

export default FieldMapper;