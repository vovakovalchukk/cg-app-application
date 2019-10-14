import React, {useRef, useEffect} from 'react';
import Input from 'Common/Components/Input';
import Select from 'Common/Components/Select';
import RemoveIcon from 'Common/Components/RemoveIcon';
import styled from 'styled-components';


function getGridTemplateColumns (containerWidth){
    return `grid-template-columns: ${containerWidth / 5}px 1fr 3rem 1fr 6rem;`;
}

const MapperContainer = styled.div`
    display: grid;
    row-gap: 10px;
    ${props => getGridTemplateColumns(props.containerWidth)};
    width: inherit;
`;
const HeaderRow = styled.div`
    grid-column: 1/-1;
    grid-row: 1;
    display: grid;
    ${props => getGridTemplateColumns(props.containerWidth)};
`;
const MapperColumn1Header = styled.div`
    grid-column: 2 ;
`;
const MapperColumn2Header = styled.div`
    grid-column: 4;
`;
const RowLabel = styled.label`
    grid-column: 1;
`;
const RowInput = styled(Input)`
    grid-column: 2;
`;
const RowArrow = styled.div`
    grid-column: 3;
    display: flex;
    justify-content: center;
    transform: scale(2);
    transform-origin: center;
    line-height: 2.2rem;
`;
const RowSelect = styled(Select)`
    grid-column: 4;
`;
const RowDelete = styled.button`
    grid-column: 5;
`;

const FieldRows = (props) => {
    return props.rows.map((row, index) => {
        let rowParams = {};
        rowParams['columnName'] = `Column ${index + 1}`;
        rowParams['inputId'] = `column-${index + 1}-input`;
        rowParams['onInputChange'] = (e) => {
            let desiredValue = e.target.value;
            props.changeFileField(index, desiredValue)
        };
        rowParams['onSelectChange'] = (option) => {
            props.changeCgField(index, option.value);
        };
        rowParams['deleteTemplate'] = () => {
            props.removeFieldRow(index);
        };
        rowParams['selectedField'] = props.allOptions.find((option) => {
            return option.value === row.cgField
        });
        rowParams['fileField'] = row.fileField;
//        rowParams['deleteRow'] = () => {
//            props.removeFieldRow(index);
//        };
        rowParams['shouldRenderDelete'] = props.rows.length -1 !== index ||
            (rowParams.fileField || rowParams.selectedField);
//        console.log('rowParams: ', {rowParams, row});
        return props.renderRow({index, ...rowParams});
    });
};

const FieldMapper = (props) => {
    console.log('in fieldMapper render');

//    console.log('FieldMapper props.template: ', props.template);
    let {template, changeCgField, changeFileField, removeFieldRow, addFieldRow, availableCgFieldOptions, allCgFieldOptions} = props;
    return (<MapperContainer className={'u-margin-top-xxlarge'} containerWidth={props.containerWidth}>
            <HeaderRow containerWidth={props.containerWidth}>
                <MapperColumn1Header>File Column Header</MapperColumn1Header>
                <MapperColumn2Header>Channelgrabber Field</MapperColumn2Header>
            </HeaderRow>

            <FieldRows
                rows={template.columnMap}
                changeCgField={changeCgField}
                changeFileField={changeFileField}
                removeFieldRow={removeFieldRow}
                allOptions={allCgFieldOptions}
                addFieldRow={addFieldRow}
                renderRow={(rowParams) => (
                    <React.Fragment>
                        <RowLabel htmlFor={rowParams.inputId}>{rowParams.columnName}</RowLabel>
                        <RowInput
                            id={rowParams.inputId}
                            inputClassNames={'inputbox u-border-box'}
                            value={rowParams.fileField}
                            onChange={rowParams.onInputChange}
                        />

                        <RowArrow>
                            →
                        </RowArrow>

                        <RowSelect
                            options={availableCgFieldOptions}
                            filterable={availableCgFieldOptions.length > 10}
                            autoSelectFirst={false}
                            className={'u-width-100pc'}
                            selectedOption={rowParams.selectedField}
                            onOptionChange={rowParams.onSelectChange}
                            classNames={'u-inline-block'}
                        />

                        {rowParams.shouldRenderDelete &&
                            <RemoveIcon onClick={rowParams.deleteTemplate}/>}

                    </React.Fragment>
                )}
            />
        </MapperContainer>
    );
};

export default FieldMapper;

// Hook
function useWhyDidYouUpdate(name, props) {
    // Get a mutable ref object where we can store props ...
    // ... for comparison next time this hook runs.
    const previousProps = useRef();

    useEffect(() => {
        if (previousProps.current) {
            // Get all keys from previous and current props
            const allKeys = Object.keys({ ...previousProps.current, ...props });
            // Use this object to keep track of changed props
            const changesObj = {};
            // Iterate through keys
            allKeys.forEach(key => {
                // If previous is different from current
                if (previousProps.current[key] !== props[key]) {
                    // Add to changesObj
                    changesObj[key] = {
                        from: previousProps.current[key],
                        to: props[key]
                    };
                }
            });

            // If changesObj not empty then output to console
            if (Object.keys(changesObj).length) {
                console.log('[why-did-you-update]', name, changesObj);
            }
        }

        // Finally update previousProps with current props for next hook call
        previousProps.current = props;
    });
}