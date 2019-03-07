import React from 'react';
import elementTypes from "../Portal/elementTypes";
import portalSettingsFactory from "../Portal/settingsFactory";
import ReactDOM from "react-dom";

class Portaller extends React.Component {
    static defaultProps = {
        rowIndex: null,
        distanceFromLeftSideOfTableToStartOfCell: null,
        width: null,
        allRows: [],
        render: () => {}
    };

    render() {
        let {rowIndex,distanceFromLeftSideOfTableToStartOfCell, allRows, width} = this.props;
        
        let portalSettings = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.INPUT_SAFE_SUBMITS,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows
        });

        if(!portalSettings){
            return <span/>
        }

        return (
            ReactDOM.createPortal(
                (
                    <portalSettings.PortalWrapper>
                        {this.props.render()}
                    </portalSettings.PortalWrapper>
                ),
                portalSettings.domNodeForSubmits
            )
        );
    }
}

export default Portaller;