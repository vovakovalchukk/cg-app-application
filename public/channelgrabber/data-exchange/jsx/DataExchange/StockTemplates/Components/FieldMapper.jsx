import React from 'react';
import Input from 'Common/Components/Input';
import Select from 'Common/Components/Select';
import styled from 'styled-components';

const gridTemplateColumns = `grid-template-columns: 6rem 1fr 3rem 1fr 6rem;`;

const MapperContainer = styled.div`
    display: grid;
    grid-gap: 10px;
    ${gridTemplateColumns}
    width: 50rem;
`;
const HeaderRow = styled.div`
    grid-column: 1/-1;
    grid-row: 1;
    display: grid;
    grid-gap: 10px;
    ${gridTemplateColumns}
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
            console.log('creating onSelectCHange method');
            
            
            props.changeCgField(index, option);
        };
        rowParams['selectedField'] = props.selectOptions.find((option) => {
            return option.value === row.cgField
        });
        rowParams['fileField'] = row.fileField;
//        rowParams['deleteRow'] = () => {
//            props.removeFieldRow(index);
//        };

//        console.log('rowParams: ', {rowParams, row});
        return props.renderRow({index, ...rowParams});
    });
};

const FieldMapper = (props) => {
//    console.log('FieldMapper props.template: ', props.template);

    let {template, changeCgField, changeFileField, removeFieldRow, addFieldRow, cgFieldOptions} = props;
    return (<MapperContainer className={'u-margin-top-xxlarge'}>
            <HeaderRow>
                <MapperColumn1Header>File Column Header</MapperColumn1Header>
                <MapperColumn2Header>Channelgrabber Field</MapperColumn2Header>
            </HeaderRow>

            <FieldRows
                rows={template.columnMap}
                changeCgField={changeCgField}
                changeFileField={changeFileField}
                removeFieldRow={removeFieldRow}
                selectOptions={cgFieldOptions}
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
                            ->
                        </RowArrow>

                        <RowSelect
                            options={cgFieldOptions}
                            filterable={cgFieldOptions.length > 10}
                            autoSelectFirst={false}
                            className={'u-width-100pc'}
                            selectedOption={rowParams.selectedField}
                            onOptionChange={rowParams.onSelectChange}
                            classNames={'u-inline-block'}
                        />

                        <RowDelete onClick={props.deleteTemplate} className={"button"}>
                            Delete
                        </RowDelete>
                    </React.Fragment>
                )}
            />
        </MapperContainer>
    );
};

export default FieldMapper;