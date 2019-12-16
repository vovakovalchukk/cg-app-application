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

const FieldRows = (props) => {
    return props.rows.map((row, index) => {
        const buildOptionsForRow = () => {
            return props.allOptions.map((option) => {
                const disabled = !!(props.rows.find((rowData, rowIndex) => {
                    if (rowIndex === index) {
                        return false;
                    }
                    return rowData.cgField == option.value;
                }));

                return {
                    name: option.name,
                    value: option.value,
                    disabled: disabled
                }
            });
        };

        const rowParams = {
            columnName: `Column ${index + 1}`,
            inputId: `column-${index + 1}-input`,
            onInputChange: (e) => {props.changeFileField(index, e.target.value)},
            onSelectChange: (option) => {props.changeCgField(index, option.value)},
            deleteTemplate: () => {props.removeFieldRow(index)},
            options: buildOptionsForRow(),
            selectedField: props.allOptions.find((option) => {return option.value === row.cgField}),
            fileField: row.fileField
        };
        rowParams.shouldRenderDelete = props.rows.length -1 !== index || (rowParams.fileField || rowParams.selectedField);

        return props.renderRow({index, ...rowParams});
    });
};

const FieldMapper = (props) => {
    let {template, changeCgField, changeFileField, removeFieldRow, addFieldRow, allCgFieldOptions} = props;
    const columnMap = [...template.columnMap];
    columnMap.sort((columnMapOne, columnMapTwo) => {
        return columnMapOne.order > columnMapTwo.order ? 1 : -1;
    });

    return (<MapperContainer className={'u-margin-top-xxlarge'} containerWidth={props.containerWidth}>
            <HeaderRow containerWidth={props.containerWidth}>
                <MapperColumn1Header>File Column Header</MapperColumn1Header>
                <MapperColumn2Header>Channelgrabber Field</MapperColumn2Header>
            </HeaderRow>

            <FieldRows
                rows={columnMap}
                changeCgField={changeCgField}
                changeFileField={changeFileField}
                removeFieldRow={removeFieldRow}
                allOptions={allCgFieldOptions}
                addFieldRow={addFieldRow}
                renderRow={(rowParams) => {
                    return <React.Fragment>
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
                            options={rowParams.options}
                            selectedOption={rowParams.selectedField}
                            filterable={rowParams.options.length > 10}
                            autoSelectFirst={false}
                            className={'u-width-100pc'}
                            onOptionChange={rowParams.onSelectChange}
                            classNames={'u-inline-block'}
                        />

                        {rowParams.shouldRenderDelete &&
                        <RemoveIcon onClick={rowParams.deleteTemplate}/>}

                    </React.Fragment>
                }}
            />
        </MapperContainer>
    );
};

export default FieldMapper;