import React from 'react';
import Select from 'Common/Components/Select';


class SiteSelectComponent extends React.Component {
    static defaultProps = {
        options: {}
    };

    getSiteSelectOptions = () => {
       var options = [];
       for (var siteId in this.props.options) {
           options.push({
               name: this.props.options[siteId],
               value: siteId
           })
       }
       return options;
    };

    getSelectedSite = (siteId) => {
        return {
            name: siteId ? this.props.options[siteId] : '',
            value: siteId ? siteId : ''
        }
    };

    onSiteSelected = (input, site) => {
        input.onChange(site.value);
    };

    render() {
        // Temporary fix - we don't display the site selector anymore, but we will do it in he future,
        // so it's good to leave the site selector in place
        return null;
        return (<label className="form-input-container">
            <span className={"inputbox-label"}>Site</span>
            <div className={"order-inputbox-holder"}>
                <Select
                    autoSelectFirst={false}
                    options={this.getSiteSelectOptions()}
                    selectedOption={this.getSelectedSite(this.props.input.value)}
                    onOptionChange={this.onSiteSelected.bind(this, this.props.input)}
                    filterable={true}
                />
            </div>
        </label>);
    }
}

export default SiteSelectComponent;
