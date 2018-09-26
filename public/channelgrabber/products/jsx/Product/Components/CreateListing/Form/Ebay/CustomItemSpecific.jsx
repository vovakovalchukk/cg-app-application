import React from 'react';
import Input from 'Common/Components/Input';
    export default class extends React.Component {
        getCustomInputName = (index) => {
            return 'CustomInputName' + index;
        };

        getCustomInputValueName = (index) => {
            return 'CustomInputValueName' + index;
        };

        onRemoveButtonClick = (index) => {
            this.props.onRemoveButtonClick(index);
        };

        onNameChange = (index, event) => {
            var value = event.target.value;
            this.onInputChange(index, 'name', value);
        };

        onValueChange = (index, event) => {
            var value = event.target.value;
            this.onInputChange(index, 'value', value);
        };

        onInputChange = (index, type, value) => {
            this.props.onChange(index, type, value);
        };

        renderRemoveButton = (index) => {
            return <span className="remove-icon">
                <i
                    className='fa fa-2x fa-minus-square icon-create-listing'
                    aria-hidden='true'
                    onClick={this.onRemoveButtonClick.bind(this, index)}
                />
            </span>;
        };

        render() {
            return <label>
                <span className={"inputbox-label container-extra-item-specific"}>
                    <Input
                        name={this.getCustomInputName(this.props.index)}
                        value={this.props.name}
                        onChange={this.onNameChange.bind(this, this.props.index)}
                    />
                </span>
                <div className={"order-inputbox-holder"}>
                    <Input
                        name={this.getCustomInputValueName(this.props.index)}
                        value={this.props.value}
                        onChange={this.onValueChange.bind(this, this.props.index)}
                    />
                </div>
                {this.renderRemoveButton(this.props.index)}
            </label>;
        }
    }

