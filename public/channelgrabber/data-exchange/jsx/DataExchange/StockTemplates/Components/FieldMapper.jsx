import React from 'react';
import FieldWithLabel from 'Common/Components/FieldWithLabel';
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
`;
const RowSelect = styled(Select)`
    grid-column: 4;
`;
const RowDelete = styled.button`
    grid-column: 5;
`;

const FieldMapper = props => {
    const rows = [{}, {}, {}, {}];

    const FieldRows = ({renderRow}) => {
        return rows.map((row, index) => {
            let columnName = `Column ${index + 1}`;
            let inputId = `column-${index + 1}-input`;
            return renderRow(row, columnName, inputId);
        });
    };

    return (<MapperContainer className={'u-margin-top-xxlarge'}>
            <HeaderRow>
                <MapperColumn1Header>File Column Header</MapperColumn1Header>
                <MapperColumn2Header>Channelgrabber Field</MapperColumn2Header>
            </HeaderRow>

            <FieldRows
                renderRow={(row, columnName, inputId) => (
                    <React.Fragment>
                        <RowLabel htmlFor={inputId}>{columnName}</RowLabel>
                        <RowInput
                            id={inputId}
                            inputClassNames={'inputbox u-border-box'}
                        />

                        <RowArrow>
                            ->
                        </RowArrow>

                        <RowSelect
                            options={props.options}
                            filterable={true}
                            autoSelectFirst={false}
                            title={"choose your template to load"}
                            selectedOption={props.selectedOption}
                            onOptionChange={props.onOptionChange}
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