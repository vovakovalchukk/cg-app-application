import React from 'react';

const StickySidebar = ({children, top}) => {
    return (
        <div style={{
            position: 'sticky',
            top: `${top || 0}px`,
            display: 'inline-table'
        }}>
            {children}
        </div>
    )
};

export default StickySidebar;
