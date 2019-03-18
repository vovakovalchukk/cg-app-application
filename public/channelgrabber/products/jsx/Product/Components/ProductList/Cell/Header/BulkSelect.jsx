import React from 'react';
import styled from 'styled-components';

import CheckboxStateless from 'Common/Components/Checkbox--stateless';

const CheckboxWrapper = styled.div`
  display: flex;
  justify-content: center;
  width: 100%;
`;

class BulkSelectHeader extends React.Component {
    static defaultProps = {
        bulkSelect: {}
    };
    onSelect = () => {
        this.props.actions.toggleSelectAllBulkSelect();
    };
    render() {
        let {bulkSelect} = this.props;
        return (
            <CheckboxWrapper>
                <CheckboxStateless
                    className={this.props.className}
                    onSelect={this.onSelect}
                    isSelected={bulkSelect.selectAllOn}
                />
            </CheckboxWrapper>
        );
    }
}

export default BulkSelectHeader;