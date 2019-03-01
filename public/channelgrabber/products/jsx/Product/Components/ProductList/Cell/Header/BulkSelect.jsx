import React from 'react';
import styled from 'styled-components';

import CheckboxStateless from  'Common/Components/Checkbox--stateless';

let CheckboxWrapper = styled.div`
  display: flex;
  justify-content: center;
  width: 100%;
`

class BulkSelectHeader extends React.Component {
    static defaultProps = {};
    state = {};

    render() {
        let isSelected = false;
        console.log('this.props in BulkSelect: ', this.props);

        return (
            <CheckboxWrapper>
                <CheckboxStateless
                    className={this.props.className}
                    onSelect={this.onSelectChange}
                    isSelected={isSelected}
                />
            </CheckboxWrapper>
        );
    }
}

export default BulkSelectHeader;

