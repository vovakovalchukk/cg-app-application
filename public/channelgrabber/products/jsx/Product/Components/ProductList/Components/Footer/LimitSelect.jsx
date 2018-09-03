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
        margin-left: 1rem;
    `;
    
    return StyledLimitSelect;
});
