import React from 'react';
import ReactDOM from "react-dom";
import ClickOutside from 'Common/Components/ClickOutside';
class StatelessSelectComponent extends React.Component {
    static defaultProps = {
        inputId: '',
        title: '',
        onClick: () => {
        },
        portalSettingsForDropdown: {},
        active: false
    };
    onComponentClick = () => {
        this.props.selectToggle(this.props.inputId);
    };
    getSelectedOptionName = () => {

    };
    getClassNames = () => {
        var classNames = "custom-select ";
        classNames += this.props.classNames;
        classNames += (this.props.active ? ' active ' : '');
        return classNames;
    };
    renderOption = (opt, index) => {
        return <li
            className={"custom-select-item "}
            value={opt.value} key={index}
        >
            <a value={opt.value} data-trigger-select-click="false">{opt.name}</a>
        </li>
    };
    renderOptions = () => {
        return (
            this.props.options.map(this.renderOption)
        )
    };
    renderDropdownInPortal = (Dropdown) => {
        let PortalWrapper = this.props.portalSettingsForDropdown.PortalWrapper;
        let DropdownInWrapper = () => {
            return (
                <PortalWrapper style={{'display': 'green'}}>
                    <div className={'custom-select active'}>
                        <Dropdown/>
                    </div>
                </PortalWrapper>
            );
        };
        if (this.props.active) {
            let PortalledComponent = DropdownInWrapper;
            let targetNode = this.props.portalSettingsForDropdown.domNodeForSubmits;

            return ReactDOM.createPortal(
                <PortalledComponent/>,
                targetNode
            )
        }
        return <span></span>
    };
    renderDropdown = () => {
        let Dropdown = () => (
            <div className="animated fadeInDown open-content">
                <ul>
                    {this.renderOptions()}
                </ul>
            </div>
        );
        if (this.props.portalSettingsForDropdown.usePortal && this.props.portalSettingsForDropdown.domNodeForSubmits) {
            return this.renderDropdownInPortal(Dropdown);

        }
        return <Dropdown/>
    };
    render() {
//        console.log('in Select--stateless with this.props: ', this.props);
        return (
                <div className={this.getClassNames()}
                     onClick={this.onComponentClick}
                     title={this.props.title}
                >
                    <div className="selected">
                        <span className="selected-content">
                            <b>{this.props.prefix ? (this.props.prefix + ": ") : ""}</b>
                            {this.getSelectedOptionName()}
                        </span>
                        <span className="sprite-arrow-down-10-black">&nbsp;</span>
                    </div>
                    {this.renderDropdown()}
                </div>
        );
    }
}

export default StatelessSelectComponent;

