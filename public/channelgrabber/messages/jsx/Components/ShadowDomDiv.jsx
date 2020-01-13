import React, {useEffect} from 'react';

const ShadowDomDiv = (props) => {
    const shadowRef = React.createRef();
    console.log('ShadowDomDiv re-render');

    useEffect(() => {
        const shadowChild = shadowRef.current.querySelector('div');
        if ( shadowRef.current.querySelector('div').shadowRoot === null) {
            shadowChild.attachShadow({
                mode: 'open'
            });
        }
        shadowChild.shadowRoot.innerHTML = props.body;
    });

    return (
        <div ref={shadowRef}>
            <div></div>
        </div>
    )
};

export default ShadowDomDiv;