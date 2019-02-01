import React from 'react';
import ReactDOM from "react-dom";
import styled from 'styled-components';
import portalFactory from "../Portal/portalFactory";

const Dropdown = (props) => (
    <div className={"custom-select active"}>
        <div className={"animated fadeInDown open-content " + props.className}>
            <ul>
                {props.renderOptions()}
            </ul>
        </div>
    </div>
);
const StyledDropdown = styled(Dropdown)`
    && { 
        width: ${props => props.width ? props.width + 'px' : 'auto'};
        min-width: ${props => props.width ? 'auto' : 'inherit'};
    }
`;
const CustomSelectLink = styled.a`
    text-overflow: ellipsis;
    white-space: nowrap; 
    overflow: hidden; 
    box-sizing: initial;
`;
const SelectArea = styled.div`
    && {
        width: ${props => props.width ? props.width + 'px' : 'auto'};
    }
`;

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
        return this.props.selectedOption && this.props.selectedOption.name ? this.props.selectedOption.name : ''
    };
    getClassNames = () => {
        return 'custom-select ' + this.props.classNames + (this.props.active ? 'active' : '');
    };
    onOptionSelected = (value) => {
        var selectedOption = this.props.options.find(function(option) {
            return option.value === value;
        });
        this.props.onOptionChange(selectedOption);
    };
    renderOption = (opt, index) => {
        return <li
            className={"custom-select-item "}
            value={opt.value}
            key={index}
            onClick={this.onOptionSelected.bind(this, opt.value)}
        >
            <CustomSelectLink value={opt.value} data-trigger-select-click="false">{opt.name}</CustomSelectLink>
        </li>
    };
    renderOptions = () => {
        return (
            this.props.options.map(this.renderOption)
        )
    };
    renderDropdownInPortal = () => {
        if (!this.props.active) {
            return <span/>
        }

        let portalSettings = this.props.portalSettingsForDropdown;
        return portalFactory.createPortal({
            portalSettings,
            Component: StyledDropdown,
            componentProps: {
                renderOptions: this.renderOptions,
                width: this.props.styleVars.widthOfDropdown
            }
        });
    };
    renderDropdown = () => {
        if (this.props.portalSettingsForDropdown.usePortal && this.props.portalSettingsForDropdown.domNodeForSubmits) {
            return this.renderDropdownInPortal();
        }
        return <StyledDropdown
            renderOptions={this.renderOptions}
            width={this.props.styleVars.widthOfDropdown}
        />
    };
    render() {
        return (
            <div className={this.getClassNames()}
                 onClick={this.onComponentClick}
                 title={this.props.title}
            >
                <SelectArea className="selected" width={this.props.styleVars.widthOfInput}>
                        <span className="selected-content">
                            <b>{this.props.prefix ? (this.props.prefix + ": ") : ""}</b>
                            {this.getSelectedOptionName()}
                        </span>
                    <span className="sprite-arrow-down-10-black">&nbsp;</span>
                </SelectArea>
                {this.renderDropdown()}
            </div>
        );
    }
}

export default StatelessSelectComponent;