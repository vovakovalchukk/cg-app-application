define([
    'react',
    'styled-components'
], function(
    React,
    styled
) {
    "use strict";
    
    styled = styled.default;
    
    const LimitSelect = ({className, limit, changeLimit, options}) => {
        console.log('in LimitSelect with limit: ' , limit);
        return (<select
            className={className}
            value={limit}
            onChange={e => {
                changeLimit(e.target.value);
            }}>
            {
                options.map((option) => {
                        return (<option value={option}>{option}</option>);
                    }
                )
            }
        </select>);
    };
    const StyledLimitSelect = styled(LimitSelect)`
        color: palevioletred;
        font-weight: bold;
        margin-left: 1rem;
    `;
    
    return StyledLimitSelect;
});
